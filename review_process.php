<?php
session_start();
require_once 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_car_review'])) {
        $deleteId = $_POST['delete_car_review_id'];
        $stmt = $pdo->prepare("DELETE FROM car_reviews WHERE id = :id");
        $stmt->execute([':id' => $deleteId]);
    }

    if (isset($_POST['delete_website_review'])) {
        $deleteId = $_POST['delete_website_review_id'];
        $stmt = $pdo->prepare("DELETE FROM website_reviews WHERE id = :id");
        $stmt->execute([':id' => $deleteId]);
    }

    header("Location: admin_dashboard.php");
    exit;
}
