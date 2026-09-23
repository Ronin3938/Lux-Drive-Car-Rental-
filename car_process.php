<?php
session_start();
require_once 'dbconnect.php';
$roleFilter = isset($_GET['role_filter']) ? trim($_GET['role_filter']) : '';

if ($roleFilter !== '') {
    $stmt = $pdo->prepare("
        SELECT *,
        TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) AS age
        FROM users
        WHERE role = :role
    ");
    $stmt->execute([':role' => $roleFilter]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->query("
        SELECT *,
        TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) AS age
        FROM users
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_car'])) {
    $deleteId = $_POST['delete_car_id'];
    $stmt = $pdo->prepare("DELETE FROM cars WHERE id = :id");
    $stmt->execute([':id' => $deleteId]);

    header("Location: admin_dashboard.php");
    exit;
}
