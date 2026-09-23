<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once "dbconnect.php"; // must set up $pdo connection

$errors = [];
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id        = trim($_POST['user_id'] ?? '');
    $company_name   = trim($_POST['company_name'] ?? '');
    $address        = trim($_POST['address'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');

    if (!$user_id || !$company_name || !$address || !$contact_person) {
        $errors[] = "Please fill all fields.";
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // 1. Insert into companies
            $sql = "INSERT INTO companies (user_id, company_name, address, contact_person)
                VALUES (:user_id, :company_name, :address, :contact_person)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':user_id'        => $user_id,
                ':company_name'   => $company_name,
                ':address'        => $address,
                ':contact_person' => $contact_person
            ]);

            // 2. Update user role to 'company'
            $updateRole = $pdo->prepare("UPDATE users SET role = 'company' WHERE id = :id");
            $updateRole->execute([':id' => $user_id]);

            $pdo->commit();

            $message = "Company added successfully, and user role updated to company.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Insert failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Add Company</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        form {
            max-width: 500px;
            margin: 30px auto;
            background-color: #fff;
            padding: 25px 20px 20px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        /* Back button inside form */
        .back-btn {
            position: absolute;
            top: 5px;
            right: 15px;
            font-size: 1.5rem;
            text-decoration: none;
            color: #fff;
            background-color: #047e2fff;
            padding: 5px 10px;
            border-radius: 50%;
            transition: background-color 0.3s;
        }

        .back-btn:hover {
            background-color: #0ce1bdff;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="number"]:focus {
            border-color: #00897b;
            outline: none;
        }

        button {
            background-color: #00897b;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #00695c;
        }

        .message {
            max-width: 500px;
            margin: 10px auto;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .errors {
            background-color: #f8d7da;
            color: #721c24;
        }

        .errors ul {
            margin: 0;
            padding-left: 20px;
        }
    </style>
</head>

<body>

    <h1>Add New Company</h1>

    <?php if ($message): ?>
        <div class="message success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="message errors">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST">
        <a href="admin_dashboard.php" class="back-btn">×</a>

        <label>User ID:</label>
        <input type="number" name="user_id" required>

        <label>Company Name:</label>
        <input type="text" name="company_name" required>

        <label>Address:</label>
        <input type="text" name="address" required>

        <label>Contact Person:</label>
        <input type="text" name="contact_person" required>

        <button type="submit">Add Company</button>
    </form>

</body>

</html>