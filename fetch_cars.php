<?php
require_once 'dbconnect.php';

$carName   = !empty($_GET['car_name']) ? trim($_GET['car_name']) : null;
$carStatus = !empty($_GET['car_status']) ? trim($_GET['car_status']) : null;
$carBrand  = !empty($_GET['car_brand']) ? trim($_GET['car_brand']) : null;

$query = "
    SELECT cars.*, 
           companies.company_name AS company_name,
           (
               SELECT car_images.image 
               FROM car_images 
               WHERE car_images.car_id = cars.id 
               ORDER BY car_images.id ASC 
               LIMIT 1
           ) AS car_image
    FROM cars
    LEFT JOIN companies ON cars.company_id = companies.id
    WHERE 1=1
";


$params = [];

if ($carName) {
    $query .= " AND cars.name LIKE :name";
    $params[':name'] = "%$carName%";
}

if ($carStatus) {
    $query .= " AND cars.availability_status = :status";
    $params[':status'] = $carStatus;
}

if ($carBrand) {
    $query .= " AND cars.brand = :brand";
    $params[':brand'] = $carBrand;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($cars) {
    foreach ($cars as $car): ?>
        <tr>
            <td><?= htmlspecialchars($car['id']) ?></td>
            <td>
                <?php if (!empty($car['car_image'])): ?>
                    <img src="uploads/car_images/<?= htmlspecialchars($car['car_image']) ?>" alt="Car Image" width="80">
                <?php else: ?>
                    No image
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($car['name']) ?></td>
            <td><?= htmlspecialchars($car['brand']) ?></td>
            <td><?= htmlspecialchars($car['seats']) ?></td>
            <td><?= htmlspecialchars($car['price_per_day']) ?></td>
            <td><?= htmlspecialchars($car['availability_status']) ?></td>
            <td><?= htmlspecialchars($car['company_name'] ?? 'N/A') ?></td>
            <td>
                <form method="post" action="car_process.php" onsubmit="return confirm('Are you sure?');">
                    <input type="hidden" name="delete_car_id" value="<?= $car['id'] ?>">
                    <button type="submit" name="delete_car" class="btn-delete">Delete</button>
                </form>
            </td>
        </tr>
<?php endforeach;
} else {
    echo "<tr><td colspan='9'>No cars found</td></tr>";
}
?>