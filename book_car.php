<?php
session_start();
require_once 'dbconnect.php'; // Make sure this sets up $pdo with PDO

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unexpected error occurred.'];

// Read raw JSON input
$input = json_decode(file_get_contents('php://input'), true);

// --- 1. Validate required fields ---
$required_fields = ['car_id', 'start_date', 'end_date', 'pickup_lat', 'pickup_lon', 'dropoff_lat', 'dropoff_lon', 'csrf_token'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || $input[$field] === '') {
        echo json_encode(['success' => false, 'message' => "❌ Missing required field: $field"]);
        exit;
    }
}

// --- 2. Validate login ---
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => "❌ Please log in to book a car."]);
    exit;
}

// --- 3. CSRF check ---
if (!isset($_SESSION['csrf_token']) || $input['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => "❌ Invalid CSRF token."]);
    exit;
}

// --- 4. Sanitize input ---
$car_id     = filter_var($input['car_id'], FILTER_VALIDATE_INT);
$user_id    = $_SESSION['user_id'];
$start_date = $input['start_date'];
$end_date   = $input['end_date'];
$pickup_lat = filter_var($input['pickup_lat'], FILTER_VALIDATE_FLOAT);
$pickup_lon = filter_var($input['pickup_lon'], FILTER_VALIDATE_FLOAT);
$dropoff_lat = filter_var($input['dropoff_lat'], FILTER_VALIDATE_FLOAT);
$dropoff_lon = filter_var($input['dropoff_lon'], FILTER_VALIDATE_FLOAT);
$coupon_id  = !empty($input['coupon_id']) ? filter_var($input['coupon_id'], FILTER_VALIDATE_INT) : null;
$apply_discount = !empty($input['apply_discount']); // bool
$payment_method = isset($input['payment_method']) ? $input['payment_method'] : 'cash';

if ($car_id === false || $pickup_lat === false || $pickup_lon === false || $dropoff_lat === false || $dropoff_lon === false) {
    echo json_encode(['success' => false, 'message' => "❌ Invalid data format."]);
    exit;
}

try {
    $pdo->beginTransaction();

    // --- 5. Validate car ---
    $stmtCar = $pdo->prepare("SELECT price_per_day, availability_status FROM cars WHERE id = :car_id");
    $stmtCar->execute([':car_id' => $car_id]);
    $car = $stmtCar->fetch(PDO::FETCH_ASSOC);

    if (!$car || $car['availability_status'] !== 'available') {
        throw new Exception("Car is not available for booking.");
    }

    // --- 6. Calculate price ---
    $start_dt = new DateTime($start_date);
    $end_dt   = new DateTime($end_date);
    $interval = $start_dt->diff($end_dt);
    $num_days = max(1, $interval->days);

    $server_total_price = $car['price_per_day'] * $num_days;
    $discount_applied = 0;

    // First-time discount
    if ($apply_discount) {
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = :uid AND status = 'completed'");
        $stmtCheck->execute([':uid' => $user_id]);
        if ($stmtCheck->fetchColumn() == 0) {
            $server_total_price *= 0.90;
            $discount_applied = 1;
        }
    }

    // Coupon discount
    if ($coupon_id) {
        $stmtCoupon = $pdo->prepare("SELECT coupon_value FROM user_coupons WHERE id = :cid AND user_id = :uid AND is_used = 0");
        $stmtCoupon->execute([':cid' => $coupon_id, ':uid' => $user_id]);
        $coupon = $stmtCoupon->fetch(PDO::FETCH_ASSOC);

        if ($coupon) {
            $server_total_price = max(0, $server_total_price - $coupon['coupon_value']);
            $discount_applied = 1;

            // Mark coupon used
            $pdo->prepare("UPDATE user_coupons SET is_used = 1 WHERE id = :cid")
                ->execute([':cid' => $coupon_id]);
        }
    }

    // --- 7. Insert booking ---
    $stmtBooking = $pdo->prepare("
        INSERT INTO bookings 
        (user_id, car_id, start_date, end_date, total_price, discount_applied, coupon_id, pickup_lat, pickup_lon, dropoff_lat, dropoff_lon, status)
        VALUES 
        (:uid, :cid, :start, :end, :price, :disc, :coupon_id, :plat, :plon, :dlat, :dlon, 'pending')
    ");
    $stmtBooking->execute([
        ':uid' => $user_id,
        ':cid' => $car_id,
        ':start' => $start_date,
        ':end' => $end_date,
        ':price' => $server_total_price,
        ':disc' => $discount_applied,
        ':coupon_id' => $coupon_id,
        ':plat' => $pickup_lat,
        ':plon' => $pickup_lon,
        ':dlat' => $dropoff_lat,
        ':dlon' => $dropoff_lon
    ]);

    $booking_id = $pdo->lastInsertId();

    // --- 8. Insert payment ---
    $stmtPayment = $pdo->prepare("
        INSERT INTO payments (booking_id, payment_method, amount, status)
        VALUES (:bid, :pmethod, :amount, 'pending')
    ");
    $stmtPayment->execute([
        ':bid' => $booking_id,
        ':pmethod' => $payment_method,
        ':amount' => $server_total_price
    ]);

    // --- 9. Update car availability ---
    $pdo->prepare("UPDATE cars SET availability_status = 'booked' WHERE id = :car_id")
        ->execute([':car_id' => $car_id]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => "✅ Booking successful! Total: $" . number_format($server_total_price, 2)]);
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Booking error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => "❌ Booking failed. Please contact support."]);
    exit;
}
