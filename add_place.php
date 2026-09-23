<?php
require_once 'dbconnect.php';

$message = ''; // Variable to store success or error messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $place_name = $_POST['name'];
    $place_description = $_POST['description'];
    $place_category = $_POST['category'];
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;
    $image_files = $_FILES['place_images'];

    $upload_directory = 'uploads/place_images/';
    if (!is_dir($upload_directory)) mkdir($upload_directory, 0777, true);

    try {
        $pdo->beginTransaction();

        // ✅ Include latitude & longitude in insert
        $sql_place = "INSERT INTO places (name, description, category, latitude, longitude) 
                      VALUES (?, ?, ?, ?, ?)";
        $stmt_place = $pdo->prepare($sql_place);
        $stmt_place->execute([$place_name, $place_description, $place_category, $latitude, $longitude]);
        $place_id = $pdo->lastInsertId();


        if (!empty($image_files['name'][0])) {
            $sql_image = "INSERT INTO place_images (place_id, image) VALUES (?, ?)";
            $stmt_image = $pdo->prepare($sql_image);

            foreach ($image_files['name'] as $key => $filename) {
                $temp_path = $image_files['tmp_name'][$key];
                $new_filename = uniqid() . '_' . basename($filename);
                $target_path = $upload_directory . $new_filename;

                if (move_uploaded_file($temp_path, $target_path)) {
                    $stmt_image->execute([$place_id, $new_filename]);
                } else {
                    throw new Exception("Failed to upload image: " . $filename);
                }
            }
        }

        $pdo->commit();
        $message = "New place and its images added successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add New Place</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            min-height: 100vh;
            margin: 0;
        }

        .form-container {
            position: relative;
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 0.75rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }

        h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #1f2937;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 0.5rem;
        }

        input[type="text"],
        select,
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        input:focus,
        select:focus,
        textarea:focus,
        input[type="file"]:focus {
            border-color: #3b82f6;
            outline: none;
        }

        textarea {
            resize: vertical;
        }

        .submit-button {
            width: 100%;
            padding: 0.75rem;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
        }

        .submit-button:hover {
            transform: translateY(-2px);
            background-color: #2563eb;
        }

        .submit-button:active {
            transform: translateY(0);
        }

        .message {
            text-align: center;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
        }

        .success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .error {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Back Button Icon */
        .back-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 1.5rem;
            color: #ffffff;
            background-color: #047e2f;
            padding: 8px 10px;
            border-radius: 50%;
            text-decoration: none;
            text-align: center;
        }

        .back-btn:hover {
            background-color: #065f3b;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <a href="admin_dashboard.php" class="back-btn">x</a>
        <h2>Add New Place</h2>

        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="add_place.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Place Name:</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4"></textarea>
            </div>

            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <option value="">-- Select Category --</option>
                    <option value="Kachin">Kachin</option>
                    <option value="Kayah">Kayah</option>
                    <option value="Kayin">Kayin</option>
                    <option value="Chin">Chin</option>
                    <option value="Mon">Mon</option>
                    <option value="Rakhine">Rakhine</option>
                    <option value="Shan">Shan</option>
                    <option value="Yangon">Yangon</option>
                    <option value="Mandalay">Mandalay</option>
                    <option value="Sagaing">Sagaing</option>
                    <option value="Bago">Bago</option>
                    <option value="Tanintharyi">Tanintharyi</option>
                    <option value="Ayeyarwady">Ayeyarwady</option>
                    <option value="Magway">Magway</option>
                </select>
            </div>
            <div class="form-group">
                <label for="latitude">Latitude:</label>
                <input type="text" id="latitude" name="latitude" placeholder="e.g. 16.8409" required>
            </div>

            <div class="form-group">
                <label for="longitude">Longitude:</label>
                <input type="text" id="longitude" name="longitude" placeholder="e.g. 96.1735" required>
            </div>


            <div class="form-group">
                <label for="place_images">Upload Images:</label>
                <input type="file" id="place_images" name="place_images[]" multiple required>
            </div>

            <button type="submit" class="submit-button">Add Place</button>
        </form>
    </div>
</body>

</html>