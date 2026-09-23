<?php
session_start();
require_once 'dbconnect.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - LuxDrive</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .container h1,
        .container h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 20px;
        }

        .container h1 {
            font-size: 2.5em;
        }

        .container h2 {
            font-size: 1.8em;
            margin-top: 40px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
        }

        .container p {
            font-size: 1.1em;
            color: #555;
            margin-bottom: 20px;
            text-indent: 20px;
        }

        /* Basic responsive styling */
        @media (max-width: 768px) {
            .container {
                margin: 20px;
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <?php require_once 'header.php'; ?>

    <div class="container">
        <h1>Privacy Policy</h1>
        <p>At LuxDrive, we are committed to protecting your privacy. This policy explains how we collect, use, and safeguard your personal information when you use our services.</p>

        <h2>Information We Collect</h2>
        <p>We collect personal information that you provide directly to us, such as your name, email address, phone number, and payment details. We may also collect non-personal information, such as your IP address, browser type, and usage data, to help us improve our services.</p>

        <h2>How We Use Your Information</h2>
        <p>Your information is used to provide, maintain, and improve our services. This includes processing your bookings, communicating with you about your reservations, and personalizing your experience. We may also use your data for internal analytics and to ensure the security of our platform.</p>

        <h2>Data Security</h2>
        <p>We implement a variety of security measures to protect your personal information. We use industry-standard encryption, secure servers, and strict access controls to prevent unauthorized access, disclosure, or alteration of your data.</p>

        <h2>Your Rights</h2>
        <p>You have the right to access, update, or delete your personal information at any time. If you wish to review or modify your data, please contact our support team. We will respond to your request in accordance with applicable data protection laws.</p>

        <h2>Changes to This Policy</h2>
        <p>We may update this Privacy Policy from time to time. Any changes will be posted on this page, and the "effective date" at the bottom will be revised. We encourage you to review this policy periodically to stay informed about how we are protecting your information.</p>

        <h2>Contact Us</h2>
        <p>If you have any questions or concerns about this Privacy Policy, please contact us at support@luxdrive.com.</p>
    </div>

    <?php require_once 'footer.php'; ?>
</body>

</html>