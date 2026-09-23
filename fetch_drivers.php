<?php
require_once 'dbconnect.php';

$search = isset($_POST['search']) ? trim($_POST['search']) : '';

$query = "SELECT d.*, c.name as car_name FROM drivers d LEFT JOIN cars c ON d.car_id = c.id";

$params = [];
if ($search !== '') {
    $query .= " WHERE d.name LIKE :search";
    $params[':search'] = "%$search%";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($drivers as $driver):
?>
    <tr>
        <td><?= htmlspecialchars($driver['id']) ?></td>
        <td><?= htmlspecialchars($driver['name']) ?></td>
        <td><?= htmlspecialchars($driver['phone']) ?></td>
        <td><?= htmlspecialchars($driver['email']) ?></td>
        <td><?= htmlspecialchars($driver['license_number']) ?></td>
        <td><?= htmlspecialchars($driver['car_name']) ?></td>
        <td><?= htmlspecialchars($driver['status']) ?></td>
        <td>
            <form method="post" class="action-form" onsubmit="return confirm('Delete this driver?');" action="miscellaneous_process.php">
                <input type="hidden" name="delete_driver_id" value="<?= $driver['id'] ?>">
                <button type="submit" name="delete_driver" class="btn-delete">Delete</button>
            </form>
        </td>
    </tr>
<?php endforeach; ?>