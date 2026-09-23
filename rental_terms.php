<?php
session_start();
require_once 'dbconnect.php';

// Fetch the most recent terms and conditions from the database
try {
    $stmt = $pdo->prepare("SELECT content, version_number, effective_date FROM terms_and_conditions ORDER BY effective_date DESC LIMIT 1");
    $stmt->execute();
    $terms = $stmt->fetch(PDO::FETCH_ASSOC);
    $content = $terms['content'] ?? 'No terms and conditions found.';
    $version = $terms['version_number'] ?? 'N/A';
    $effectiveDate = $terms['effective_date'] ?? 'N/A';
} catch (PDOException $e) {
    // In case of a database error, display a fallback message.
    $content = 'Error loading terms and conditions. Please try again later.';
    $version = 'N/A';
    $effectiveDate = 'N/A';
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Terms and Conditions - Lux Drive</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 900px;
            margin: 20px auto;
            padding: 40px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .container h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 20px;
        }

        .container h2 {
            margin-top: 30px;
            color: #34495e;
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
        }

        .version-info {
            text-align: center;
            color: #7f8c8d;
            margin-bottom: 30px;
        }

        .version-info p {
            margin-bottom: 20px;
            text-align: justify;
        }

        .legal-content {
            white-space: pre-wrap;
            font-size: 1.1em;
        }
    </style>
</head>

<body>

    <?php require_once 'header.php'; ?>

    <div class="container">
        <h1>Rental Terms and Conditions</h1>
        <div class="version-info">
            <p>Version: <?= htmlspecialchars($version) ?> | Effective Date: <?= htmlspecialchars($effectiveDate) ?></p>
        </div>

        <div class="legal-content">
            <?php echo nl2br(htmlspecialchars($content)); ?>
        </div>
    </div>

    <?php require_once 'footer.php'; ?>
</body>

</html>