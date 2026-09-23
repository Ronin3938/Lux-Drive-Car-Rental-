<?php
session_start();
require_once 'dbconnect.php';

// In a real application, you would implement robust admin authentication here.
// For example:
// if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
//     header("Location: /access_denied.php");
//     exit;
// }

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $question = trim($_POST['question'] ?? '');
        $answer = trim($_POST['answer'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if (!empty($question) && !empty($answer) && !empty($category)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO faq (question, answer, category, status) VALUES (?, ?, ?, ?)");
                $stmt->execute([$question, $answer, $category, $status]);
                $message = 'success_add';
            } catch (PDOException $e) {
                $message = 'error_add';
            }
        } else {
            $message = 'invalid_add';
        }
    } elseif ($action === 'delete') {
        $faq_id = $_POST['faq_id'] ?? 0;
        try {
            $stmt = $pdo->prepare("DELETE FROM faq WHERE id = ?");
            $stmt->execute([$faq_id]);
            $message = 'success_delete';
        } catch (PDOException $e) {
            $message = 'error_delete';
        }
    } elseif ($action === 'update_status') {
        $faq_id = $_POST['faq_id'] ?? 0;
        $status = $_POST['status'] ?? 'active';
        try {
            $stmt = $pdo->prepare("UPDATE faq SET status = ? WHERE id = ?");
            $stmt->execute([$status, $faq_id]);
            $message = 'success_update';
        } catch (PDOException $e) {
            $message = 'error_update';
        }
    }
}

// Fetch all FAQs to display in a list
try {
    $stmt = $pdo->query("SELECT * FROM faq ORDER BY created_at DESC");
    $allFaqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $allFaqs = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit FAQ - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .message.invalid {
            background-color: #fff3cd;
            color: #856404;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        .button {
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

        .button:hover {
            background-color: #34495e;
        }

        .faq-list {
            margin-top: 40px;
        }

        .faq-list h2 {
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .faq-item-display {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .faq-content {
            flex-grow: 1;
            padding-right: 20px;
        }

        .faq-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .delete-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        .status-form {
            display: flex;
            gap: 5px;
            align-items: center;
        }

        .status-form select {
            padding: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Add New FAQ</h1>

        <?php if ($message === 'success_add'): ?>
            <div class="message success">FAQ added successfully!</div>
        <?php elseif ($message === 'error_add'): ?>
            <div class="message error">An error occurred while adding the FAQ.</div>
        <?php elseif ($message === 'invalid_add'): ?>
            <div class="message invalid">Please fill out all required fields.</div>
        <?php elseif ($message === 'success_delete'): ?>
            <div class="message success">FAQ deleted successfully!</div>
        <?php elseif ($message === 'error_delete'): ?>
            <div class="message error">An error occurred while deleting the FAQ.</div>
        <?php elseif ($message === 'success_update'): ?>
            <div class="message success">FAQ status updated successfully!</div>
        <?php elseif ($message === 'error_update'): ?>
            <div class="message error">An error occurred while updating the FAQ status.</div>
        <?php endif; ?>

        <form action="edit_faq.php" method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="question">Question:</label>
                <input type="text" id="question" name="question" required>
            </div>
            <div class="form-group">
                <label for="answer">Answer:</label>
                <textarea id="answer" name="answer" required></textarea>
            </div>
            <div class="form-group">
                <label for="category">Category:</label>
                <input type="text" id="category" name="category" required>
            </div>
            <div class="form-group">
                <label for="status">Status:</label>
                <select id="status" name="status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <button type="submit" class="button">Add FAQ</button>
        </form>

        <div class="faq-list">
            <h2>Current FAQs</h2>
            <?php if (!empty($allFaqs)): ?>
                <?php foreach ($allFaqs as $faq): ?>
                    <div class="faq-item-display">
                        <div class="faq-content">
                            <h4><?= htmlspecialchars($faq['question']); ?></h4>
                            <p><strong>Category:</strong> <?= htmlspecialchars($faq['category']); ?></p>
                            <p><strong>Status:</strong> <?= htmlspecialchars($faq['status']); ?></p>
                        </div>
                        <div class="faq-actions">
                            <form class="status-form" action="edit_faq.php" method="POST">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="faq_id" value="<?= htmlspecialchars($faq['id']); ?>">
                                <select name="status">
                                    <option value="active" <?= ($faq['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?= ($faq['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                                <button type="submit" class="button">Update</button>
                            </form>
                            <form action="edit_faq.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this FAQ?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="faq_id" value="<?= htmlspecialchars($faq['id']); ?>">
                                <button type="submit" class="delete-btn">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No FAQs have been added yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>