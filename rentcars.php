<?php
session_start();
require_once 'dbconnect.php';

// Get user ID from session if logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Get all cars with their categories and images
$query = "
    SELECT c.id, c.name, c.brand, c.seats, c.price_per_day, c.availability_status, c.description,
           GROUP_CONCAT(DISTINCT cat.name) AS categories,
           GROUP_CONCAT(DISTINCT ci.image) AS images
    FROM cars c
    LEFT JOIN car_categories cc ON c.id = cc.car_id
    LEFT JOIN categories cat ON cc.category_id = cat.id
    LEFT JOIN car_images ci ON c.id = ci.car_id
    GROUP BY c.id
";
$stmt = $pdo->prepare($query);
$stmt->execute();
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all categories dynamically
$catStmt = $pdo->query("SELECT name FROM categories ORDER BY id ASC");
$allCategories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

// Get all brands dynamically for the dropdown filter
$brandStmt = $pdo->query("SELECT DISTINCT brand FROM cars ORDER BY brand ASC");
$allBrands = $brandStmt->fetchAll(PDO::FETCH_COLUMN);

// Get all cars in the user's wishlist
$wishlist = [];
if ($user_id) {
    $wishlist_query = "SELECT car_id FROM wishlists WHERE user_id = :user_id";
    $wishlist_stmt = $pdo->prepare($wishlist_query);
    $wishlist_stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $wishlist_stmt->execute();
    $wishlist = $wishlist_stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Rent Cars - Ride With Me</title>
    <link rel="stylesheet" href="css/rentcars.css">
    <link rel="stylesheet" href="css/homepage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body>
    <?php require_once 'header.php'; ?>

    <div id="popup-notification" class="popup-message"></div>

    <div class="luxury-container">
        <h1 id="category-title">All Lux Drive's Cars</h1>

        <!-- New Search and Filter Controls -->
        <div class="filter-controls">
            <input type="text" id="searchInput" placeholder="Search by car name or brand...">
            <div class="price-filters">
                <button class="filter-btn active" data-filter-type="price" data-price-range="all">All Prices</button>
                <button class="filter-btn" data-filter-type="price" data-price-range="0-100">$0 - $100</button>
                <button class="filter-btn" data-filter-type="price" data-price-range="100-200">$100 - $200</button>
                <button class="filter-btn" data-filter-type="price" data-price-range="200-300">$200 - $300</button>
                <button class="filter-btn" data-filter-type="price" data-price-range="300-max">$300+</button>
            </div>
            <div class="brand-filter">
                <select id="brandDropdown" class="filter-btn">
                    <option value="all">All Brands</option>
                    <?php foreach ($allBrands as $brandName): ?>
                        <option value="<?= htmlspecialchars($brandName) ?>"><?= htmlspecialchars($brandName) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <!-- End of New Controls -->

        <div class="category-buttons">
            <button class="cat-btn active" data-category="All">All</button>
            <?php foreach ($allCategories as $catName): ?>
                <button class="cat-btn" data-category="<?= htmlspecialchars($catName) ?>">
                    <?= htmlspecialchars($catName) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="luxury-grid">
            <?php if (!empty($cars)): ?>
                <?php foreach ($cars as $car): ?>
                    <?php
                    $images = $car['images'] ? explode(',', $car['images']) : ['default.jpg'];
                    $carCategories = explode(',', $car['categories']);
                    $maxStack = 5;
                    $stackImages = array_slice($images, 0, $maxStack);
                    $isWished = in_array($car['id'], $wishlist);
                    ?>
                    <div class="luxury-card" data-car-id="<?= htmlspecialchars($car['id']); ?>" data-car-name="<?= htmlspecialchars($car['name']); ?>" data-car-brand="<?= htmlspecialchars($car['brand']); ?>" data-price="<?= htmlspecialchars($car['price_per_day']); ?>" data-categories="<?= htmlspecialchars(implode(',', $carCategories)) ?>">

                        <div class="car-image-collage">
                            <button class="wishlist-btn <?= $isWished ? 'wished' : ''; ?>" data-car-id="<?= htmlspecialchars($car['id']); ?>" title="Add to Wishlist">
                                <i class="fa-solid fa-heart"></i>
                            </button>
                            <div class="main-image">
                                <img src="uploads/car_images/<?= htmlspecialchars($stackImages[4]); ?>" alt="Main">
                            </div>
                            <div class="side-images">
                                <?php if (isset($stackImages[1])): ?>
                                    <img src="uploads/car_images/<?= htmlspecialchars($stackImages[0]); ?>" alt="Side 1">
                                <?php endif; ?>
                                <?php if (isset($stackImages[2])): ?>
                                    <img src="uploads/car_images/<?= htmlspecialchars($stackImages[1]); ?>" alt="Side 2">
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="luxury-card-content">
                            <h3 class="car-name"><?= htmlspecialchars($car['name']); ?></h3>
                            <h4 class="car-brand"><?= htmlspecialchars($car['brand']); ?></h4>

                            <p><strong>Seats:</strong> <?= htmlspecialchars($car['seats']); ?> |
                                <strong>Price:</strong> $<span class="car-price"><?= htmlspecialchars($car['price_per_day']); ?></span>/day
                            </p>
                            <p>Status: <span class="status <?= $car['availability_status']; ?>">
                                    <?= ucfirst($car['availability_status']); ?>
                                </span></p>
                            <a href="car_details.php?id=<?= htmlspecialchars($car['id']); ?>" class="rent-btn"
                                <?= ($car['availability_status'] !== 'available') ? 'disabled' : ''; ?>
                                onclick="handleRentClick(event, <?= json_encode((bool)$user_id); ?>)">
                                <?= ($car['availability_status'] === 'available') ? 'Rent Now' : 'Not Available'; ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No cars available at the moment.</p>
            <?php endif; ?>
        </div>

    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const priceButtons = document.querySelectorAll('.price-filters button');
        const brandDropdown = document.getElementById('brandDropdown');
        const categoryButtons = document.querySelectorAll('.cat-btn');
        const cars = document.querySelectorAll('.luxury-card');
        const title = document.getElementById('category-title');
        const wishlistButtons = document.querySelectorAll('.wishlist-btn');
        const popup = document.getElementById('popup-notification');

        // Function to handle the "Rent Now" button click
        function handleRentClick(event, isLoggedIn) {
            if (!isLoggedIn) {
                event.preventDefault(); // Stop the link from navigating
                popup.textContent = 'Please log in or sign up to rent a car.';
                popup.style.backgroundColor = '#FF5733';
                popup.classList.add('show');
                setTimeout(() => {
                    popup.classList.remove('show');
                }, 3000);
            }
        }

        // Main function to filter cars based on all criteria
        const filterCars = () => {
            const searchQuery = searchInput.value.toLowerCase();
            const selectedPriceRange = document.querySelector('.price-filters button.active').dataset.priceRange;
            const selectedBrand = brandDropdown.value.toLowerCase();
            const selectedCategory = document.querySelector('.cat-btn.active').dataset.category.toLowerCase();

            cars.forEach(car => {
                const carName = car.dataset.carName.toLowerCase();
                const carBrand = car.dataset.carBrand.toLowerCase();
                const carPrice = parseFloat(car.dataset.price);
                const carCategories = car.dataset.categories.toLowerCase().split(',');

                // Check search query
                const matchesSearch = carName.includes(searchQuery) || carBrand.includes(searchQuery);

                // Check price range
                let matchesPrice = false;
                if (selectedPriceRange === 'all') {
                    matchesPrice = true;
                } else if (selectedPriceRange === '0-100') {
                    matchesPrice = carPrice >= 0 && carPrice <= 100;
                } else if (selectedPriceRange === '100-200') {
                    matchesPrice = carPrice > 100 && carPrice <= 200;
                } else if (selectedPriceRange === '200-300') {
                    matchesPrice = carPrice > 200 && carPrice <= 300;
                } else if (selectedPriceRange === '300-max') {
                    matchesPrice = carPrice > 300;
                }

                // Check brand
                const matchesBrand = selectedBrand === 'all' || carBrand === selectedBrand;

                // Check category
                const matchesCategory = selectedCategory === 'all' || carCategories.includes(selectedCategory);

                if (matchesSearch && matchesPrice && matchesBrand && matchesCategory) {
                    car.style.display = 'block';
                } else {
                    car.style.display = 'none';
                }
            });
        };

        // Event listeners for new filters
        searchInput.addEventListener('input', filterCars);

        priceButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                priceButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                filterCars();
            });
        });

        brandDropdown.addEventListener('change', filterCars);


        // Event listeners for existing category buttons (updated to use the main filter function)
        categoryButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const selected = btn.getAttribute('data-category');

                // Update active class
                categoryButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                // Update title
                title.textContent = selected === "All" ? "All Cars" : selected + " Cars";

                // Run the main filter function
                filterCars();
            });
        });

        // Add event listener for wishlist buttons
        wishlistButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const carId = btn.getAttribute('data-car-id');
                const isWished = btn.classList.contains('wished');
                const action = isWished ? 'remove' : 'add';

                fetch('add_to_wishlist.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            car_id: carId,
                            action: action
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Toggle the 'wished' class on the button
                            btn.classList.toggle('wished');

                            // Show popup notification
                            popup.textContent = data.message;
                            popup.style.backgroundColor = '#4CAF50';
                            popup.classList.add('show');
                            setTimeout(() => {
                                popup.classList.remove('show');
                            }, 3000);

                        } else if (data.status === 'not_logged_in') {
                            popup.textContent = data.message;
                            popup.style.backgroundColor = '#FF5733';
                            popup.classList.add('show');
                            setTimeout(() => {
                                popup.classList.remove('show');
                            }, 3000);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        popup.textContent = 'An error occurred. Please try again.';
                        popup.style.backgroundColor = '#FF5733';
                        popup.classList.add('show');
                        setTimeout(() => {
                            popup.classList.remove('show');
                        }, 3000);
                    });
            });
        });
    </script>

    <?php require_once 'footer.php'; ?>
</body>

</html>