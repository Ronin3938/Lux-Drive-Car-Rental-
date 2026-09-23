<?php
require_once 'dbconnect.php';
session_start();

require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Check if the user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($password, $user['password'])) {
            $role = $user['role'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $role;
            $_SESSION['user_name'] = $user['name'];

            // If user is admin, send OTP to the email
            if ($role == 'admin') {
                // Generate OTP and expiration time
                $otp = random_int(100000, 999999);
                $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

                // Store OTP and expiration time in the DB
                $stmt = $pdo->prepare("UPDATE users SET otp = ?, otp_expires_at = ? WHERE id = ?");
                $stmt->execute([$otp, $expires_at, $user['id']]);

                // Send OTP email
                $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'okkarkoko2131@gmail.com';
                    $mail->Password = 'bmbevhejelcorrkz';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port = 465;

                    // Recipients
                    $mail->setFrom('okkarkoko2131@gmail.com', 'Lux Drive');
                    $mail->addAddress($email);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Your OTP for Admin Login';
                    $mail->Body = "Your OTP for admin login is <strong>$otp</strong>. It will expire in 15 minutes.";

                    $mail->send();
                    $_SESSION['reset_email'] = $email;
                    header('Location: admin_otp.php'); // Redirect to OTP verification page
                    exit;
                } catch (Exception $e) {
                    $message = "Failed to send OTP email. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                // For non-admin users, proceed normally
                if ($role == 'company') {
                    header("Location: companies_dashboard.php");
                    exit;
                } else if ($role == 'customer') {
                    // Set the success message only for customers
                    $_SESSION['success_message'] = "Login successful!";
                    header("Location: homepage.php");
                    exit;
                }
            }
        } else {
            $message = "Incorrect password.";
        }
    } else {
        $message = "No user found with this email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login - Lux Drive</title>
    <link rel="stylesheet" href="css/login.css">
</head>

<body>

    <div class="left-panel">
        <div class="form-box">
            <h2>Login</h2>
            <form method="post" action="">
                <div class="input-group">
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="btn">Login</button>
            </form>
            <?php if ($message): ?>
                <div class="msg"><?= $message ?></div>
            <?php endif; ?>
            <div class="form-footer">
                Don’t have an account? <a href="signup.php">Sign Up</a><br>
                <a href="forgot_password.php">Forgot Password?</a>
            </div>
        </div>
    </div>

    <div class="right-panel">
        <div class="brand">Lux Drive</div>
        <div class="tagline">Luxury. Comfort. Reliability.</div>

        <div class="highlight-box">
            <h1>Welcome</h1>
            <p>Book your ride easily with <b>Lux Drive</b>. Choose from luxury, SUVs, or economy cars. Travel smarter with our trusted service.</p>
        </div>
    </div>

</body>

</html>