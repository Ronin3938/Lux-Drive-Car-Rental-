<?php
require_once 'dbconnect.php';

// Get filter/search inputs
$search = isset($_POST['search']) ? trim($_POST['search']) : '';
$rating = isset($_POST['rating']) && $_POST['rating'] !== '' ? intval($_POST['rating']) : null;

// Base query
$query = "
    SELECT cr.*, c.name AS car_name, u.name AS user_name
    FROM car_reviews cr
    JOIN cars c ON cr.car_id = c.id
    JOIN users u ON cr.user_id = u.id
";
$params = [];

// Apply filters
$conditions = [];

if ($search !== '') {
    $conditions[] = "(c.name LIKE :search OR u.name LIKE :search OR cr.review_text LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($rating !== null) {
    $conditions[] = "cr.rating = :rating";
    $params[':rating'] = $rating;
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(' AND ', $conditions);
}

$query .= " ORDER BY cr.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$carReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output table rows
foreach ($carReviews as $review):
?>
    <tr>
        <td><?= htmlspecialchars($review['id']) ?></td>
        <td><?= htmlspecialchars($review['car_name']) ?></td>
        <td><?= htmlspecialchars($review['user_name']) ?></td>
        <td><?= htmlspecialchars($review['rating']) ?> / 5</td>
        <td><?= htmlspecialchars($review['review_text']) ?></td>
        <td><?= htmlspecialchars($review['created_at']) ?></td>
        <td>
            <form method="post" class="action-form" onsubmit="return confirm('Delete this review?');" action="review_process.php">
                <input type="hidden" name="delete_car_review_id" value="<?= $review['id'] ?>">
                <button type="submit" name="delete_car_review" class="btn-delete">Delete</button>
            </form>
        </td>
    </tr>
<?php endforeach; ?>