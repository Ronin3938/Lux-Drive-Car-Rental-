<?php
require_once 'dbconnect.php';
session_start();

// ✅ Only allow admin or company role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'company'])) {
    header("Location: login.php");
    exit;
}

$driverId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch driver info
$stmt = $pdo->prepare("SELECT * FROM drivers WHERE id = :id");
$stmt->execute([':id' => $driverId]);
$driver = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$driver) {
    echo "Driver not found.";
    exit;
}

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $phone    = trim($_POST['phone']);
    $email    = trim($_POST['email']);
    $license  = trim($_POST['license_number']);
    $carId    = !empty($_POST['car_id']) ? intval($_POST['car_id']) : null;

    // Handle profile image upload
    $profileImage = $driver['image']; // keep old image
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/drivers/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $profileImage = $fileName;
        }
    }

    $update = $pdo->prepare("
    UPDATE drivers 
    SET name = :name, phone = :phone, email = :email, 
        license_number = :license, car_id = :car_id, image = :image
    WHERE id = :id
");
    $update->execute([
        ':name' => $name,
        ':phone' => $phone,
        ':email' => $email,
        ':license' => $license,
        ':car_id' => $carId,
        ':image' => $profileImage,
        ':id' => $driverId
    ]);

    header("Location: admin_dashboard.php"); // Redirect back to driver section
    exit;
}

// ✅ Fetch available cars (only unassigned + currently assigned to this driver)
$stmtCars = $pdo->prepare("
    SELECT c.id, c.name 
    FROM cars c
    WHERE c.id NOT IN (SELECT car_id FROM drivers WHERE car_id IS NOT NULL AND id != :driverId)
       OR c.id = :currentCar
");
$stmtCars->execute([
    ':driverId' => $driverId,
    ':currentCar' => $driver['car_id']
]);
$cars = $stmtCars->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Driver</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f2f5;
        }

        h2 {
            text-align: center;
            margin-top: 40px;
            color: #333;
        }

        .back-button {
            position: absolute;
            top: 20px;
            right: 20px;
            text-decoration: none;
            background: #007bff;
            color: #fff;
            padding: 10px 15px;
            border-radius: 6px;
            font-weight: 500;
            transition: background 0.3s ease;
        }

        .back-button:hover {
            background: #0056b3;
        }

        form {
            max-width: 500px;
            margin: 60px auto;
            background: #ffffff;
            padding: 30px 25px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }

        form:hover {
            transform: translateY(-3px);
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: 500;
            color: #555;
        }

        input,
        select {
            width: 100%;
            padding: 10px 12px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            transition: border 0.2s ease, box-shadow 0.2s ease;
        }

        input:focus,
        select:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
            outline: none;
        }

        select[disabled] {
            background-color: #e9ecef;
            color: #495057;
            cursor: not-allowed;
            opacity: 1;
        }

        button {
            margin-top: 20px;
            padding: 12px 20px;
            width: 100%;
            background: #28a745;
            color: #fff;
            font-size: 16px;
            font-weight: 500;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        button:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        button.cancel {
            background: #6c757d;
            margin-top: 10px;
        }

        button.cancel:hover {
            background: #5a6268;
        }

        .profile-preview {
            display: block;
            max-width: 140px;
            margin-top: 10px;
            border-radius: 10px;
            border: 1px solid #ddd;
        }

        @media (max-width: 600px) {
            form {
                margin: 40px 15px;
                padding: 25px 20px;
            }

            h2 {
                margin-top: 20px;
                font-size: 22px;
            }
        }
    </style>
</head>

<body>
    <h2>Edit Driver</h2>
    <form method="post" enctype="multipart/form-data">
        <label>Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($driver['name']) ?>" required>

        <label>Phone:</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($driver['phone']) ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($driver['email']) ?>">

        <label>License Number:</label>
        <input type="text" name="license_number" value="<?= htmlspecialchars($driver['license_number']) ?>" required>
        <label>Status:</label>
        <select name="status" disabled>
            <option value="available" <?= $driver['status'] === 'available' ? 'selected' : '' ?>>Available</option>
            <option value="busy" <?= $driver['status'] === 'busy' ? 'selected' : '' ?>>Busy</option>
            <option value="on_leave" <?= $driver['status'] === 'on_leave' ? 'selected' : '' ?>>On Leave</option>
        </select>



        <label>Assigned Car:</label>
        <select name="car_id">
            <option value="">None</option>
            <?php foreach ($cars as $car): ?>
                <option value="<?= $car['id'] ?>" <?= $driver['car_id'] == $car['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($car['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Profile Image:</label>
        <?php if (!empty($driver['image'])): ?>
            <img src="uploads/drivers/<?= htmlspecialchars($driver['image']) ?>" alt="Profile" class="profile-preview">
        <?php endif; ?>
        <input type="file" name="image" accept="image/*">

        <button type="submit">Save Changes</button>
        <a href="admin_dashboard.php"><button type="button" class="cancel">Cancel</button></a>
    </form>
</body>

</html>