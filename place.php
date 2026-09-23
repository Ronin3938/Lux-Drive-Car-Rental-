<?php
require_once 'dbconnect.php';


try {
    $categories_sql = "SELECT DISTINCT category FROM places ORDER BY category";
    $stmt_cat = $pdo->prepare($categories_sql);
    $stmt_cat->execute();
    $categories = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {

    $categories = [];
}

$selected_category = isset($_GET['category']) && $_GET['category'] !== '' ? $_GET['category'] : null;

$search_query = isset($_GET['search']) ? $_GET['search'] : '';


$sql = "
    SELECT 
        p.id, 
        p.name, 
        p.description,
        pi.image,
        p.category
    FROM places p
    LEFT JOIN place_images pi ON p.id = pi.place_id
";

// Start building the WHERE clause
$conditions = [];
$params = [];

if ($selected_category) {
    $conditions[] = "p.category = :category";
    $params[':category'] = $selected_category;
}

if (!empty($search_query)) {
    $conditions[] = "(p.name LIKE :search_query OR p.description LIKE :search_query)";
    $params[':search_query'] = '%' . $search_query . '%';
}

// Add WHERE clause if any conditions exist
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

$sql .= " ORDER BY p.id";

try {
    // Prepare and execute the query
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Places</title>
    <link rel="stylesheet" href="css/place.css">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<?php require_once 'header.php'; ?>

<body>
    <div class="container">
        <div class="header">
            <h1>Discover Our Destinations</h1>
        </div>

        <div class="filter-section">
            <form action="place.php" method="GET">
                <div class="search-box">
                    <input type="text" name="search" id="search-input" placeholder="Search places..." value="<?php echo htmlspecialchars($search_query); ?>">
                </div>
                <div class="category-filter">
                    <select name="category" id="category-filter">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['category']); ?>"
                                <?php echo $selected_category == $category['category'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['category']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="search-button">Search</button>
                <button type="button" class="show-all-button" onclick="window.location.href='place.php'">Show All</button>
            </form>
        </div>

        <div class="card-container">
            <?php
            if (count($result) > 0) {
                // Loop through the results and display each place
                foreach ($result as $row) {
                    $imagePath = !empty($row['image']) ? "uploads/place_images/" . htmlspecialchars($row['image']) : "https://placehold.co/400x300/E5E7EB/4B5563?text=No+Image";
            ?>
                    <div class="card">
                        <div class="card-image-container">
                            <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="card-image">
                        </div>
                        <div class="card-content">
                            <h2 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h2>
                            <p class="card-description"><?php echo htmlspecialchars($row['description']); ?></p>
                            <a href="rentcars.php?place_id=<?php echo htmlspecialchars($row['id']); ?>" class="rent-button">Lets Travel Now!</a>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo "<p class='no-results'>No places found matching the selected criteria.</p>";
            }
            ?>
        </div>
    </div>
    <?php require_once 'footer.php'; ?>
</body>

</html>