<?php
require_once 'dbconnect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$car_id = $_POST['car_id'] ?? null;
$status = $_POST['status'] ?? null;

if (!$car_id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    // Verify the car belongs to the logged-in company
    $stmt = $pdo->prepare("SELECT c.id FROM cars c JOIN companies co ON c.company_id = co.id WHERE c.id = ? AND co.user_id = ?");
    $stmt->execute([$car_id, $_SESSION['user_id']]);
    $car = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$car) {
        echo json_encode(['success' => false, 'message' => 'Car not found']);
        exit;
    }

    // Update status
    $update = $pdo->prepare("UPDATE cars SET availability_status = ? WHERE id = ?");
    $update->execute([$status, $car_id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
