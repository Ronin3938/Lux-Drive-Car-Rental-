<?php
session_start();
require_once 'dbconnect.php';

// Check if an ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$categoryId = $_GET['id'];
$message = '';

// Handle form submission for updating category
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $categoryName = $_POST['category_name'];
    $currentImage = $_POST['current_image'];
    $imageFileName = $currentImage; // Default to the current image

    // Handle new image upload
    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] == 0) {
        $uploadDir = 'uploads/categories/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileExt = pathinfo($_FILES['category_image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExt;
        $fileDestination = $uploadDir . $fileName;

        // Move the new file and update the filename
        if (move_uploaded_file($_FILES['category_image']['tmp_name'], $fileDestination)) {
            $imageFileName = $fileName;

            // Delete the old image if it's not a default one
            if ($currentImage && file_exists($uploadDir . $currentImage)) {
                unlink($uploadDir . $currentImage);
            }
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE categories SET name = :name, image = :image WHERE id = :id");
        $stmt->execute([
            ':name' => $categoryName,
            ':image' => $imageFileName,
            ':id' => $categoryId
        ]);
        $message = "Category updated successfully!";
    } catch (PDOException $e) {
        $message = "Error updating category: " . $e->getMessage();
    }
}

// Fetch the current category details for the form
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
$stmt->execute([':id' => $categoryId]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header("Location: admin_dashboard.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category</title>
    <link rel="stylesheet" href="css/admin_dashboard.css">
    <style>
        .edit-form-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .edit-form-container h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .edit-form-container form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .edit-form-container input[type="text"],
        .edit-form-container input[type="file"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .edit-form-container button {
            padding: 12px;
            border: none;
            border-radius: 4px;
            background-color: #3498db;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .edit-form-container button:hover {
            background-color: #2980b9;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
        }

        .current-image-container {
            text-align: center;
            margin-bottom: 10px;
        }

        .current-image-container img {
            max-width: 150px;
            height: auto;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .message {
            text-align: center;
            color: #27ae60;
            font-weight: bold;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

    <div class="edit-form-container">
        <h2>Edit Car Category</h2>

        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="current_image" value="<?= htmlspecialchars($category['image']) ?>">

            <label for="category_name">Category Name:</label>
            <input type="text" id="category_name" name="category_name" value="<?= htmlspecialchars($category['name']) ?>" required>

            <label for="category_image">Current Image:</label>
            <div class="current-image-container">
                <?php if (!empty($category['image'])): ?>
                    <img src="uploads/categories/<?= htmlspecialchars($category['image']) ?>" alt="Current Image">
                <?php else: ?>
                    No Image
                <?php endif; ?>
            </div>

            <label for="new_image">Choose New Image (optional):</label>
            <input type="file" id="new_image" name="category_image">

            <button type="submit">Update Category</button>
        </form>

        <a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>

</body>

</html>