<?php
session_start();
require_once 'dbconnect.php'; // Make sure this initializes $pdo

$success_message = '';
$user_name = '';

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['user_name'])) {
    $user_name = $_SESSION['user_name'];
    unset($_SESSION['user_name']);
}

// ✅ Fetch top 3 best-rated cars
$query = "
    SELECT 
        c.id,
        c.name,
        c.brand,
        c.price_per_day,
        c.image,
        IFNULL(AVG(r.rating), 0) AS avg_rating,
        COUNT(r.id) AS total_reviews
    FROM cars c
    LEFT JOIN car_reviews r ON c.id = r.car_id
    GROUP BY c.id
    ORDER BY avg_rating DESC
    LIMIT 3
";

$stmt = $pdo->query($query);
$topCars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Lux Drive - Customer Home</title>
    <link rel="stylesheet" href="css/homepage.css">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="javascript/homepage.js" defer></script>
</head>

<body>
    <?php if (!empty($success_message) && !empty($user_name)): ?>
        <div class="success-box" id="successBox">
            <h2>🎉 <?php echo htmlspecialchars($success_message); ?> 🎉</h2>
            <p>Welcome, <?php echo htmlspecialchars($user_name); ?>! You can now explore the platform.</p>
        </div>
    <?php endif; ?>

    <?php require_once 'header.php'; ?>

    <!-- Slideshow -->
    <div class="slideshow">
        <img src="homepageimg/img1.jpg" alt="Slide 1" class="active">
        <img src="homepageimg/img2.jpg" alt="Slide 2">
        <img src="homepageimg/img3.jpg" alt="Slide 3">
        <img src="homepageimg/img4.jpg" alt="Slide 4">
        <img src="homepageimg/img5.jpg" alt="Slide 5">
        <img src="homepageimg/img6.jpg" alt="Slide 6">
    </div>

    <!-- Call-to-Action Section -->
    <div class="cta-section">
        <h2>🚗 Your Perfect Ride is Waiting!</h2>
        <p>Luxury, economy, SUV, or electric — we have the car that suits your style. Book now and hit the road with confidence!</p>
        <a href="rentcars.php" class="cta-button">Rent a Car Today</a>
    </div>

    <!-- 6 Box Services -->
    <div class="box-container">
        <a href="rentcars.php" class="box">
            <img src="homepageimg/luxury.jpg" alt="Luxury Cars">
            <div class="box-overlay">Luxury Cars</div>
        </a>
        <a href="rentcars.php" class="box">
            <img src="homepageimg/economy.jpg" alt="Economy Cars">
            <div class="box-overlay">Economy Cars</div>
        </a>
        <a href="rentcars.php" class="box">
            <img src="homepageimg/electric.jpg" alt="Electric Vehicle">
            <div class="box-overlay">Electric Vehicle</div>
        </a>
        <a href="rentcars.php" class="box">
            <img src="homepageimg/suv.jpg" alt="SUV">
            <div class="box-overlay">SUV</div>
        </a>
        <a href="rentcars.php" class="box">
            <img src="homepageimg/salon.jpg" alt="Salon">
            <div class="box-overlay">Salon</div>
        </a>
        <a href="rentcars.php" class="box">
            <img src="homepageimg/family.jpg" alt="Family Ride">
            <div class="box-overlay">Family Ride</div>
        </a>
    </div>

    <!-- Top 3 Best Rated Cars -->
    <div class="top-cars-section">
        <h2>⭐ Top Rated Cars</h2>
        <div class="top-cars-container">
            <?php foreach ($topCars as $car):
                // ✅ Fetch the FIRST image for this car from car_images
                $stmt = $pdo->prepare("SELECT image FROM car_images WHERE car_id = :car_id ORDER BY id ASC LIMIT 1");
                $stmt->execute([':car_id' => $car['id']]);
                $firstImage = $stmt->fetchColumn();

                // ✅ Fallback: if no image in car_images, use cars.image
                $carImage = $firstImage ?: $car['image'];
            ?>
                <div class="top-car-box">
                    <img src="uploads/car_images/<?= htmlspecialchars($carImage); ?>" alt="<?= htmlspecialchars($car['name']); ?>">
                    <h3><?= htmlspecialchars($car['name']); ?></h3>
                    <h2><?= htmlspecialchars($car['brand']); ?></h2>
                    <p>Price per day: $<?= htmlspecialchars($car['price_per_day']); ?></p>
                    <p>Rating: <?= number_format($car['avg_rating'], 1); ?> ⭐ (<?= $car['total_reviews']; ?> reviews)</p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="car_details.php?id=<?= $car['id']; ?>" class="cta-button">View Details</a>
                    <?php else: ?>
                        <a href="login.php" class="cta-button">Log in to View Details</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php
    // ✅ Fetch top 3 drivers by trip_count
    $topDriversQuery = "
        SELECT *
        FROM drivers
        ORDER BY trip_count DESC
        LIMIT 3
    ";
    $stmtDrivers = $pdo->prepare($topDriversQuery);
    $stmtDrivers->execute();
    $topDrivers = $stmtDrivers->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <!-- Top 3 Drivers Section -->
    <div class="top-drivers-section">
        <h2>🏆 Most Experienced Drivers</h2>
        <div class="top-drivers-container">
            <?php foreach ($topDrivers as $driver): ?>
                <?php
                $driverImage = !empty($driver['image'])
                    ? 'uploads/drivers/' . $driver['image']
                    : 'uploads/driver_images/default-driver.jpg';
                ?>
                <div class="top-driver-box">
                    <img src="<?= htmlspecialchars($driverImage); ?>" alt="<?= htmlspecialchars($driver['name']); ?>">
                    <h3><?= htmlspecialchars($driver['name']); ?></h3>
                    <p>Trips Completed: <?= htmlspecialchars($driver['trip_count']); ?></p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="car_details.php?id=<?= $driver['car_id']; ?>" class="cta-button">View Car Details</a>
                    <?php else: ?>
                        <a href="login.php" class="cta-button">Log in to View Details</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>



    <?php require_once 'footer.php'; ?>
    <?php require_once 'chatbot.php'; ?>
</body>

</html>