<?php
// Include the database connection file
require_once 'dbconnect.php';

$message = ''; // Message to display to the user (success or error)

// Fetch cars from the database that do not already have a driver
try {
    $cars_sql = "SELECT id, name FROM cars WHERE id NOT IN (SELECT car_id FROM drivers) ORDER BY name";
    $stmt_cars = $pdo->prepare($cars_sql);
    $stmt_cars->execute();
    $cars = $stmt_cars->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If the query fails, set an error message and provide an empty array
    $message = "Error fetching cars: " . $e->getMessage();
    $cars = [];
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $license_number = trim($_POST['license_number']);
    $car_id = $_POST['car_id'];
    $image_path = NULL; // Default to NULL if no image is uploaded

    // Handle image upload
    if (isset($_FILES['driver_image']) && $_FILES['driver_image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/driver_images/';
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_tmp_name = $_FILES['driver_image']['tmp_name'];
        $file_name = uniqid() . '_' . basename($_FILES['driver_image']['name']);
        $destination = $upload_dir . $file_name;

        if (move_uploaded_file($file_tmp_name, $destination)) {
            $image_path = $destination;
        } else {
            $message = "Error uploading image.";
        }
    }

    // Basic validation
    if (empty($name) || empty($phone) || empty($license_number) || empty($car_id)) {
        $message = "Please fill in all required fields.";
    } elseif (empty($message)) { // Only proceed if no image upload error occurred
        try {
            // SQL query to insert a new driver, including the image column
            $insert_sql = "INSERT INTO drivers (name, phone, email, license_number, car_id, image) VALUES (:name, :phone, :email, :license_number, :car_id, :image)";
            $stmt = $pdo->prepare($insert_sql);

            // Bind parameters to the prepared statement
            $stmt->bindValue(':name', $name);
            $stmt->bindValue(':phone', $phone);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':license_number', $license_number);
            $stmt->bindValue(':car_id', $car_id);
            $stmt->bindValue(':image', $image_path);

            // Execute the statement
            if ($stmt->execute()) {
                $message = "Driver added successfully!";
            } else {
                $message = "Error adding driver. Please try again.";
            }
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
        }
    }
    header("Location: admin_dashboard.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Driver</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        body {
            background-color: #f3f4f6;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .form-container {
            position: relative;
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }

        h1 {
            text-align: center;
            color: #1f2937;
            margin-bottom: 1.5rem;
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
        input[type="email"],
        select,
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
        input[type="file"]:focus {
            border-color: #3b82f6;
            outline: none;
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

        /* Back Button as Icon inside form */
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
        <a href="admin_dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
        <h1>Add New Driver</h1>
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="add_driver.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Driver Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="email">Email (Optional)</label>
                <input type="email" id="email" name="email">
            </div>
            <div class="form-group">
                <label for="license_number">License Number</label>
                <input type="text" id="license_number" name="license_number" required>
            </div>
            <div class="form-group">
                <label for="driver_image">Driver Image (Optional)</label>
                <input type="file" id="driver_image" name="driver_image">
            </div>
            <div class="form-group">
                <label for="car_id">Assign Car</label>
                <select id="car_id" name="car_id" required>
                    <?php if (count($cars) > 0): ?>
                        <option value="">-- Select a Car --</option>
                        <?php foreach ($cars as $car): ?>
                            <option value="<?= htmlspecialchars($car['id']); ?>"><?= htmlspecialchars($car['name'] . ' (ID: ' . $car['id'] . ')'); ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">No cars available for assignment</option>
                    <?php endif; ?>
                </select>
            </div>
            <button type="submit" class="submit-button">Add Driver</button>
        </form>
    </div>
</body>

</html>