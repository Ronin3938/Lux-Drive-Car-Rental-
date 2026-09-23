<?php
session_start();
require_once 'dbconnect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect non-admin users to login page
    header('Location: login.php');
    exit;
}

// Data retrieval for dashboard sections
$users = $pdo->query("
    SELECT *,
    TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) AS age
    FROM users
")->fetchAll(PDO::FETCH_ASSOC);
$places = $pdo->query("SELECT * FROM places")->fetchAll(PDO::FETCH_ASSOC);
$companies = $pdo->query("SELECT * FROM companies")->fetchAll(PDO::FETCH_ASSOC);
$cars = $pdo->query("SELECT * FROM cars")->fetchAll(PDO::FETCH_ASSOC);
$bookings = $pdo->query("
    SELECT
        b.id, b.start_date, b.end_date, b.total_price, b.status,b.pickup_address,b.dropoff_lon,b.dropoff_lat,b.pickup_lon,b.pickup_lat,
        u.name AS user_name,
        c.name AS car_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN cars c ON b.car_id = c.id
")->fetchAll(PDO::FETCH_ASSOC);


// New data retrieval queries
$carReviews = $pdo->query("
    SELECT cr.*, c.name as car_name, u.name as user_name
    FROM car_reviews cr
    JOIN cars c ON cr.car_id = c.id
    JOIN users u ON cr.user_id = u.id
")->fetchAll(PDO::FETCH_ASSOC);

$terms = $pdo->query("SELECT * FROM terms_and_conditions")->fetchAll(PDO::FETCH_ASSOC);
$faqs = $pdo->query("SELECT * FROM faq")->fetchAll(PDO::FETCH_ASSOC);
$contacts = $pdo->query("SELECT * FROM contact_details")->fetchAll(PDO::FETCH_ASSOC);
$drivers = $pdo->query("
    SELECT d.*, c.name as car_name
    FROM drivers d
    JOIN cars c ON d.car_id = c.id
")->fetchAll(PDO::FETCH_ASSOC);
$payments = $pdo->query("
    SELECT p.*, b.id as booking_id
    FROM payments p
    JOIN bookings b ON p.booking_id = b.id
")->fetchAll(PDO::FETCH_ASSOC);
$rewards = $pdo->query("
    SELECT r.*, u.name as user_name
    FROM rewards r
    JOIN users u ON r.user_id = u.id
")->fetchAll(PDO::FETCH_ASSOC);
$wishlists = $pdo->query("
    SELECT w.*, u.name as user_name, c.name as car_name
    FROM wishlists w
    JOIN users u ON w.user_id = u.id
    JOIN cars c ON w.car_id = c.id
")->fetchAll(PDO::FETCH_ASSOC);

$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// Monthly income (group by month, only paid)
$monthlyIncome = $pdo->query("
    SELECT DATE_FORMAT(payment_date, '%Y-%m') AS month, SUM(amount) AS total
    FROM payments
    WHERE status = 'paid'
    GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
    ORDER BY month ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Cars with average rating
$carRatings = $pdo->query("
    SELECT c.name AS car_name, AVG(cr.rating) AS avg_rating, COUNT(cr.id) as review_count
    FROM cars c
    LEFT JOIN car_reviews cr ON c.id = cr.car_id
    GROUP BY c.id
    ORDER BY avg_rating DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Drivers with trip counts
$driverTrips = $pdo->query("
    SELECT d.name AS driver_name, COUNT(b.id) AS trip_count
    FROM drivers d
    LEFT JOIN cars c ON d.car_id = c.id
    LEFT JOIN bookings b ON c.id = b.car_id
    GROUP BY d.id
    ORDER BY trip_count DESC
")->fetchAll(PDO::FETCH_ASSOC);
// Company with the highest income
$topCompany = $pdo->query("
    SELECT comp.company_name, SUM(p.amount) AS total_income
    FROM payments p
    JOIN bookings b ON p.booking_id = b.id
    JOIN cars c ON b.car_id = c.id
    JOIN companies comp ON c.company_id = comp.id
    WHERE p.status = 'paid'
    GROUP BY comp.id
    ORDER BY total_income DESC
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);
// Customers who paid the most
$topCustomers = $pdo->query("
    SELECT u.name AS customer_name, SUM(p.amount) AS total_spent
    FROM payments p
    JOIN bookings b ON p.booking_id = b.id
    JOIN users u ON b.user_id = u.id
    WHERE p.status = 'paid'
    GROUP BY u.id
    ORDER BY total_spent DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);



$errorMessage = null;
if (isset($_SESSION['error_message'])) {
    $errorMessage = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
$successMessage = null;
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
$roleFilter = isset($_GET['role_filter']) ? trim($_GET['role_filter']) : '';

if ($roleFilter !== '') {
    $stmt = $pdo->prepare("
        SELECT *,
        TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) AS age
        FROM users
        WHERE role = :role
    ");
    $stmt->execute([':role' => $roleFilter]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->query("
        SELECT *,
        TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) AS age
        FROM users
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch current logo information
$stmt = $pdo->prepare("SELECT name, image FROM logo LIMIT 1");
$stmt->execute();
$current_logo = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/admin_dashboard.css">
</head>

<body>

    <div class="container">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h3>Admin Panel</h3>
                <a href="homepage.php" class="back-home-btn">⬅ Back to Home</a>
            </div>
            <nav class="sidebar-nav">
                <a href="#" data-section-id="usermanagement" class="nav-link active">
                    <span>👤</span> User Management
                </a>
                <a href="#" data-section-id="cars" class="nav-link">
                    <span>🚗</span> Car Management
                </a>
                <a href="#" data-section-id="car-categories" class="nav-link">
                    <span>📂</span> Car Categories Management
                </a>
                <a href="#" data-section-id="bookings" class="nav-link">
                    <span>📅</span> Booking Management
                </a>
                <a href="#" data-section-id="companies" class="nav-link">
                    <span>🏢</span> Company Management
                </a>
                <a href="#" data-section-id="drivers" class="nav-link">
                    <span>👨‍✈️</span> Driver Management
                </a>
                <a href="#" data-section-id="places" class="nav-link">
                    <span>🗺️</span> Places Management
                </a>
                <a href="#" data-section-id="payments" class="nav-link">
                    <span>💳</span> Payment Management
                </a>
                <a href="#" data-section-id="rewards" class="nav-link">
                    <span>🏆</span> Rewards
                </a>
                <a href="#" data-section-id="wishlist" class="nav-link">
                    <span>❤️</span> Wishlist
                </a>
                <a href="#" data-section-id="reviews" class="nav-link">
                    <span>⭐</span> Reviews & Ratings
                </a>
                <a href="#" data-section-id="terms-conditions" class="nav-link">
                    <span>📝</span> Terms & Conditions
                </a>
                <a href="#" data-section-id="faq" class="nav-link">
                    <span>❓</span> FAQ
                </a>
                <a href="#" data-section-id="contact" class="nav-link">
                    <span>📞</span> Contact
                </a>
                <a href="#" data-section-id="reports" class="nav-link">
                    <span>📊</span> Reports & Analytics
                </a>
                <a href="#" data-section-id="logo" class="nav-link">
                    <span>🚗</span> Logo
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                <button class="menu-toggle">☰</button>
                <h2>Admin Dashboard</h2>
            </header>
            <section id="usermanagement" class="section active">
                <h3>User Management</h3>
                <div class="filter-container">
                    <form method="get" action="">
                        <label for="role_filter">Filter by Role:</label>
                        <select name="role_filter" id="role_filter" onchange="this.form.submit()">
                            <option value="">All</option>
                            <option value="customer" <?= (isset($_GET['role_filter']) && $_GET['role_filter'] === 'customer') ? 'selected' : '' ?>>Customer</option>
                            <option value="company" <?= (isset($_GET['role_filter']) && $_GET['role_filter'] === 'company') ? 'selected' : '' ?>>Company</option>
                            <option value="admin" <?= (isset($_GET['role_filter']) && $_GET['role_filter'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </form>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Gender</th>
                                <th>Age</th>
                                <th>Address</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['id']) ?></td>
                                    <td><?= htmlspecialchars($user['name']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= htmlspecialchars($user['phone']) ?></td>
                                    <td><?= htmlspecialchars($user['gender']) ?></td>
                                    <td><?= htmlspecialchars($user['age']) ?></td>
                                    <td><?= htmlspecialchars($user['address']) ?></td>
                                    <td><?= htmlspecialchars($user['role']) ?></td>
                                    <td>
                                        <form method="post" class="action-form" action="user_process.php">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <select name="role" onchange="this.form.submit()">
                                                <option value="customer" <?= $user['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                                <option value="company" <?= $user['role'] === 'company' ? 'selected' : '' ?>>Company</option>
                                            </select>
                                        </form>
                                        <form method="post" class="action-form" onsubmit="return confirm('Are you sure you want to delete this user?');" action="user_process.php">
                                            <input type="hidden" name="delete_user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" name="delete_user" class="btn-delete">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="cars" class="section">
                <h3>Car Management</h3>
                <div class="action-buttons">
                    <form action="add_car.php" method="get" style="display:inline;">
                        <button type="submit" class="btn-add">➕ Add New Car</button>
                    </form>
                    <form action="check_noti.php" method="get" style="display:inline;">
                        <button type="submit" class="btn-add">Check Notifications</button>
                    </form>
                </div>
                <div class="filter-container">
                    <label for="car_name">Search Car Name:</label>
                    <input type="text" id="car_name" placeholder="Enter car name">

                    <label for="car_status">Filter by Availability:</label>
                    <select id="car_status">
                        <option value="">All</option>
                        <option value="available">Available</option>
                        <option value="booked">Booked</option>
                        <option value="maintenance">Maintenance</option>
                    </select>

                    <label for="car_brand">Filter by Brand:</label>
                    <select id="car_brand">
                        <option value="">All</option>
                        <?php
                        require_once 'dbconnect.php';
                        $brands = $pdo->query("SELECT DISTINCT brand FROM cars")->fetchAll(PDO::FETCH_COLUMN);
                        foreach ($brands as $brand) {
                            echo "<option value='" . htmlspecialchars($brand) . "'>" . htmlspecialchars($brand) . "</option>";
                        }
                        ?>
                    </select>

                    <button type="button" id="reset_filters">Reset</button>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Brand</th>
                                <th>Seats</th>
                                <th>Price/Day</th>
                                <th>Status</th>
                                <th>Company</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="cars_table_body">
                            <!-- Data will be loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>
            </section>

            <script>
                function loadCars() {
                    const name = document.getElementById('car_name').value;
                    const status = document.getElementById('car_status').value;
                    const brand = document.getElementById('car_brand').value;

                    const params = new URLSearchParams();
                    if (name) params.append('car_name', name);
                    if (status) params.append('car_status', status);
                    if (brand) params.append('car_brand', brand);

                    const url = params.toString() ? 'fetch_cars.php?' + params.toString() : 'fetch_cars.php';

                    fetch(url)
                        .then(response => response.text())
                        .then(data => {
                            document.getElementById('cars_table_body').innerHTML = data;
                        })
                        .catch(error => console.error('Error loading cars:', error));
                }

                // Event listeners
                document.getElementById('car_name').addEventListener('input', loadCars);
                document.getElementById('car_status').addEventListener('change', loadCars);
                document.getElementById('car_brand').addEventListener('change', loadCars);
                document.getElementById('reset_filters').addEventListener('click', function() {
                    document.getElementById('car_name').value = '';
                    document.getElementById('car_status').value = '';
                    document.getElementById('car_brand').value = '';
                    loadCars();
                });

                document.addEventListener('DOMContentLoaded', loadCars);
            </script>

            <section id="car-categories" class="section">
                <h3>Car Categories</h3>
                <div class="form-container">
                    <h4>Add New Category</h4>
                    <form method="post" class="add-form" enctype="multipart/form-data" action="miscellaneous_process.php">
                        <input type="text" name="category_name" placeholder="Category Name" required>
                        <input type="file" name="category_image">
                        <button type="submit" name="add_category" class="btn-add">Add Category</button>
                    </form>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Image</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><?= htmlspecialchars($cat['id']) ?></td>
                                    <td><?= htmlspecialchars($cat['name']) ?></td>
                                    <td>
                                        <?php if (!empty($cat['image'])): ?>
                                            <img src="uploads/categories/<?= htmlspecialchars($cat['image']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" class="category-thumbnail">
                                        <?php else: ?>
                                            No Image
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form action="edit_category.php" method="get" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars($cat['id']); ?>">
                                            <button type="submit" class="btn-edit">Edit</button>
                                        </form>
                                        <form method="post" class="action-form" onsubmit="return confirm('Delete this category?');" action="miscellaneous_process.php">
                                            <input type="hidden" name="delete_category_id" value="<?= $cat['id'] ?>">
                                            <button type="submit" name="delete_category" class="btn-delete">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="bookings" class="section">
                <h3>Booking Management</h3>
                <div class="filter-container">
                    <label for="booking_status">Filter by Status:</label>
                    <select id="booking_status">
                        <option value="">All</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <button type="button" id="reset_booking_filter">Reset</button>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User Name</th>
                                <th>Car Name</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Pickup Latitude</th>
                                <th>Pickup Longitude</th>
                                <th>Dropoff Latitude</th>
                                <th>Dropoff Longitude</th>
                                <th>Total Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="bookings_table_body">
                            <!-- Rows loaded via AJAX -->
                        </tbody>
                    </table>
                </div>

            </section>
            <script>
                function loadBookings() {
                    const status = document.getElementById('booking_status').value;

                    const xhr = new XMLHttpRequest();
                    xhr.open('GET', `fetch_bookings.php?booking_status=${encodeURIComponent(status)}`, true);
                    xhr.onload = function() {
                        if (this.status === 200) {
                            document.getElementById('bookings_table_body').innerHTML = this.responseText;
                        }
                    };
                    xhr.send();
                }

                // Event listeners
                document.getElementById('booking_status').addEventListener('change', loadBookings);
                document.getElementById('reset_booking_filter').addEventListener('click', function() {
                    document.getElementById('booking_status').value = '';
                    loadBookings();
                });

                // Initial load
                window.onload = function() {
                    loadBookings(); // Load bookings initially
                };
            </script>


            <section id="companies" class="section">
                <h3>Company Management</h3>
                <div class="action-buttons">
                    <form action="add_company.php" method="get" style="display:inline;">
                        <button type="submit" class="btn-add">➕ Add New Company</button>
                    </form>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Company Name</th>
                                <th>Contact Person</th>
                                <th>Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($companies as $comp): ?>
                                <tr>
                                    <td><?= htmlspecialchars($comp['id']) ?></td>
                                    <td><?= htmlspecialchars($comp['company_name']) ?></td>
                                    <td><?= htmlspecialchars($comp['contact_person']) ?></td>
                                    <td><?= htmlspecialchars($comp['address']) ?></td>
                                    <td>
                                        <form method="post" class="action-form" onsubmit="return confirm('Delete this company?');" action="miscellaneous_process.php">
                                            <input type="hidden" name="delete_company_id" value="<?= $comp['id'] ?>">
                                            <button type="submit" name="delete_company" class="btn-delete">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="drivers" class="section">
                <h3>Driver Management</h3>
                <div class="action-buttons">
                    <form action="add_driver.php" method="get" style="display:inline;">
                        <button type="submit" class="btn-add">➕ Add New Driver</button>
                    </form>
                </div>

                <div class="filter-container" style="margin:10px 0;">
                    <label for="driver_search">Search by Name:</label>
                    <input type="text" id="driver_search" placeholder="Enter driver name...">
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>License #</th>
                                <th>Assigned Car</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="drivers_table_body">
                            <?php foreach ($drivers as $driver): ?>
                                <tr>
                                    <td><?= htmlspecialchars($driver['id']) ?></td>
                                    <td><?= htmlspecialchars($driver['name']) ?></td>
                                    <td><?= htmlspecialchars($driver['phone']) ?></td>
                                    <td><?= htmlspecialchars($driver['email']) ?></td>
                                    <td><?= htmlspecialchars($driver['license_number']) ?></td>
                                    <td><?= htmlspecialchars($driver['car_name']) ?></td>
                                    <td><?= htmlspecialchars($driver['status']) ?></td>
                                    <td>
                                        <a href="edit_driver.php?id=<?= $driver['id'] ?>" class="btn-edit">Edit</a>
                                        <form method="post" class="action-form" onsubmit="return confirm('Delete this driver?');" action="miscellaneous_process.php">
                                            <input type="hidden" name="delete_driver_id" value="<?= $driver['id'] ?>">
                                            <button type="submit" name="delete_driver" class="btn-delete">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <script>
                const driverSearchInput = document.getElementById('driver_search');
                const driverTableBody = document.querySelector('#drivers tbody');

                driverSearchInput.addEventListener('keyup', function() {
                    const search = this.value;

                    fetch('fetch_drivers.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'search=' + encodeURIComponent(search)
                        })
                        .then(response => response.text())
                        .then(data => {
                            driverTableBody.innerHTML = data;
                        });
                });
            </script>

            <section id="places" class="section">
                <h3>Places</h3>
                <div class="action-buttons">
                    <form action="add_place.php" method="get" style="display:inline;">
                        <button type="submit" class="btn-add">➕ Add New Place</button>
                    </form>
                </div>

                <div class="filter-container" style="margin:10px 0;">
                    <label for="place_search">Search by Name:</label>
                    <input type="text" id="place_search" placeholder="Enter place name...">
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Image</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="places_table_body">
                            <?php foreach ($places as $place): ?>
                                <tr>
                                    <td><?= htmlspecialchars($place['id']) ?></td>
                                    <td><?= htmlspecialchars($place['name']) ?></td>
                                    <td><?= htmlspecialchars($place['description']) ?></td>
                                    <td><?= htmlspecialchars($place['category']) ?></td>
                                    <td>
                                        <?php if (!empty($place['image'])): ?>
                                            <img src="uploads/place_images/<?= htmlspecialchars($place['image']) ?>" alt="<?= htmlspecialchars($place['name']) ?>" class="place-thumbnail">
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
                        </tbody>
                    </table>
                </div>
            </section>
            <script>
                const placeSearchInput = document.getElementById('place_search');
                const placesTableBody = document.getElementById('places_table_body');

                placeSearchInput.addEventListener('keyup', function() {
                    const search = this.value;

                    fetch('fetch_places.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'search=' + encodeURIComponent(search)
                        })
                        .then(response => response.text())
                        .then(data => {
                            placesTableBody.innerHTML = data;
                        });
                });
            </script>


            <section id="payments" class="section">
                <h3>Payment Management</h3>

                <div class="filter-container" style="margin:10px 0;">
                    <label for="payment_method_filter">Filter by Method:</label>
                    <select id="payment_method_filter">
                        <option value="">All</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="Paypal">Paypal</option>
                        <option value="Cash">Cash</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                    </select>

                    <label for="payment_status_filter" style="margin-left:15px;">Filter by Status:</label>
                    <select id="payment_status_filter">
                        <option value="">All</option>
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Booking ID</th>
                                <th>Method</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="payments_table_body">
                            <?php foreach ($payments as $payment): ?>
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
                        </tbody>
                    </table>
                </div>
            </section>
            <script>
                const paymentMethodFilter = document.getElementById('payment_method_filter');
                const paymentStatusFilter = document.getElementById('payment_status_filter');
                const paymentsTableBody = document.getElementById('payments_table_body');

                function fetchPayments() {
                    const method = paymentMethodFilter.value;
                    const status = paymentStatusFilter.value;

                    fetch('fetch_payments.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'method=' + encodeURIComponent(method) + '&status=' + encodeURIComponent(status)
                        })
                        .then(response => response.text())
                        .then(data => {
                            paymentsTableBody.innerHTML = data;
                        });
                }

                paymentMethodFilter.addEventListener('change', fetchPayments);
                paymentStatusFilter.addEventListener('change', fetchPayments);
            </script>


            <section id="rewards" class="section">
                <h3>Rewards</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Points</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rewards as $reward): ?>
                                <tr>
                                    <td><?= htmlspecialchars($reward['id']) ?></td>
                                    <td><?= htmlspecialchars($reward['user_name']) ?></td>
                                    <td><?= htmlspecialchars($reward['points']) ?></td>
                                    <td><?= htmlspecialchars($reward['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="wishlist" class="section">
                <h3>Wishlist</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Car</th>
                                <th>Added At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($wishlists as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['user_name']) ?></td>
                                    <td><?= htmlspecialchars($item['car_name']) ?></td>
                                    <td><?= htmlspecialchars($item['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="reviews" class="section">
                <h3>Reviews & Ratings</h3>

                <div class="filters">
                    <input type="text" id="reviewSearch" placeholder="Search by car, user, or text">
                    <select id="reviewRating">
                        <option value="">All Ratings</option>
                        <option value="5">5 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="2">2 Stars</option>
                        <option value="1">1 Star</option>
                    </select>
                    <button id="resetReviews">Reset</button>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Car</th>
                                <th>User</th>
                                <th>Rating</th>
                                <th>Review</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="reviewsTableBody">
                            <!-- AJAX-loaded rows will appear here -->
                        </tbody>
                    </table>
                </div>
            </section>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const searchInput = document.getElementById('reviewSearch');
                    const ratingSelect = document.getElementById('reviewRating');
                    const resetBtn = document.getElementById('resetReviews');
                    const tableBody = document.getElementById('reviewsTableBody');

                    // Function to fetch reviews
                    function fetchReviews() {
                        const formData = new FormData();
                        formData.append('search', searchInput.value);
                        formData.append('rating', ratingSelect.value);

                        fetch('fetch_reviews.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.text())
                            .then(data => {
                                tableBody.innerHTML = data;
                            })
                            .catch(error => console.error('Error fetching reviews:', error));
                    }

                    // Event listeners
                    searchInput.addEventListener('input', fetchReviews);
                    ratingSelect.addEventListener('change', fetchReviews);
                    resetBtn.addEventListener('click', function() {
                        searchInput.value = '';
                        ratingSelect.value = '';
                        fetchReviews();
                    });

                    // Initial load
                    fetchReviews();
                });
            </script>


            <section id="terms-conditions" class="section">
                <h3>Terms & Conditions</h3>
                <div class="form-container">
                    <form action="edit_terms.php" method="post">
                        <button type="submit" class="btn-add">Add/Update Terms</button>
                    </form>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Version Number</th>
                                <th>Effective Date</th>
                                <th>Content</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($terms as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['id']) ?></td>
                                    <td><?= htmlspecialchars($item['version_number']) ?></td>
                                    <td><?= htmlspecialchars($item['effective_date']) ?></td>
                                    <td><?= nl2br(htmlspecialchars($item['content'])) ?></td>
                                    <td>
                                        <form method="post" class="action-form" onsubmit="return confirm('Delete this entry?');" action="content_process.php">
                                            <input type="hidden" name="delete_terms_id" value="<?= $item['id'] ?>">
                                            <button type="submit" name="delete_terms" class="btn-delete">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="faq" class="section">
                <h3>FAQ Management</h3>
                <div class="form-container">
                    <h4>Add New FAQ</h4>
                    <form method="post" class="add-form" action="content_process.php">
                        <input type="text" name="question" placeholder="Question" required>
                        <textarea name="answer" placeholder="Answer" rows="3" required></textarea>
                        <button type="submit" name="add_faq" class="btn-add">Add FAQ</button>
                    </form>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Question</th>
                                <th>Answer</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($faqs as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['id']) ?></td>
                                    <td><?= htmlspecialchars($item['question']) ?></td>
                                    <td><?= nl2br(htmlspecialchars($item['answer'])) ?></td>
                                    <td>
                                        <form method="post" class="action-form" onsubmit="return confirm('Delete this FAQ?');" action="content_process.php">
                                            <input type="hidden" name="delete_faq_id" value="<?= $item['id'] ?>">
                                            <button type="submit" name="delete_faq" class="btn-delete">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="contact" class="section">
                <h3>Contact Information</h3>
                <div class="form-container">
                    <h4>Add New Contact Info</h4>
                    <form method="post" class="add-form" action="content_process.php">
                        <input type="contact_type" name="contact_type" placeholder="contact_type" required>
                        <input type="email" name="email" placeholder="Email" required>
                        <input type="text" name="phone_number" placeholder="Phone Number">
                        <input type="text" name="address" placeholder="Address">
                        <button type="submit" name="add_contact" class="btn-add">Add Contact</button>
                    </form>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contacts as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['id']) ?></td>
                                    <td><?= htmlspecialchars($item['contact_type']) ?></td>
                                    <td><?= htmlspecialchars($item['email']) ?></td>
                                    <td><?= htmlspecialchars($item['phone_number']) ?></td>
                                    <td><?= htmlspecialchars($item['address']) ?></td>
                                    <td>
                                        <form action="edit_contact.php" method="get" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars($item['id']); ?>">
                                            <button type="submit" class="btn-edit">Edit</button>
                                        </form>
                                        <form method="post" class="action-form" onsubmit="return confirm('Delete this contact?');" action="content_process.php">
                                            <input type="hidden" name="delete_contact_id" value="<?= $item['id'] ?>">
                                            <button type="submit" name="delete_contact" class="btn-delete">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php
            // Calculate total income
            try {
                $stmt = $pdo->query("SELECT SUM(amount) AS total_income FROM payments");
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $totalIncome = $row && $row['total_income'] ? (float)$row['total_income'] : 0;
            } catch (Exception $e) {
                $totalIncome = 0;
            }
            ?>


            <section id="reports" class="section">
                <h3>Reports & Analytics</h3>

                <!-- ✅ Summary Boxes -->
                <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                    <div style="flex:1; padding: 15px; background:#f5f5f5; border-radius:8px; text-align:center;">
                        <h4>Total Income</h4>
                        <p style="font-size:20px; font-weight:bold; color:green;">
                            $<?= number_format($totalIncome, 2); ?>
                        </p>
                    </div>
                    <div style="flex:1; padding: 15px; background:#f5f5f5; border-radius:8px; text-align:center;">
                        <h4>Admin Profit (13%)</h4>
                        <p style="font-size:20px; font-weight:bold; color:blue;">
                            $<?= number_format($totalIncome * 0.13, 2); ?>
                        </p>
                    </div>
                </div>

                <!-- Monthly Income -->
                <div class="report-card">
                    <h4>Monthly Income</h4>
                    <canvas id="incomeChart"></canvas>
                </div>

                <!-- ✅ Pie Chart -->
                <div class="report-card">
                    <h4>Income Breakdown</h4>
                    <div style="max-width:300px; margin:auto;">
                        <canvas id="incomePie"></canvas>
                    </div>
                </div>


                <!-- Car Ratings -->
                <div class="report-card">
                    <h4>Cars by Ratings</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>Car</th>
                                <th>Average Rating</th>
                                <th>Total Reviews</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($carRatings as $car): ?>
                                <tr>
                                    <td><?= htmlspecialchars($car['car_name']) ?></td>
                                    <td><?= number_format($car['avg_rating'], 2) ?> ⭐</td>
                                    <td><?= htmlspecialchars($car['review_count']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Driver Trips -->
                <div class="report-card">
                    <h4>Drivers by Trip Count</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>Driver</th>
                                <th>Trip Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($driverTrips as $driver): ?>
                                <tr>
                                    <td><?= htmlspecialchars($driver['driver_name']) ?></td>
                                    <td><?= htmlspecialchars($driver['trip_count']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Most Income Company -->
                <div class="report-card">
                    <h4>Top Earning Company</h4>
                    <?php if ($topCompany): ?>
                        <p><strong><?= htmlspecialchars($topCompany['company_name']); ?></strong></p>
                        <p>Total Income: $<?= number_format($topCompany['total_income'], 2); ?></p>
                    <?php else: ?>
                        <p>No company income recorded yet.</p>
                    <?php endif; ?>
                </div>

                <!-- Most Paid Customers -->
                <div class="report-card">
                    <h4>Top Paying Customers</h4>
                    <?php if ($topCustomers): ?>
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Total Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topCustomers as $cust): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($cust['customer_name']); ?></td>
                                        <td>$<?= number_format($cust['total_spent'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No payments recorded yet.</p>
                    <?php endif; ?>
                </div>
            </section>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                // Line Chart (Monthly Income)
                const incomeLabels = <?= json_encode(array_column($monthlyIncome, 'month')) ?>;
                const incomeData = <?= json_encode(array_column($monthlyIncome, 'total')) ?>;

                const ctx = document.getElementById('incomeChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: incomeLabels,
                        datasets: [{
                            label: 'Monthly Income ($)',
                            data: incomeData,
                            borderColor: 'red',
                            backgroundColor: 'rgba(255,0,0,0.2)',
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'red'
                                }
                            },
                            x: {
                                grid: {
                                    color: 'red'
                                }
                            }
                        }
                    }
                });

                // ✅ Pie Chart (Admin vs Company Income)
                const ctxPie = document.getElementById('incomePie').getContext('2d');
                new Chart(ctxPie, {
                    type: 'pie',
                    data: {
                        labels: ['Admin Profit (13%)', 'Company Income (87%)'],
                        datasets: [{
                            data: [
                                <?= $totalIncome * 0.13 ?>,
                                <?= $totalIncome * 0.87 ?>
                            ],
                            backgroundColor: ['blue', 'green']
                        }]
                    }
                });
            </script>


            <section id="logo" class="section">
                <h3>Manage Logo</h3>
                <div class="logo-preview">
                    <h4>Current Logo</h4>
                    <?php if ($current_logo) : ?>
                        <img src="<?= htmlspecialchars($current_logo['image']) ?>" alt="<?= htmlspecialchars($current_logo['name']) ?>" class="current-logo-img">
                        <p>Name: <?= htmlspecialchars($current_logo['name']) ?></p>
                    <?php else: ?>
                        <p>No logo found. Please upload one below.</p>
                    <?php endif; ?>
                </div>
                <div class="logo-form">
                    <h4>Update Logo</h4>
                    <form method="post" action="miscellaneous_process.php" enctype="multipart/form-data">
                        <div class="input-group">
                            <label for="logo_name">Logo Name:</label>
                            <input type="text" id="logo_name" name="logo_name" value="<?= htmlspecialchars($current_logo['name'] ?? '') ?>" required>
                        </div>
                        <div class="input-group">
                            <label for="logo_image">Logo Image:</label>
                            <input type="file" id="logo_image" name="logo_image">
                        </div>
                        <button type="submit" name="update_logo" class="btn-update">Update Logo</button>
                    </form>
                </div>
            </section>


        </main>
    </div>

    <div id="popup-message" class="popup-message" data-message="<?= htmlspecialchars($errorMessage ?? '') ?>"></div>

    <script src="javascript/admin_dashboard.js"></script>

</body>

</html>