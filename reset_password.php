<?php
require_once 'dbconnect.php';
session_start();

if (!isset($_SESSION['reset_email']) || !isset($_SESSION['otp_verified'])) {
    header('Location: forgot_password.php');
    exit;
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $email = $_SESSION['reset_email'];

        // Update password and clear OTP (extra safety)
        $stmt = $pdo->prepare("UPDATE users SET password = ?, otp = NULL, otp_expires_at = NULL WHERE email = ?");
        $stmt->execute([$hashed_password, $email]);

        // Clear session variables
        unset($_SESSION['reset_email'], $_SESSION['otp_verified']);

        // Redirect to login page with success message
        header('Location: login.php?reset=success');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Reset Password - Lux Drive</title>
    <link rel="stylesheet" href="css/reset_password.css" />
</head>

<body>

    <div class="left-panel">
        <div class="form-box">
            <h2>Reset Password</h2>
            <form method="post" action="">
                <div class="input-group">
                    <input type="password" name="password" placeholder="New Password" required minlength="6" />
                </div>
                <div class="input-group">
                    <input type="password" name="confirm_password" placeholder="Confirm New Password" required minlength="6" />
                </div>
                <button type="submit" class="btn">Reset Password</button>
            </form>
            <?php if ($message): ?>
                <div class="msg"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <div class="form-footer">
                Remembered your password? <a href="login.php">Login</a><br>
                Need help? <a href="forgot_password.php">Forgot Password</a>
            </div>
        </div>
    </div>

    <div class="right-panel">
        <div class="brand">Lux Drive</div>
        <div class="tagline">Luxury. Comfort. Reliability.</div>

        <div class="highlight-box">
            <h1>Reset Your Password</h1>
            <p>Enter a new password for your Lux Drive account. Make sure it’s strong and secure.</p>
        </div>
    </div>

</body>

</html>