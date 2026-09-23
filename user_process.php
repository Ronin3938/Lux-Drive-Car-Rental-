<?php
session_start();
require_once 'dbconnect.php';

// Helper function from the original file
function getAdminCount($pdo)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $stmt->execute();
    return $stmt->fetchColumn();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $adminCount = getAdminCount($pdo);

    if (isset($_POST['user_id']) && isset($_POST['role'])) {
        $userId = $_POST['user_id'];
        $newRole = $_POST['role'];

        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user['role'] === 'admin' && $adminCount == 1 && $newRole !== 'admin') {
            $_SESSION['error_message'] = 'You cannot change the role of the last remaining admin.';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
            $stmt->execute([':role' => $newRole, ':id' => $userId]);
        }
    }

    if (isset($_POST['delete_user'])) {
        $deleteId = $_POST['delete_user_id'];
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->execute([':id' => $deleteId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user['role'] === 'admin' && $adminCount == 1) {
            $_SESSION['error_message'] = 'You cannot delete the last remaining admin.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([':id' => $deleteId]);
        }
    }

    header("Location: admin_dashboard.php");
    exit;
}
