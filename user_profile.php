<?php
session_start();
require 'dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT name, email, phone, address, gender, birthdate, profile_picture 
                       FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
        }

        .profile-page {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 50px 20px;
        }

        .profile-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            display: flex;
            max-width: 1000px;
            width: 100%;
            overflow: hidden;
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

        .profile-image {
            flex: 1;
            background: #00897b;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .profile-image img {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            border: 6px solid white;
            object-fit: cover;
        }

        .profile-details {
            flex: 2;
            padding: 40px 30px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .profile-details h2 {
            margin: 0;
            font-size: 28px;
            color: #00897b;
        }

        .btn-group {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 40px;
            /* Added margin for spacing */
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: #00796b;
            color: white;
        }

        .btn-edit:hover {
            background: #00695c;
        }

        .btn-logout {
            background: #00897b;
            color: white;
        }

        .btn-logout:hover {
            background: #00695c;
        }

        .profile-info {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .profile-info p {
            margin: 10px 0;
            font-size: 16px;
            font-weight: 500;
        }

        .profile-info p span {
            font-weight: 700;
            color: #333;
        }

        @media(max-width: 800px) {
            .profile-card {
                flex-direction: column;
                align-items: center;
            }

            .profile-image {
                width: 100%;
                padding: 40px 0;
            }

            .profile-details {
                width: 100%;
                padding: 30px 20px;
            }

            .profile-info {
                grid-template-columns: 1fr;
            }

            .btn-group {
                justify-content: center;
                margin-top: 20px;
            }
        }
    </style>
    <script>
        function confirmLogout() {
            if (confirm("Are you sure you want to logout?")) {
                window.location.href = "logout.php";
            }
        }
    </script>
</head>

<body>

    <div class="profile-page">
        <div class="profile-card">
            <!-- Cross icon to go back to homepage -->
            <a href="homepage.php" class="close-btn">
                <i class="fa-solid fa-xmark"></i>
            </a>

            <!-- Profile Image -->
            <div class="profile-image">
                <img src="<?= $user['profile_picture'] ?: 'default-profile.png' ?>" alt="Profile Picture">
            </div>

            <!-- Profile Details -->
            <div class="profile-details">
                <!-- User Name -->
                <h2><?= htmlspecialchars($user['name']) ?></h2>

                <!-- User Info -->
                <div class="profile-info">
                    <p><span>Email:</span> <?= htmlspecialchars($user['email']) ?></p>
                    <p><span>Phone:</span> <?= htmlspecialchars($user['phone']) ?></p>
                    <p><span>Address:</span> <?= htmlspecialchars($user['address']) ?></p>
                    <p><span>Gender:</span> <?= htmlspecialchars($user['gender']) ?></p>
                    <p><span>Birthdate:</span> <?= htmlspecialchars($user['birthdate']) ?></p>
                </div>

                <!-- Buttons -->
                <div class="btn-group">
                    <a href="edit_profile.php"><button class="btn btn-edit">Edit</button></a>
                    <button class="btn btn-logout" onclick="confirmLogout()">Logout</button>
                </div>
            </div>
        </div>
    </div>

</body>

</html>