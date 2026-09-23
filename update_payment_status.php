<?php
require_once 'dbconnect.php';
session_start();

// Ensure user is logged in as company
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_id']) && isset($_POST['status'])) {
    $payment_id = $_POST['payment_id'];
    $status = $_POST['status'];

    try {
        $allowed_statuses = ['pending', 'paid', 'fail'];
        if (!in_array($status, $allowed_statuses)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid status provided.']);
            exit;
        }

        $pdo->beginTransaction();

        // Update payment status
        $stmt_payment = $pdo->prepare("UPDATE payments SET status = ? WHERE id = ?");
        $stmt_payment->execute([$status, $payment_id]);

        if ($stmt_payment->rowCount() > 0) {
            if ($status === 'paid') {
                // Get booking details
                $stmt_booking_details = $pdo->prepare("
                    SELECT 
                        b.id AS booking_id, 
                        b.total_price, 
                        b.user_id,
                        b.car_id
                    FROM payments AS p
                    JOIN bookings AS b ON p.booking_id = b.id
                    WHERE p.id = ?
                ");
                $stmt_booking_details->execute([$payment_id]);
                $booking_details = $stmt_booking_details->fetch(PDO::FETCH_ASSOC);

                if ($booking_details) {
                    $booking_id = $booking_details['booking_id'];
                    $car_id = $booking_details['car_id'];
                    $user_id = $booking_details['user_id'];
                    $total_price = $booking_details['total_price'];

                    // Update car availability
                    $stmt_car_status = $pdo->prepare("UPDATE cars SET availability_status = 'available' WHERE id = ?");
                    $stmt_car_status->execute([$car_id]);

                    // Update booking status to 'completed'
                    $stmt_update_booking = $pdo->prepare("UPDATE bookings SET status = 'completed' WHERE id = ?");
                    $stmt_update_booking->execute([$booking_id]);


                    // Add reward points to user
                    $points_to_add = intval($total_price * 10);
                    $stmt_check_rewards = $pdo->prepare("SELECT * FROM rewards WHERE user_id = ?");
                    $stmt_check_rewards->execute([$user_id]);
                    $existing_reward = $stmt_check_rewards->fetch(PDO::FETCH_ASSOC);

                    if ($existing_reward) {
                        $stmt_update_rewards = $pdo->prepare("UPDATE rewards SET points = points + ? WHERE user_id = ?");
                        $stmt_update_rewards->execute([$points_to_add, $user_id]);
                    } else {
                        $stmt_insert_rewards = $pdo->prepare("INSERT INTO rewards (user_id, points) VALUES (?, ?)");
                        $stmt_insert_rewards->execute([$user_id, $points_to_add]);
                    }

                    // Increment driver trip count
                    $stmt_driver = $pdo->prepare("SELECT id FROM drivers WHERE car_id = ?");
                    $stmt_driver->execute([$car_id]);
                    $driver = $stmt_driver->fetch(PDO::FETCH_ASSOC);

                    if ($driver) {
                        $stmt_update_driver = $pdo->prepare("UPDATE drivers SET trip_count = trip_count + 1 WHERE id = ?");
                        $stmt_update_driver->execute([$driver['id']]);
                    }
                }
            }

            $pdo->commit();
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            $pdo->rollBack();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Payment ID not found or status is already the same.']);
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing payment ID or status in request.']);
}
