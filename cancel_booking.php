<?php
session_start();
require_once 'dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$booking_id = $input['booking_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$booking_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid booking ID.']);
    exit;
}

// Get booking info
$stmt = $pdo->prepare("SELECT status, car_id FROM bookings WHERE id = ? AND user_id = ?");
$stmt->execute([$booking_id, $user_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    echo json_encode(['status' => 'error', 'message' => 'Booking not found.']);
    exit;
}

if ($booking['status'] !== 'pending') {
    echo json_encode(['status' => 'error', 'message' => 'Only pending bookings can be cancelled.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Cancel booking
    $updateBooking = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
    $updateBooking->execute([$booking_id]);

    // Make car available
    $updateCar = $pdo->prepare("UPDATE cars SET availability_status = 'available' WHERE id = ?");
    $updateCar->execute([$booking['car_id']]);

    // Reset coupon(s) used by this user
    $resetCoupon = $pdo->prepare("UPDATE user_coupons SET is_used = 0 WHERE user_id = ? AND is_used = 1");
    $resetCoupon->execute([$user_id]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Booking cancelled, car available, coupon reset.']);
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Cancel booking failed: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to cancel booking. Check server logs.']);
}
