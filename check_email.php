<?php
require_once 'dbconnect.php';
header('Content-Type: application/json');
$email = $_GET['email'] ?? '';
if ($email) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email=?");
    $stmt->execute([$email]);
    echo json_encode(['exists' => $stmt->rowCount() > 0]);
} else echo json_encode(['exists' => false]);
