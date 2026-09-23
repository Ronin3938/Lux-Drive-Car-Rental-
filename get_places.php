<?php
require_once 'dbconnect.php';

header('Content-Type: application/json');

try {
    // This query is correct and necessary to get all required data for the front-end.
    $stmt = $pdo->query("SELECT id, name, latitude, longitude FROM places");
    $places = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($places);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
