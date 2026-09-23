<?php
session_start();
require_once 'dbconnect.php';

// Check if the user is an admin. Redirect if not.
// This is a placeholder; you'll need to implement your own admin check logic.
// For example, if you have an `is_admin` column in your `users` table:
// if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
//     header("Location: /access_denied.php"); 
//     exit;
// }

$message = '';

// Handle form submission to add a new version of terms
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $version_number = $_POST['version_number'] ?? '';
    $effective_date = $_POST['effective_date'] ?? '';
    $content = $_POST['content'] ?? '';

    if (!empty($version_number) && !empty($effective_date) && !empty($content)) {
        try {
            // Check if version number already exists
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM terms_and_conditions WHERE version_number = ?");
            $checkStmt->execute([$version_number]);
            if ($checkStmt->fetchColumn() > 0) {
                $message = 'duplicate_version';
            } else {
                // Insert new terms and conditions into the database
                $insertStmt = $pdo->prepare("
                    INSERT INTO terms_and_conditions (version_number, effective_date, content) 
                    VALUES (?, ?, ?)
                ");
                $insertStmt->execute([$version_number, $effective_date, $content]);
                $message = 'success';
            }
        } catch (PDOException $e) {
            $message = 'error';
        }
    } else {
        $message = 'invalid';
    }
}

// Fetch the most recent terms for display
try {
    $stmt = $pdo->prepare("SELECT content, version_number, effective_date FROM terms_and_conditions ORDER BY effective_date DESC LIMIT 1");
    $stmt->execute();
    $currentTerms = $stmt->fetch(PDO::FETCH_ASSOC);
    $currentContent = $currentTerms['content'] ?? 'No terms found. Add a new version below.';
    $currentVersion = $currentTerms['version_number'] ?? '';
    $currentEffectiveDate = $currentTerms['effective_date'] ?? '';
} catch (PDOException $e) {
    $currentContent = 'Error loading current terms.';
    $currentVersion = '';
    $currentEffectiveDate = '';
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Terms - Lux Drive Admin</title>
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

        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 20px;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .message.invalid {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .message.duplicate {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="date"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        textarea {
            height: 400px;
            resize: vertical;
        }

        button {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #2c3e50;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #34495e;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Back / Close Button -->
        <a href="admin_dashboard.php" style="float: right; text-decoration: none; font-size: 1.5em; color: #333; margin-bottom: 10px;">
            <i class="fas fa-times"></i>
        </a>

        <h1>Edit Rental Terms and Conditions</h1>

        <?php if ($message === 'success'): ?>
            <div class="message success">New terms and conditions version saved successfully!</div>
        <?php elseif ($message === 'error'): ?>
            <div class="message error">An unknown database error occurred. Please check your server logs.</div>
        <?php elseif ($message === 'invalid'): ?>
            <div class="message invalid">Please fill in all fields.</div>
        <?php elseif ($message === 'duplicate_version'): ?>
            <div class="message duplicate">Error: This version number already exists. Please choose a unique version number.</div>
        <?php endif; ?>

        <form action="edit_terms.php" method="POST">
            <div class="form-group">
                <label for="version_number">Version Number:</label>
                <input type="text" id="version_number" name="version_number" placeholder="e.g., 2.0" value="<?= htmlspecialchars($currentVersion); ?>" required>
            </div>
            <div class="form-group">
                <label for="effective_date">Effective Date:</label>
                <input type="date" id="effective_date" name="effective_date" value="<?= htmlspecialchars(date('Y-m-d')); ?>" required>
            </div>
            <div class="form-group">
                <label for="content">Content:</label>
                <textarea id="content" name="content" required><?= htmlspecialchars($currentContent); ?></textarea>
            </div>
            <button type="submit">Save New Version</button>
        </form>
    </div>
</body>

</html>