<?php
session_start();
require 'dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$message = '';
$pass_message = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $birthdate = $_POST['birthdate'] ?? '';

    $profile_picture = $user['profile_picture'];
    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "uploads/profile_pic/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $file_name = basename($_FILES["profile_picture"]["name"]);
        $target_file = $target_dir . time() . '_' . $file_name;
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $profile_picture = $target_file;
        }
    }

    $update = $pdo->prepare("UPDATE users SET name=:name, phone=:phone, address=:address, gender=:gender, birthdate=:birthdate, profile_picture=:profile_picture WHERE id=:id");
    $update->execute([
        ':name' => $name,
        ':phone' => $phone,
        ':address' => $address,
        ':gender' => $gender,
        ':birthdate' => $birthdate,
        ':profile_picture' => $profile_picture,
        ':id' => $user_id
    ]);

    $message = "Profile updated successfully!";
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (password_verify($current, $user['password'])) {
        if ($new === $confirm) {
            $new_hashed = password_hash($new, PASSWORD_DEFAULT);
            $update_pass = $pdo->prepare("UPDATE users SET password=:password WHERE id=:id");
            $update_pass->execute([
                ':password' => $new_hashed,
                ':id' => $user_id
            ]);
            $pass_message = "Password changed successfully!";
        } else {
            $pass_message = "New password and confirmation do not match!";
        }
    } else {
        $pass_message = "Current password is incorrect!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 0;
        }

        .edit-page {
            display: flex;
            justify-content: center;
            padding: 50px 20px;
        }

        .edit-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            width: 100%;
            padding: 40px 30px;
            position: relative;
        }

        .close-btn {
            position: absolute;
            top: 25px;
            right: 25px;
            font-size: 1.5em;
            color: #888;
            cursor: pointer;
            transition: color 0.3s ease;
            text-decoration: none;
        }

        .close-btn:hover {
            color: #333;
        }

        h2 {
            color: #00897b;
            margin-bottom: 25px;
            text-align: center;
        }

        form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px 30px;
            margin-bottom: 40px;
        }

        label {
            font-weight: 600;
            margin-bottom: 5px;
            display: block;
        }

        input,
        select {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        /* Styling for the file input button */
        .file-input-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .file-input-wrapper input[type="file"] {
            display: none;
        }

        .file-input-wrapper label {
            cursor: pointer;
            background: #f0f2f5;
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
            font-weight: 400;
            transition: background 0.2s ease-in-out;
        }

        .file-input-wrapper label:hover {
            background: #e0e2e5;
        }

        .file-input-filename {
            font-size: 14px;
            color: #555;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }


        .full-width {
            grid-column: span 2;
        }

        .btn-group {
            grid-column: span 2;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-save {
            background: #00897b;
            color: white;
        }

        .btn-save:hover {
            background: #00695c;
        }

        .btn-cancel {
            background: #ccc;
            color: #333;
        }

        .btn-cancel:hover {
            background: #999;
        }

        .message {
            text-align: center;
            color: green;
            margin-bottom: 15px;
            grid-column: span 2;
        }

        .profile-preview {
            grid-column: span 2;
            display: flex;
            justify-content: center;
        }

        .profile-preview img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #00897b;
        }

        @media(max-width: 600px) {
            form {
                grid-template-columns: 1fr;
            }

            .btn-group {
                justify-content: center;
            }
        }

        .section-title {
            grid-column: span 2;
            text-align: center;
            font-weight: 700;
            color: #00897b;
            margin: 20px 0 10px 0;
        }

        .btn-forgot {
            background: #fdd835;
            color: #333;
        }

        .btn-forgot:hover {
            background: #fbc02d;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            const profilePreviewImg = document.getElementById('profilePreview');
            const fileNameSpan = document.getElementById('file-name');
            const src = profilePreviewImg.src;
            const fileName = src.substring(src.lastIndexOf('/') + 1);
            if (fileName && fileName !== 'default-profile.png') {
                fileNameSpan.textContent = fileName;
            } else {
                fileNameSpan.textContent = 'No file chosen';
            }
        });

        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                document.getElementById('profilePreview').src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);

            // Update file name display
            const fileName = event.target.files[0] ? event.target.files[0].name : 'No file chosen';
            document.getElementById('file-name').textContent = fileName;
        }

        function confirmLogout() {
            if (confirm("Are you sure you want to logout?")) {
                window.location.href = "logout.php";
            }
        }
    </script>
</head>

<body>

    <div class="edit-page">
        <div class="edit-card">
            <a href="user_profile.php" class="close-btn">
                <i class="fa-solid fa-xmark"></i>
            </a>
            <h2>Edit Profile</h2>

            <!-- Profile Update Form -->
            <?php if ($message): ?>
                <div class="message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
                <!-- Profile Picture Preview -->
                <div class="profile-preview full-width">
                    <img id="profilePreview" src="<?= $user['profile_picture'] ?: 'uploads/profile_pic/default-profile.png' ?>" alt="Profile Picture">
                </div>

                <!-- Upload Section -->
                <div class="full-width">
                    <label for="profile_picture">Change Profile Picture</label>
                    <div class="file-input-wrapper">
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*" onchange="previewImage(event)">
                        <label for="profile_picture" class="btn-browse">Choose File...</label>
                        <span id="file-name" class="file-input-filename"></span>
                    </div>
                </div>
                <div>
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>

                <div>
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
                </div>

                <div>
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" value="<?= htmlspecialchars($user['address']) ?>">
                </div>

                <div>
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender">
                        <option value="">Select</option>
                        <option value="Male" <?= $user['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= $user['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                        <option value="Other" <?= $user['gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>

                <div>
                    <label for="birthdate">Birthdate</label>
                    <input type="date" id="birthdate" name="birthdate" value="<?= htmlspecialchars($user['birthdate']) ?>">
                </div>

                <div class="btn-group">
                    <a href="user_profile.php"><button type="button" class="btn btn-cancel">Cancel</button></a>
                    <button type="submit" name="update_profile" class="btn btn-save">Save Changes</button>
                </div>
            </form>

            <!-- Change Password Form -->
            <div class="section-title">Change Password</div>
            <?php if ($pass_message): ?>
                <div class="message"><?= htmlspecialchars($pass_message) ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="full-width">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="full-width">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="full-width">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <div class="btn-group">
                    <button type="submit" name="change_password" class="btn btn-save">Change Password</button>
                    <button type="button" class="btn btn-cancel" onclick="confirmLogout()">Logout</button>
                </div>
            </form>

            <!-- Forgot Password Button -->
            <div class="btn-group">
                <a href="forgot_password.php">
                    <button type="button" class="btn btn-forgot">Forgot Password?</button>
                </a>
            </div>
        </div>
    </div>
</body>

</html>