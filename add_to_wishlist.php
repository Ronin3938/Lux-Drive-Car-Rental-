<?php
session_start();
require_once 'dbconnect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'not_logged_in', 'message' => 'Please log in to add to your wishlist.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['car_id']) || !isset($input['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit;
}

$car_id = $input['car_id'];
$action = $input['action'];

try {
    if ($action === 'add') {
        // Check if the car is already in the wishlist to prevent duplicates
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ? AND car_id = ?");
        $checkStmt->execute([$user_id, $car_id]);
        if ($checkStmt->fetchColumn() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Car is already in your wishlist.']);
            exit;
        }

        // Add to wishlist and explicitly set notified_status to not_notified
        $addStmt = $pdo->prepare("INSERT INTO wishlists (user_id, car_id, notified_status) VALUES (?, ?, 'not_notified')");
        $addStmt->execute([$user_id, $car_id]);
        echo json_encode(['status' => 'success', 'message' => 'Car added to wishlist!']);
    } elseif ($action === 'remove') {
        // Remove from wishlist
        $removeStmt = $pdo->prepare("DELETE FROM wishlists WHERE user_id = ? AND car_id = ?");
        $removeStmt->execute([$user_id, $car_id]);
        echo json_encode(['status' => 'success', 'message' => 'Car removed from wishlist.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    }
} catch (PDOException $e) {
    error_log("Database error in wishlist: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error. Please try again later.']);
}
