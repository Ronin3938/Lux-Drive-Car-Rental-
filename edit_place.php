<?php
session_start();
require_once 'dbconnect.php';

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);

    if (empty($name) || empty($description) || empty($category) || empty($_FILES['place_images']['name'][0])) {
        $error = "All fields and at least one image are required.";
    } else {
        try {
            // Start a transaction
            $pdo->beginTransaction();

            // Insert into the 'places' table
            $sql_place = "INSERT INTO places (name, description, category) VALUES (?, ?, ?)";
            $stmt_place = $pdo->prepare($sql_place);
            $stmt_place->execute([$name, $description, $category]);
            $place_id = $pdo->lastInsertId();

            // Handle image uploads
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            foreach ($_FILES['place_images']['name'] as $key => $image_name) {
                $image_tmp_name = $_FILES['place_images']['tmp_name'][$key];
                $image_size = $_FILES['place_images']['size'][$key];
                $image_error = $_FILES['place_images']['error'][$key];
                $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
                $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');

                if (in_array($image_ext, $allowed_extensions) && $image_error === 0) {
                    $new_image_name = uniqid('place_') . '.' . $image_ext;
                    $upload_path = $upload_dir . $new_image_name;

                    if (move_uploaded_file($image_tmp_name, $upload_path)) {
                        $sql_image = "INSERT INTO place_images (place_id, image) VALUES (?, ?)";
                        $stmt_image = $pdo->prepare($sql_image);
                        $stmt_image->execute([$place_id, $new_image_name]);
                    } else {
                        throw new Exception("Failed to upload image.");
                    }
                }
            }

            // Commit the transaction
            $pdo->commit();
            $message = "New place added successfully!";
        } catch (Exception $e) {
            // Rollback the transaction on error
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add New Place</title>
    <link rel="stylesheet" href="css/rentcars.css">
    <link rel="stylesheet" href="css/add_place.css">
</head>

<body>
    <?php require_once 'header.php'; ?>

    <div class="add-place-container">
        <h1>Add New Place</h1>

        <?php if (!empty($message)): ?>
            <div class="success-message">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="add_place.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Place Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label for="category">Category (Region/State):</label>
                <select id="category" name="category" required>
                    <option value="">Select a Region/State</option>
                    <?php
                    // Define the 14 regions and states of Myanmar
                    $allRegions = [
                        'Ayeyarwady Region',
                        'Bago Region',
                        'Chin State',
                        'Kachin State',
                        'Kayah State',
                        'Kayin State',
                        'Magway Region',
                        'Mandalay Region',
                        'Mon State',
                        'Rakhine State',
                        'Sagaing Region',
                        'Shan State',
                        'Tanintharyi Region',
                        'Yangon Region'
                    ];
                    foreach ($allRegions as $region): ?>
                        <option value="<?= htmlspecialchars($region) ?>"><?= htmlspecialchars($region) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="place_images">Upload Images (Hold Ctrl/Cmd to select multiple):</label>
                <input type="file" id="place_images" name="place_images[]" multiple required>
            </div>
            <button type="submit" class="submit-btn">Add Place</button>
        </form>
    </div>

    <?php require_once 'footer.php'; ?>
</body>

</html>