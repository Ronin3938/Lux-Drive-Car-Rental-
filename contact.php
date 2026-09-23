<?php
session_start();
require_once 'dbconnect.php';

// Fetch all active contact details from the database
try {
    $stmt = $pdo->prepare("SELECT contact_type, email, phone_number, address FROM contact_details WHERE status = 'active' ORDER BY id ASC");
    $stmt->execute();
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $contacts = [];
    $error = "Unable to load contact information at this time. Please try again later.";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
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
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1em;
            color: #555;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Grid layout - 3 columns on desktop */
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            /* Always max 3 columns */
            gap: 20px;
        }

        @media (max-width: 992px) {
            .contact-grid {
                grid-template-columns: repeat(2, 1fr);
                /* 2 columns on tablets */
            }
        }

        @media (max-width: 600px) {
            .contact-grid {
                grid-template-columns: 1fr;
                /* 1 column on mobile */
            }
        }

        .contact-card {
            background-color: #f9f9f9;
            padding: 25px;
            border-radius: 10px;
            border: 1px solid #eee;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            width: 100%;
            box-sizing: border-box;
        }

        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .contact-card h2 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 1.5em;
        }

        .contact-card p {
            margin: 5px 0;
            font-size: 1em;
            color: #777;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .contact-card p i {
            margin-right: 10px;
            color: #3498db;
        }

        .error-message {
            color: #e74c3c;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php require_once 'header.php'; ?>
    <div class="container">
        <div class="header">
            <h1>Get in Touch</h1>
            <p>
                We're here to help! Whether you have a question about our services, need assistance with a booking, or just want to say hello, feel free to reach out to us through any of the channels below.
            </p>
        </div>

        <?php if (isset($error)): ?>
            <p class="error-message"><?= htmlspecialchars($error); ?></p>
        <?php elseif (!empty($contacts)): ?>
            <div class="contact-grid">
                <?php foreach ($contacts as $contact): ?>
                    <div class="contact-card">
                        <h2><?= htmlspecialchars($contact['contact_type']); ?></h2>
                        <?php if (!empty($contact['email'])): ?>
                            <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($contact['email']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($contact['phone_number'])): ?>
                            <p><i class="fas fa-phone"></i> <?= htmlspecialchars($contact['phone_number']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($contact['address'])): ?>
                            <p><i class="fas fa-map-marker-alt"></i> <?= nl2br(htmlspecialchars($contact['address'])); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="error-message">No contact information is available at this time.</p>
        <?php endif; ?>
    </div>
    <?php require_once 'footer.php'; ?>
</body>

</html>