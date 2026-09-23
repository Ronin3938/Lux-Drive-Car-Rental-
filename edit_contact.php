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
$edit_contact = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? null;

    if ($action === 'add' || $action === 'edit') {
        $contact_type = trim($_POST['contact_type'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone_number = trim($_POST['phone_number'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if (!empty($contact_type) && !empty($status)) {
            try {
                if ($action === 'add') {
                    $stmt = $pdo->prepare("INSERT INTO contact_details (contact_type, email, phone_number, address, status) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$contact_type, $email, $phone_number, $address, $status]);
                    $message = 'success_add';
                } elseif ($action === 'edit' && $id) {
                    $stmt = $pdo->prepare("UPDATE contact_details SET contact_type = ?, email = ?, phone_number = ?, address = ?, status = ? WHERE id = ?");
                    $stmt->execute([$contact_type, $email, $phone_number, $address, $status, $id]);
                    $message = 'success_edit';
                }
            } catch (PDOException $e) {
                $message = 'error';
                error_log("Contact form error: " . $e->getMessage());
            }
        } else {
            $message = 'invalid';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            try {
                $stmt = $pdo->prepare("DELETE FROM contact_details WHERE id = ?");
                $stmt->execute([$id]);
                $message = 'success_delete';
            } catch (PDOException $e) {
                $message = 'error';
                error_log("Contact delete error: " . $e->getMessage());
            }
        }
    }
}

// Check for edit request via GET
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM contact_details WHERE id = ?");
        $stmt->execute([$id]);
        $edit_contact = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $edit_contact = null;
    }
}

// Fetch all contact details to display in a list
try {
    $stmt = $pdo->query("SELECT * FROM contact_details ORDER BY id DESC");
    $allContacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $allContacts = [];
    error_log("Contact fetch error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Contact - Admin</title>
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
            text-align: center;
            text-decoration: none;
        }

        .button:hover {
            background-color: #34495e;
        }

        .contact-list {
            margin-top: 40px;
        }

        .contact-list h2 {
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .contact-item-display {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .contact-content {
            flex-grow: 1;
            padding-right: 20px;
        }

        .contact-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .edit-btn,
        .delete-btn {
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            border: none;
            color: white;
            text-decoration: none;
        }

        .edit-btn {
            background-color: #3498db;
        }

        .edit-btn:hover {
            background-color: #2980b9;
        }

        .delete-btn {
            background-color: #e74c3c;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1><?= $edit_contact ? 'Edit Contact' : 'Add New Contact'; ?></h1>

        <?php if ($message === 'success_add'): ?>
            <div class="message success">Contact added successfully!</div>
        <?php elseif ($message === 'success_edit'): ?>
            <div class="message success">Contact updated successfully!</div>
        <?php elseif ($message === 'success_delete'): ?>
            <div class="message success">Contact deleted successfully!</div>
        <?php elseif ($message === 'error'): ?>
            <div class="message error">An error occurred. Please try again.</div>
        <?php elseif ($message === 'invalid'): ?>
            <div class="message invalid">Please fill out all required fields.</div>
        <?php endif; ?>

        <form action="edit_contact.php" method="POST">
            <input type="hidden" name="action" value="<?= $edit_contact ? 'edit' : 'add'; ?>">
            <input type="hidden" name="id" value="<?= htmlspecialchars($edit_contact['id'] ?? ''); ?>">

            <div class="form-group">
                <label for="contact_type">Contact Type:</label>
                <input type="text" id="contact_type" name="contact_type" value="<?= htmlspecialchars($edit_contact['contact_type'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="text" id="email" name="email" value="<?= htmlspecialchars($edit_contact['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="phone_number">Phone Number:</label>
                <input type="text" id="phone_number" name="phone_number" value="<?= htmlspecialchars($edit_contact['phone_number'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address"><?= htmlspecialchars($edit_contact['address'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="status">Status:</label>
                <select id="status" name="status">
                    <option value="active" <?= ($edit_contact['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?= ($edit_contact['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <button type="submit" class="button"><?= $edit_contact ? 'Update Contact' : 'Add Contact'; ?></button>
        </form>

        <div class="contact-list">
            <h2>Current Contact Details</h2>
            <?php if (!empty($allContacts)): ?>
                <?php foreach ($allContacts as $contact): ?>
                    <div class="contact-item-display">
                        <div class="contact-content">
                            <h4><?= htmlspecialchars($contact['contact_type']); ?> (<?= htmlspecialchars($contact['status']); ?>)</h4>
                            <p><?= htmlspecialchars($contact['email']); ?></p>
                        </div>
                        <div class="contact-actions">
                            <a href="edit_contact.php?action=edit&id=<?= htmlspecialchars($contact['id']); ?>" class="edit-btn">Edit</a>
                            <form action="edit_contact.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this contact?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($contact['id']); ?>">
                                <button type="submit" class="delete-btn">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No contact details have been added yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>