<?php
require_once 'dbconnect.php';
session_start();

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp = $_POST['otp'] ?? '';
    $email = $_SESSION['reset_email'] ?? '';

    if (!$email || !$otp) {
        $message = 'Please enter the OTP.';
    } else {
        // Fetch user and compare OTP
        $stmt = $pdo->prepare("SELECT otp, otp_expires_at FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Check if OTP is correct and not expired
            if ($otp == $user['otp'] && strtotime($user['otp_expires_at']) > time()) {
                // OTP is valid, proceed to admin homepage
                header('Location: homepage.php');
                exit;
            } else {
                $message = "Invalid or expired OTP.";
            }
        } else {
            $message = "No user found with this email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Verify OTP - Lux Drive</title>
    <link rel="stylesheet" href="css/admin_otp.css">
</head>

<body>

    <div class="left-panel">
        <div class="form-box">
            <h2>Verify OTP</h2>
            <form method="post" action="">
                <div class="input-group">
                    <input type="text" name="otp" placeholder="6-digit OTP" required maxlength="6" pattern="\d{6}">
                </div>
                <button type="submit" class="btn">Verify OTP</button>
            </form>
            <?php if ($message): ?>
                <div class="msg"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <div class="form-footer">
                Didn't receive OTP? <a href="login.php">Request Again</a><br>
            </div>
        </div>
    </div>

    <div class="right-panel">
        <div class="brand">Lux Drive</div>
        <div class="tagline">Luxury. Comfort. Reliability.</div>

        <div class="highlight-box">
            <h1>One-Time Password</h1>
            <p>Please enter the 6-digit OTP sent to your email to verify your identity.</p>
        </div>
    </div>

</body>

</html>