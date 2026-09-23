<?php
session_start();
require_once 'dbconnect.php';

// Check if headers have already been sent before attempting to set the content type
if (headers_sent()) {
    echo json_encode(['success' => false, 'message' => 'Error: Headers already sent. Check for any output (like spaces or newlines) before the opening <?php tag in this file or dbconnect.php.']);
    exit;
}

header('Content-Type: application/json');

// Check if the PDO connection object is available
if (!isset($pdo)) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed. Please check dbconnect.php.']);
    exit;
}

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to exchange points.']);
    exit;
}

// Ensure the required POST data is present
if (!isset($_POST['points_to_exchange']) || !isset($_POST['coupon_value'])) {
    echo json_encode(['success' => false, 'message' => 'Missing data in request.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$points_to_exchange = intval($_POST['points_to_exchange']);

// This is the line that needs to be changed:
// Use intval() instead of floatval() to match the integer value in the array.
$coupon_value = intval($_POST['coupon_value']);

// Define the valid exchange options to prevent invalid data
$exchange_options = [
    1000 => 100,
    2000 => 220,
    3000 => 350,
    5000 => 600,
    10000 => 1500
];

// Check if the exchange option is valid based on the defined options
if (!array_key_exists($points_to_exchange, $exchange_options) || $exchange_options[$points_to_exchange] !== $coupon_value) {
    echo json_encode(['success' => false, 'message' => 'Invalid exchange option.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Verify that the user ID exists in the users table to prevent foreign key constraint violations.
    $stmt_check_user = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id = ?");
    $stmt_check_user->execute([$user_id]);
    $user_exists = $stmt_check_user->fetchColumn();

    if ($user_exists == 0) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'User not found. Please log in again.']);
        exit;
    }

    // Check if the user has an existing rewards entry.
    $stmt_check = $pdo->prepare("SELECT points FROM rewards WHERE user_id = ?");
    $stmt_check->execute([$user_id]);
    $current_points = $stmt_check->fetchColumn();

    // If no entry exists, create one with 0 points.
    if ($current_points === false) {
        $stmt_insert = $pdo->prepare("INSERT INTO rewards (user_id, points) VALUES (?, 0)");
        $stmt_insert->execute([$user_id]);
        $current_points = 0;
    }

    if ($current_points < $points_to_exchange) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Insufficient points.']);
        exit;
    }

    // Deduct the points from the user's total
    $stmt_deduct = $pdo->prepare("UPDATE rewards SET points = points - ? WHERE user_id = ?");
    $stmt_deduct->execute([$points_to_exchange, $user_id]);

    // Insert a new coupon record for the user
    $stmt_insert_coupon = $pdo->prepare("
        INSERT INTO user_coupons (user_id, coupon_value, points_exchanged) 
        VALUES (?, ?, ?)
    ");
    $stmt_insert_coupon->execute([$user_id, $coupon_value, $points_to_exchange]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Points exchanged successfully!']);
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
