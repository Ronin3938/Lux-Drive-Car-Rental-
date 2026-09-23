<?php
require_once 'dbconnect.php';
session_start();

require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';

    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Generate 6-digit OTP
        $otp = random_int(100000, 999999);
        $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // Store OTP and expiration in DB
        $stmt = $pdo->prepare("UPDATE users SET otp = ?, otp_expires_at = ? WHERE id = ?");
        $stmt->execute([$otp, $expires_at, $user['id']]);

        // Send OTP email via Gmail SMTP using PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;

            // Use your Gmail credentials here
            $mail->Username = 'okkarkoko2131@gmail.com';
            $mail->Password = 'bmbevhejelcorrkz';

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('okkarkoko2131@gmail.com', 'Lux Drive');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Your Lux Drive Password Reset OTP';
            $mail->Body    = "<p>Your OTP for password reset is: <strong>$otp</strong></p><p>This OTP will expire in 15 minutes.</p>";

            $mail->send();

            $_SESSION['reset_email'] = $email;
            header('Location: verify_otp.php');
            exit;
        } catch (Exception $e) {
            $message = "Failed to send OTP email. Mailer Error: {$mail->ErrorInfo}";
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
    <title>Forgot Password - Lux Drive</title>
    <link rel="stylesheet" href="css/forgot_password.css">
</head>

<body>

    <div class="left-panel">
        <div class="form-box">
            <h2>Forgot Password</h2>
            <form method="post" action="">
                <div class="input-group">
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>
                <button type="submit" class="btn">Send OTP</button>
            </form>
            <?php if ($message): ?>
                <div class="msg"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <div class="form-footer">
                Remembered your password? <a href="login.php">Login</a><br>
                Don’t have an account? <a href="signup.php">Sign Up</a>
            </div>
        </div>
    </div>

    <div class="right-panel">
        <div class="brand">Lux Drive</div>
        <div class="tagline">Luxury. Comfort. Reliability.</div>

        <div class="highlight-box">
            <h1>Need Help?</h1>
            <p>Enter your email to receive a One-Time Password (OTP) for resetting your password securely.</p>
        </div>
    </div>

</body>

</html>