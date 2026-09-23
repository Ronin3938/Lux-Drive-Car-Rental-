<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once "dbconnect.php"; // must set up $pdo (PDO connection)

$errors = [];
$message = "";

$car_id = $_GET['id'] ?? null;

if (!$car_id || !is_numeric($car_id)) {
    die("Invalid car ID provided.");
}

// Fetch categories
try {
    $stmt = $pdo->query("SELECT id, name FROM categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error fetching categories: " . $e->getMessage());
}

// Fetch companies
try {
    $stmt = $pdo->query("SELECT id, company_name FROM companies");
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error fetching companies: " . $e->getMessage());
}

// Fetch car details for pre-filling the form
try {
    $sql = "SELECT * FROM cars WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $car_id]);
    $car = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$car) {
        die("Car not found.");
    }

    // Fetch car categories
    $sql_cats = "SELECT category_id FROM car_categories WHERE car_id = :car_id";
    $stmt_cats = $pdo->prepare($sql_cats);
    $stmt_cats->execute([':car_id' => $car_id]);
    $car_category_ids = $stmt_cats->fetchAll(PDO::FETCH_COLUMN);

    // Fetch car features
    $sql_feats = "SELECT feature_name FROM car_features WHERE car_id = :car_id";
    $stmt_feats = $pdo->prepare($sql_feats);
    $stmt_feats->execute([':car_id' => $car_id]);
    $car_features = $stmt_feats->fetchAll(PDO::FETCH_COLUMN);

    // Fetch car images
    $sql_imgs = "SELECT id, image FROM car_images WHERE car_id = :car_id";
    $stmt_imgs = $pdo->prepare($sql_imgs);
    $stmt_imgs->execute([':car_id' => $car_id]);
    $car_images = $stmt_imgs->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error fetching car details: " . $e->getMessage());
}

