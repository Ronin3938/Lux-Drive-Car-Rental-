<?php
require_once 'dbconnect.php';
require_once 'mail.php'; // PHPMailer helper
session_start();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_id = $_POST['booking_id'] ?? null;
    $new_status = $_POST['status'] ?? null;
    $user_id = $_SESSION['user_id'];

    if ($booking_id && $new_status) {
        try {
            // Get company ID
            $stmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $company = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$company) {
                $response['message'] = 'Company not found for this user.';
                echo json_encode($response);
                exit;
            }
            $company_id = $company['id'];

            // Validate booking belongs to this company + fetch user info
            $stmt = $pdo->prepare("
                SELECT b.id, b.coupon_id, b.car_id, b.start_date, b.end_date, u.email, u.name,
                       c.name AS car_name, c.brand
                FROM bookings AS b
                JOIN cars AS c ON b.car_id = c.id
                JOIN users AS u ON b.user_id = u.id
                WHERE b.id = ? AND c.company_id = ?
            ");
            $stmt->execute([$booking_id, $company_id]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($booking) {
                $pdo->beginTransaction();

                // Update booking status
                $update_stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
                $update_stmt->execute([$new_status, $booking_id]);

                // 🔹 Handle cancelled bookings
                if ($new_status === 'cancelled') {
                    // 1. Free up coupon if used
                    if (!empty($booking['coupon_id'])) {
                        $stmtCoupon = $pdo->prepare("UPDATE user_coupons SET is_used = 0 WHERE id = ?");
                        $stmtCoupon->execute([$booking['coupon_id']]);
                    }

                    // 2. Make car available again
                    $stmtCar = $pdo->prepare("UPDATE cars SET availability_status = 'available' WHERE id = ?");
                    $stmtCar->execute([$booking['car_id']]);

                    // 3. Delete related payment records
                    $stmtPayment = $pdo->prepare("DELETE FROM payments WHERE booking_id = ?");
                    $stmtPayment->execute([$booking_id]);
                }

                // 🔹 Handle completed bookings
                if ($new_status === 'completed') {
                    $stmtCar = $pdo->prepare("UPDATE cars SET availability_status = 'available' WHERE id = ?");
                    $stmtCar->execute([$booking['car_id']]);
                }

                $pdo->commit();

                // ✅ Send email notification
                $toEmail = $booking['email'];
                $subject = "Your Booking #{$booking_id} Status Update";

                if ($new_status === 'confirmed') {
                    $body = "
                        <h2>Booking Confirmed ✅</h2>
                        <p>Dear {$booking['name']},</p>
                        <p>Your booking for <strong>{$booking['brand']} {$booking['car_name']}</strong>
                        from <strong>{$booking['start_date']}</strong> to <strong>{$booking['end_date']}</strong>
                        has been <b>confirmed</b>.</p>
                        <p>Thank you for choosing Lux Drive!</p>
                    ";
                } elseif ($new_status === 'cancelled') {
                    $body = "
                        <h2>Booking Cancelled ❌</h2>
                        <p>Dear {$booking['name']},</p>
                        <p>Your booking for <strong>{$booking['brand']} {$booking['car_name']}</strong>
                        from <strong>{$booking['start_date']}</strong> to <strong>{$booking['end_date']}</strong>
                        has been <b>cancelled</b>.</p>
                        <p>If you believe this is a mistake, please contact our support team.</p>
                    ";
                } elseif ($new_status === 'completed') {
                    $body = "
                        <h2>Booking Completed 🎉</h2>
                        <p>Dear {$booking['name']},</p>
                        <p>Your trip with <strong>{$booking['brand']} {$booking['car_name']}</strong> has been <b>completed</b>.</p>
                        <p>We’d love your feedback! Please leave a review on our platform.</p>
                    ";
                } else {
                    $body = "
                        <h2>Booking Update</h2>
                        <p>Your booking #{$booking_id} status has been changed to <b>{$new_status}</b>.</p>
                    ";
                }

                sendNotificationEmail($toEmail, $subject, $body);

                $response['success'] = true;
                $response['message'] = 'Status updated successfully and email sent.';
            } else {
                $response['message'] = 'Booking not found or does not belong to your company.';
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Missing booking ID or new status.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
