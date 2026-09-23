<?php
require_once 'dbconnect.php';

$method = isset($_POST['method']) ? trim($_POST['method']) : '';
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

$query = "SELECT p.*, b.id as booking_id 
          FROM payments p 
          JOIN bookings b ON p.booking_id = b.id";
$params = [];
$conditions = [];

if ($method !== '') {
    $conditions[] = "p.payment_method = :method";
    $params[':method'] = $method;
}
if ($status !== '') {
    $conditions[] = "p.status = :status";
    $params[':status'] = $status;
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($payments as $payment):
?>
    <tr>
        <td><?= htmlspecialchars($payment['id']) ?></td>
        <td><?= htmlspecialchars($payment['booking_id']) ?></td>
        <td><?= htmlspecialchars($payment['payment_method']) ?></td>
        <td>$<?= htmlspecialchars($payment['amount']) ?></td>
        <td><?= htmlspecialchars($payment['payment_date']) ?></td>
        <td><?= htmlspecialchars($payment['status']) ?></td>
        <td>
            <form method="post" class="action-form" onsubmit="return confirm('Delete this payment record?');" action="miscellaneous_process.php">
                <input type="hidden" name="delete_payment_id" value="<?= $payment['id'] ?>">
                <button type="submit" name="delete_payment" class="btn-delete">Delete</button>
            </form>
        </td>
    </tr>
<?php endforeach; ?>