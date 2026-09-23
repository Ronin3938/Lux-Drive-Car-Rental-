<?php
session_start();
require_once 'dbconnect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to submit a review.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$car_id = isset($input['car_id']) ? intval($input['car_id']) : 0;
$rating = isset($input['rating']) ? intval($input['rating']) : 0;
$review_text = trim($input['review_text'] ?? '');
$user_id = $_SESSION['user_id'];

if ($car_id <= 0 || $rating < 1 || $rating > 5 || empty($review_text)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO car_reviews (car_id, user_id, rating, review_text, created_at) 
        VALUES (:car_id, :user_id, :rating, :review_text, NOW())
    ");
    $stmt->execute([
        ':car_id' => $car_id,
        ':user_id' => $user_id,
        ':rating' => $rating,
        ':review_text' => $review_text
    ]);

    echo json_encode(['success' => true, 'message' => 'Review submitted successfully!']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
