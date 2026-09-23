<?php
require_once 'dbconnect.php';

$search = isset($_POST['search']) ? trim($_POST['search']) : '';

$query = "SELECT * FROM places";
$params = [];

if ($search !== '') {
    $query .= " WHERE name LIKE :search";
    $params[':search'] = "%$search%";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$places = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($places as $place):
?>
    <tr>
        <td><?= htmlspecialchars($place['id']) ?></td>
        <td><?= htmlspecialchars($place['name']) ?></td>
        <td><?= htmlspecialchars($place['description']) ?></td>
        <td><?= htmlspecialchars($place['category']) ?></td>
        <td>
            <?php if (!empty($place['image'])): ?>
                <img src="uploads/<?= htmlspecialchars($place['image']) ?>" alt="<?= htmlspecialchars($place['name']) ?>" class="place-thumbnail">
            <?php else: ?>
                No image
            <?php endif; ?>
        </td>
        <td>
            <form method="post" class="action-form" onsubmit="return confirm('Delete this place?');" action="miscellaneous_process.php">
                <input type="hidden" name="delete_place_id" value="<?= $place['id'] ?>">
                <button type="submit" name="delete_place" class="btn-delete">Delete</button>
            </form>
        </td>
    </tr>
<?php endforeach; ?>