<?php
// Start the session only once at the very top.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once 'dbconnect.php';

$logo_name = 'Lux Drive'; // Default name
$logo_image = ''; // Default image

try {
    $stmt_logo = $pdo->prepare("SELECT name, image FROM logo LIMIT 1");
    $stmt_logo->execute();
    $db_logo = $stmt_logo->fetch(PDO::FETCH_ASSOC);

    if ($db_logo) {
        $logo_name = htmlspecialchars($db_logo['name']);
        $logo_image = htmlspecialchars($db_logo['image']);
    }
} catch (PDOException $e) {
    error_log("Database error fetching logo: " . $e->getMessage());
    // Use default values on error
}

// Check if a user is logged in
if (!empty($_SESSION['user_id'])) {
    // Prepare and execute a query to get the user's name and profile picture.
    $stmt = $pdo->prepare("SELECT name, profile_picture FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // If the user data is found, update the session variables.
    if ($user) {
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['profile_picture'] = $user['profile_picture'];
    }
}

// Fetch car categories from the database
$car_categories = [];
try {
    $stmt_cats = $pdo->query("SELECT name, image FROM categories ORDER BY id ASC");
    $car_categories = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error fetching car categories: " . $e->getMessage());
}

// Hardcoded places data for the dropdown
$places = [
    ['name' => 'Yangon'],
    ['name' => 'Mandalay'],
    ['name' => 'Sagaing'],
    ['name' => 'Magway'],
    ['name' => 'Bago'],
    ['name' => 'Ayeyarwady'],
    ['name' => 'Tanintharyi'],
    ['name' => 'Kachin'],
    ['name' => 'Kayah'],
    ['name' => 'Kayin'],
    ['name' => 'Chin'],
    ['name' => 'Mon'],
    ['name' => 'Rakhine'],
    ['name' => 'Shan']
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lux Drive- Home</title>
    <link rel="stylesheet" href="css/header.css">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</head>

<body>
    <div class="fixed-top">
        <div class="discount1">
            <p style="text-align: center; padding-top: 0.4%;">
                !!! Discount 10% for first time ride !!!
                <a href="rentcars.php" style="text-decoration: none; color: white;">&nbsp;Click here to book!</a>
            </p>
        </div>

        <div class="header1">
            <div class="logo-area1">
                <a href="homepage.php" style="text-decoration: none;">
                    <img src="<?php echo $logo_image; ?>" alt="<?php echo $logo_name; ?>" class="logo-img">
                </a>
                <a href="homepage.php" style="text-decoration: none;">
                    <h1><?php echo $logo_name; ?></h1>
                </a>
            </div>

            <form action="rentcars.php" method="GET" class="search-box1">
                <input type="text" name="search_query" placeholder="Search cars or categories">
                <button type="submit">Search</button>
            </form>

            <div class="admin-btn-container1">
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="admin_dashboard.php" class="btn admin-btn">Admin Dashboard</a>
                    <?php endif; ?>
            </div>

            <div class="auth-area1">


                <div class="user-profile-container">
                    <a href="user_profile.php" title="My Profile">
                        <?php if (!empty($_SESSION['profile_picture'])): ?>
                            <img src="<?php echo htmlspecialchars($_SESSION['profile_picture']); ?>" alt="Profile" class="profile-pic">
                        <?php else: ?>
                            <ion-icon name="person-circle-outline" class="profile-icon"></ion-icon>
                        <?php endif; ?>
                    </a>
                    <?php if (!empty($_SESSION['user_name'])): ?>
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <a href="login.php" class="login-btn">Login</a>
                <a href="signup.php" class="Signup-btn">Sign Up</a>
            <?php endif; ?>
            </div>
        </div>


        <nav>
            <ul>
                <li><a href="homepage.php">Home</a></li>
                <li><a href="rentcars.php">Cars</a>
                    <ul>
                        <?php foreach ($car_categories as $category): ?>
                            <?php
                            $image_path = !empty($category['image']) ? "uploads/categories/" . $category['image'] : "https://placehold.co/70x70/A8D8EA/394A6D?text=Car";
                            ?>
                            <li>
                                <a href="rentcars.php?category=<?php echo urlencode($category['name']); ?>" class="dropdown-item-content">
                                    <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" class="circle-image">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li class="places-dropdown-parent">Places
                    <div class="places-dropdown-container">
                        <div class="dropdown-column">
                            <h2>Regions</h2>
                            <ul>
                                <?php for ($i = 0; $i < 7; $i++): ?>
                                    <li>
                                        <a href="place.php?place=<?php echo urlencode($places[$i]['name']); ?>" class="dropdown-item-content">
                                            <img src="uploads/placeimage/placeimg<?php echo $i + 1; ?>.jpg" alt="<?php echo htmlspecialchars($places[$i]['name']); ?> image" class="circle-image">
                                            <?php echo htmlspecialchars($places[$i]['name']); ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </div>
                        <div class="dropdown-column">
                            <h2>States</h2>
                            <ul>
                                <?php for ($i = 7; $i < 14; $i++): ?>
                                    <li>
                                        <a href="place.php?place=<?php echo urlencode($places[$i]['name']); ?>" class="dropdown-item-content">
                                            <img src="uploads/placeimage/placeimg<?php echo $i + 1; ?>.jpg" alt="<?php echo htmlspecialchars($places[$i]['name']); ?> image" class="circle-image">
                                            <?php echo htmlspecialchars($places[$i]['name']); ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </div>
                    </div>
                </li>
                <li><a href="booking_history.php">Bookings</a></li>
                <li><a href="about_us.php">About Us</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
            <div class="nav-right">
                <a href="rewards.php" title="Rewards">Rewards ⭐</a>
                <a href="wishlist.php" title="Wishlist">Wishlist ❤️</a>
            </div>
        </nav>
    </div>
    <script src="javascript/header.js"></script>
</body>

</html>