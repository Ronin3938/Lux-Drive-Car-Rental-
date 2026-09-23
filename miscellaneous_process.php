<?php
session_start();
require_once 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Places, Companies, Drivers, Payments, etc.
    if (isset($_POST['delete_place'])) {
        $deleteId = $_POST['delete_place_id'];
        $stmt = $pdo->prepare("DELETE FROM places WHERE id = :id");
        $stmt->execute([':id' => $deleteId]);
    }

    if (isset($_POST['delete_company'])) {
        $deleteId = $_POST['delete_company_id'];
        $stmt = $pdo->prepare("DELETE FROM companies WHERE id = :id");
        $stmt->execute([':id' => $deleteId]);
    }

    if (isset($_POST['delete_driver'])) {
        $deleteId = $_POST['delete_driver_id'];
        $stmt = $pdo->prepare("DELETE FROM drivers WHERE id = :id");
        $stmt->execute([':id' => $deleteId]);
    }

    if (isset($_POST['delete_payment'])) {
        $deleteId = $_POST['delete_payment_id'];
        $stmt = $pdo->prepare("DELETE FROM payments WHERE id = :id");
        $stmt->execute([':id' => $deleteId]);
    }


    // Category Management
    if (isset($_POST['add_category'])) {
        $categoryName = $_POST['category_name'];
        $imageFileName = null;
        if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] == 0) {
            $uploadDir = 'uploads/categories/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileExt = pathinfo($_FILES['category_image']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '.' . $fileExt;
            $fileDestination = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['category_image']['tmp_name'], $fileDestination)) {
                $imageFileName = $fileName;
            }
        }
        $stmt = $pdo->prepare("INSERT INTO categories (name, image) VALUES (:name, :image)");
        $stmt->execute([':name' => $categoryName, ':image' => $imageFileName]);
    }
    if (isset($_POST['delete_category'])) {
        $deleteId = $_POST['delete_category_id'];
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->execute([':id' => $deleteId]);
    }

    // Logo Management
    if (isset($_POST['update_logo'])) {
        $logo_name = $_POST['logo_name'];
        $image_path = null;
        if (isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] === UPLOAD_ERR_OK) {
            $file_name = $_FILES['logo_image']['name'];
            $file_tmp_name = $_FILES['logo_image']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['png', 'jpg', 'jpeg', 'gif'];
            if (in_array($file_ext, $allowed_ext)) {
                $upload_dir = 'homepageimg/';
                $new_file_name = 'logo.' . $file_ext;
                $file_path = $upload_dir . $new_file_name;
                if (move_uploaded_file($file_tmp_name, $file_path)) {
                    $image_path = $file_path;
                } else {
                    $_SESSION['error_message'] = 'Failed to upload image.';
                }
            } else {
                $_SESSION['error_message'] = 'Invalid file type. Only PNG, JPG, JPEG, and GIF are allowed.';
            }
        }
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM logo");
            $stmt->execute();
            $logo_exists = $stmt->fetchColumn();
            if ($logo_exists > 0) {
                $query = "UPDATE logo SET name = ?";
                $params = [$logo_name];
                if ($image_path) {
                    $query .= ", image = ?";
                    $params[] = $image_path;
                }
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
            } else {
                if ($image_path) {
                    $stmt = $pdo->prepare("INSERT INTO logo (name, image) VALUES (?, ?)");
                    $stmt->execute([$logo_name, $image_path]);
                } else {
                    $_SESSION['error_message'] = 'Logo does not exist. Please provide an image to create one.';
                }
            }
            if (!isset($_SESSION['error_message'])) {
                $_SESSION['success_message'] = 'Logo updated successfully!';
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
        }
    }

    header("Location: admin_dashboard.php");
    exit;
}