// Handle form submission for update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $company_id = trim($_POST['company_id'] ?? '');
    $seats = trim($_POST['seats'] ?? '');
    $price_per_day = trim($_POST['price_per_day'] ?? '');
    $fuel_type = trim($_POST['fuel_type'] ?? 'petrol');
    $availability = trim($_POST['availability_status'] ?? 'Available');
    $description = trim($_POST['description'] ?? '');
    $category_ids = $_POST['category_ids'] ?? [];
    $features = $_POST['features'] ?? [];
    $images_to_delete = $_POST['delete_images'] ?? [];

    // Validate required fields
    if (!$name || !$brand || !$company_id || !$seats || !$price_per_day || empty($category_ids)) {
        $errors[] = "Please fill all required fields and select at least one category.";
    }

    // Handle multiple image uploads
    $image_names = [];
    if (!empty($_FILES['images']['name'][0])) {
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        $uploadDir = __DIR__ . '/uploads';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }

        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $mime = $_FILES['images']['type'][$key] ?? '';
            if (!isset($allowed[$mime])) {
                $errors[] = 'One of the images is not JPG, PNG, or WEBP';
                continue;
            }
            $ext = $allowed[$mime];
            $image_name = uniqid('car_', true) . '.' . $ext;
            $dest = $uploadDir . '/' . $image_name;

            if (move_uploaded_file($tmp_name, $dest)) {
                $image_names[] = $image_name;
            } else {
                $errors[] = 'Failed to upload one of the images';
            }
        }
    }

    // Update database
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Update car info
            $sql = "UPDATE cars SET 
                    name = :name, 
                    brand = :brand, 
                    company_id = :company_id, 
                    seats = :seats, 
                    price_per_day = :price_per_day, 
                    fuel_type = :fuel_type,
                    availability_status = :availability_status, 
                    description = :description
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':brand' => $brand,
                ':company_id' => $company_id,
                ':seats' => $seats,
                ':price_per_day' => $price_per_day,
                ':fuel_type' => $fuel_type,
                ':availability_status' => $availability,
                ':description' => $description,
                ':id' => $car_id
            ]);

            // Delete existing categories and insert new ones
            $pdo->prepare("DELETE FROM car_categories WHERE car_id = ?")->execute([$car_id]);
            $catStmt = $pdo->prepare("INSERT INTO car_categories (car_id, category_id) VALUES (:car_id, :category_id)");
            foreach ($category_ids as $cat_id) {
                $catStmt->execute([
                    ':car_id' => $car_id,
                    ':category_id' => $cat_id
                ]);
            }

            // Delete existing features and insert new ones
            $pdo->prepare("DELETE FROM car_features WHERE car_id = ?")->execute([$car_id]);
            $featStmt = $pdo->prepare("INSERT INTO car_features (car_id, feature_name) VALUES (:car_id, :feature_name)");
            foreach ($features as $feature_name) {
                $featStmt->execute([':car_id' => $car_id, ':feature_name' => $feature_name]);
            }

            // Handle image deletions
            if (!empty($images_to_delete)) {
                foreach ($images_to_delete as $img_id) {
                    // Fetch image filename to delete from the filesystem
                    $img_sql = "SELECT image FROM car_images WHERE id = ?";
                    $img_stmt = $pdo->prepare($img_sql);
                    $img_stmt->execute([$img_id]);
                    $img_file = $img_stmt->fetchColumn();

                    // Delete from database
                    $pdo->prepare("DELETE FROM car_images WHERE id = ?")->execute([$img_id]);

                    // Delete file from server
                    if ($img_file) {
                        $file_path = __DIR__ . '/uploads/' . $img_file;
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }
                }
            }

            // Insert new images
            if ($image_names) {
                $imgStmt = $pdo->prepare("INSERT INTO car_images (car_id, image) VALUES (:car_id, :image)");
                foreach ($image_names as $img) {
                    $imgStmt->execute([':car_id' => $car_id, ':image' => $img]);
                }
            }

            $pdo->commit();
            $message = "Car, categories, features, and images updated successfully.";

            // Re-fetch data to reflect changes
            header("Location: edit_car.php?id=" . $car_id . "&success=1");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Update failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Car</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f6f8fb;
            margin: 0;
            padding: 30px;
        }

        h1 {
            color: #2c3e50;
        }

        form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            max-width: 700px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
        }

        label {
            display: block;
            margin: 14px 0 6px;
            font-weight: 600;
        }

        input[type="text"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #dcdfe4;
            border-radius: 6px;
            box-sizing: border-box;
        }

        textarea {
            min-height: 110px;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .row.features {
            grid-template-columns: repeat(3, 1fr);
        }

        button {
            margin-top: 16px;
            background: #2ecc71;
            color: #fff;
            border: none;
            padding: 12px 16px;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background: #27ae60;
        }

        .msg {
            margin-bottom: 16px;
            padding: 12px;
            border-radius: 6px;
        }

        .ok {
            background: #e9f9ee;
            color: #1b7f3a;
            border: 1px solid #b9ebc7;
        }

        .err {
            background: #fff2f2;
            color: #b00020;
            border: 1px solid #ffd5d5;
        }

        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .image-preview {
            position: relative;
            width: 100px;
            height: 100px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 5px;
        }

        .delete-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: rgba(255, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            font-size: 12px;
            line-height: 1;
        }
    </style>
</head>

<body>
    <h1>Edit Car: <?= htmlspecialchars($car['name'] ?? '') ?></h1>

    <?php if ($message || isset($_GET['success'])): ?>
        <div class="msg ok">Car updated successfully.</div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="msg err">
            <?php foreach ($errors as $e): ?>
                <div>• <?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Car Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($car['name'] ?? '') ?>" required>

        <label>Brand</label>
        <input type="text" name="brand" value="<?= htmlspecialchars($car['brand'] ?? '') ?>" required>

        <div class="row">
            <div>
                <label>Category</label>
                <div>
                    <?php foreach ($categories as $cat): ?>
                        <div>
                            <input type="checkbox" name="category_ids[]" value="<?= (int)$cat['id'] ?>" id="cat_<?= (int)$cat['id'] ?>" <?= in_array($cat['id'], $car_category_ids) ? 'checked' : '' ?>>
                            <label for="cat_<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <script>
                    document.querySelector('form').addEventListener('submit', function(e) {
                        const checked = document.querySelectorAll('input[name="category_ids[]"]:checked');
                        if (checked.length === 0) {
                            alert('Please select at least one category.');
                            e.preventDefault();
                        }
                    });
                </script>
            </div>

            <div>
                <label>Company</label>
                <select name="company_id" required>
                    <option value="">-- Select Company --</option>
                    <?php foreach ($companies as $comp): ?>
                        <option value="<?= (int)$comp['id'] ?>" <?= ((int)$comp['id'] === (int)$car['company_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($comp['company_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div>
                <label>Seats</label>
                <input type="number" name="seats" min="1" value="<?= htmlspecialchars($car['seats'] ?? '') ?>" required>
            </div>
            <div>
                <label>Price per Day</label>
                <input type="number" step="0.01" name="price_per_day" value="<?= htmlspecialchars($car['price_per_day'] ?? '') ?>" required>
            </div>
        </div>

        <label>Fuel Type</label>
        <select name="fuel_type" required>
            <option value="petrol" <?= ($car['fuel_type'] === 'petrol') ? 'selected' : '' ?>>Petrol</option>
            <option value="diesel" <?= ($car['fuel_type'] === 'diesel') ? 'selected' : '' ?>>Diesel</option>
            <option value="electric" <?= ($car['fuel_type'] === 'electric') ? 'selected' : '' ?>>Electric</option>
            <option value="hybrid" <?= ($car['fuel_type'] === 'hybrid') ? 'selected' : '' ?>>Hybrid</option>
        </select>

        <label>Availability Status</label>
        <select name="availability_status" required>
            <option value="available" <?= ($car['availability_status'] === 'available') ? 'selected' : '' ?>>Available</option>
            <option value="booked" <?= ($car['availability_status'] === 'booked') ? 'selected' : '' ?>>Booked</option>
            <option value="maintenance" <?= ($car['availability_status'] === 'maintenance') ? 'selected' : '' ?>>Maintenance</option>
        </select>

        <label>Car Features</label>
        <div class="row features">
            <?php
            $all_features = [
                'Adaptive cruise control',
                'Backup camera',
                'AUX input',
                'Bluetooth',
                'USB charger',
                'USB input',
                'GPS',
                'Keyless entry',
                'Heated seats',
                'Bermister Sound System',
                'Ambient Lighting',
                'USB-C ports'
            ];
            foreach ($all_features as $feature) :
            ?>
                <div>
                    <input type="checkbox" name="features[]" value="<?= htmlspecialchars($feature) ?>" id="feature_<?= str_replace(' ', '_', $feature) ?>" <?= in_array($feature, $car_features) ? 'checked' : '' ?>>
                    <label for="feature_<?= str_replace(' ', '_', $feature) ?>"><?= htmlspecialchars($feature) ?></label>
                </div>
            <?php endforeach; ?>
        </div>

        <label>Current Images (Check to delete)</label>
        <div class="image-preview-container">
            <?php foreach ($car_images as $img): ?>
                <div class="image-preview">
                    <img src="./uploads/<?= htmlspecialchars($img['image']) ?>" alt="Car Image">
                    <input type="checkbox" name="delete_images[]" value="<?= (int)$img['id'] ?>">
                    <button type="button" class="delete-image" onclick="this.parentNode.querySelector('input').checked=true; this.parentNode.style.opacity=0.5;">&times;</button>
                </div>
            <?php endforeach; ?>
        </div>

        <label>Add New Image (JPG/PNG/WEBP)</label>
        <input type="file" name="images[]" accept=".jpg,.jpeg,.png,.webp" multiple>

        <label>Description</label>
        <textarea name="description" placeholder="Short details about the car..."><?= htmlspecialchars($car['description'] ?? '') ?></textarea>

        <button type="submit">Update Car</button>
    </form>
</body>

</html>