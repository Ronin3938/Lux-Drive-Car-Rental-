<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once "dbconnect.php"; // must set up $pdo (PDO connection)

$errors = [];
$message = "";

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

// Handle form submission
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

    // Get features from categorized groups
    $features_by_category = $_POST['features'] ?? [];
    $additional_features = array_filter($_POST['additional_features'] ?? []);

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

        $uploadDir = __DIR__ . '/uploads/car_images/';
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

    // Insert into database
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Insert car info
            $sql = "INSERT INTO cars 
                    (name, brand, company_id, seats, price_per_day, fuel_type, availability_status, description)
                    VALUES (:name, :brand, :company_id, :seats, :price_per_day, :fuel_type, :availability_status, :description)";
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
            ]);

            $car_id = $pdo->lastInsertId();

            // Insert categories
            $catStmt = $pdo->prepare("INSERT INTO car_categories (car_id, category_id) VALUES (:car_id, :category_id)");
            foreach ($category_ids as $cat_id) {
                $catStmt->execute([
                    ':car_id' => $car_id,
                    ':category_id' => $cat_id
                ]);
            }

            // Insert features
            $featStmt = $pdo->prepare("INSERT INTO car_features (car_id, feature_name, feature_value) VALUES (:car_id, :feature_name, :feature_value)");

            // Loop through categorized features
            foreach ($features_by_category as $category_key => $features) {
                // This check ensures the value is an array before looping
                if (is_array($features)) {
                    foreach ($features as $feature_name) {
                        $featStmt->execute([
                            ':car_id' => $car_id,
                            ':feature_name' => $feature_name,
                            ':feature_value' => $category_key
                        ]);
                    }
                }
            }

            // Loop through additional features, giving them the 'Additional features' value
            if (!empty($additional_features)) {
                foreach ($additional_features as $feature_name) {
                    $featStmt->execute([
                        ':car_id' => $car_id,
                        ':feature_name' => $feature_name,
                        ':feature_value' => 'Additional features'
                    ]);
                }
            }

            // Corrected SQL to use `image` instead of `path`
            if (!empty($image_names)) {
                $imgStmt = $pdo->prepare("INSERT INTO car_images (car_id, image) VALUES (:car_id, :image)");
                foreach ($image_names as $image_path) {
                    $imgStmt->execute([
                        ':car_id' => $car_id,
                        ':image' => $image_path
                    ]);
                }
            }

            $pdo->commit();
            $message = "Car, categories, features, and images added successfully.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Insert failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Insert Car</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f6f8fb;
            margin: 0;
            padding: 30px;
        }

        h1 {
            color: #2c3e50;
            text-align: center;
        }

        form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            position: relative;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            max-width: 1200px;
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

        input[type="checkbox"] {
            display: inline-block;
            vertical-align: middle;
            margin-right: 6px;
        }

        label[for^="cat_"] {
            display: inline-block;
            margin-right: 15px;
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

        .features-group {
            margin-bottom: 20px;
            border: 1px solid #dcdfe4;
            border-radius: 6px;
            padding: 15px;
        }

        .features-group h4 {
            margin-top: 0;
        }

        .additional-features-section {
            display: flex;
            flex-direction: column;
            gap: 10px;
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

        .add-feature-btn {
            background: #3498db;
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            width: fit-content;
            margin-top: 0;
        }

        .add-feature-btn:hover {
            background: #2980b9;
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

        .back-btn {
            position: absolute;
            top: 5px;
            right: 15px;
            font-size: 1.5rem;
            text-decoration: none;
            color: #fff;
            background-color: #047e2fff;
            padding: 5px 10px;
            border-radius: 50%;
            transition: background-color 0.3s;
        }

        .back-btn:hover {
            background-color: #0ce1bdff;
        }

        /* Two panel layout */
        .left-panel,
        .right-panel {
            display: flex;
            flex-direction: column;
        }

        /* Make the features scrollable if too long */
        .right-panel {
            max-height: 80vh;
            overflow-y: auto;
        }

        /* Responsive */
        @media (max-width: 900px) {
            form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <h1>Insert New Car</h1>

    <?php if ($message) : ?>
        <div class="msg ok"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($errors) : ?>
        <div class="msg err">
            <?php foreach ($errors as $e) : ?>
                <div>• <?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <a href="admin_dashboard.php" class="back-btn">×</a>

        <!-- LEFT PANEL -->
        <div class="left-panel">
            <label>Car Name</label>
            <input type="text" name="name" required>

            <label>Brand</label>
            <input type="text" name="brand" required>

            <label>Category</label>
            <div>
                <?php foreach ($categories as $cat) : ?>
                    <div>
                        <input type="checkbox" name="category_ids[]" value="<?= (int)$cat['id'] ?>" id="cat_<?= (int)$cat['id'] ?>">
                        <label for="cat_<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></label>
                    </div>
                <?php endforeach; ?>
            </div>

            <label>Company</label>
            <select name="company_id" required>
                <option value="">-- Select Company --</option>
                <?php foreach ($companies as $comp) : ?>
                    <option value="<?= (int)$comp['id'] ?>"><?= htmlspecialchars($comp['company_name']) ?></option>
                <?php endforeach; ?>
            </select>

            <div class="row">
                <div>
                    <label>Seats</label>
                    <input type="number" name="seats" min="1" required>
                </div>
                <div>
                    <label>Price per Day</label>
                    <input type="number" step="0.01" name="price_per_day" required>
                </div>
            </div>

            <label>Fuel Type</label>
            <select name="fuel_type" required>
                <option value="petrol">Petrol</option>
                <option value="diesel">Diesel</option>
                <option value="electric">Electric</option>
                <option value="hybrid">Hybrid</option>
            </select>

            <label>Availability Status</label>
            <select name="availability_status" required>
                <option value="available">Available</option>
                <option value="booked">Booked</option>
                <option value="maintenance">Maintenance</option>
            </select>
        </div>

        <!-- RIGHT PANEL -->
        <div class="right-panel">
            <div class="features-group">
                <h4>Safety</h4>
                <?php
                $safety_features = [
                    'Adaptive cruise control',
                    'Backup camera',
                    'Blind spot warning'
                ];
                foreach ($safety_features as $feature) : ?>
                    <div>
                        <input type="checkbox" name="features[Safety][]" value="<?= htmlspecialchars($feature) ?>" id="feature_<?= str_replace(' ', '_', $feature) ?>">
                        <label for="feature_<?= str_replace(' ', '_', $feature) ?>"><?= htmlspecialchars($feature) ?></label>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="features-group">
                <h4>Device connectivity</h4>
                <?php
                $device_features = [
                    'AUX input',
                    'Bluetooth',
                    'USB charger',
                    'USB input'
                ];
                foreach ($device_features as $feature) : ?>
                    <div>
                        <input type="checkbox" name="features[Device connectivity][]" value="<?= htmlspecialchars($feature) ?>" id="feature_<?= str_replace(' ', '_', $feature) ?>">
                        <label for="feature_<?= str_replace(' ', '_', $feature) ?>"><?= htmlspecialchars($feature) ?></label>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="features-group">
                <h4>Convenience</h4>
                <?php
                $convenience_features = [
                    'GPS',
                    'Keyless entry'
                ];
                foreach ($convenience_features as $feature) : ?>
                    <div>
                        <input type="checkbox" name="features[Convenience][]" value="<?= htmlspecialchars($feature) ?>" id="feature_<?= str_replace(' ', '_', $feature) ?>">
                        <label for="feature_<?= str_replace(' ', '_', $feature) ?>"><?= htmlspecialchars($feature) ?></label>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="features-group">
                <h4>Additional features</h4>
                <div id="additional-features-container" class="additional-features-section">
                    <div>
                        <input type="text" name="additional_features[]" placeholder="Enter feature name...">
                    </div>
                </div>
                <button type="button" class="add-feature-btn" onclick="addFeatureInput()">Add more features</button>
            </div>

            <label>Image (JPG/PNG/WEBP)</label>
            <input type="file" name="images[]" accept=".jpg,.jpeg,.png,.webp" multiple>

            <label>Description</label>
            <textarea name="description" placeholder="Short details about the car..."></textarea>

            <button type="submit">Add Car</button>
        </div>
    </form>

    <script>
        function showMessage(message, type = 'ok') {
            const msgBox = document.createElement('div');
            msgBox.className = `msg ${type}`;
            msgBox.textContent = message;
            document.querySelector('form').before(msgBox);
            setTimeout(() => msgBox.remove(), 5000);
        }

        function addFeatureInput() {
            const container = document.getElementById('additional-features-container');
            const newDiv = document.createElement('div');
            newDiv.innerHTML = `<input type="text" name="additional_features[]" placeholder="Enter feature name...">`;
            container.appendChild(newDiv);
        }

        document.querySelector('form').addEventListener('submit', function(e) {
            const checked = document.querySelectorAll('input[name="category_ids[]"]:checked');
            if (checked.length === 0) {
                showMessage('Please select at least one category.', 'err');
                e.preventDefault();
            }
        });
    </script>
</body>

</html>