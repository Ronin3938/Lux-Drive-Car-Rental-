<?php
session_start();
require_once 'dbconnect.php';

// Check if a user is logged in
if (!isset($_SESSION['user_id'])) {
    $wishlistCars = [];
    $isLoggedIn = false;
} else {
    $isLoggedIn = true;
    $user_id = $_SESSION['user_id'];

    // Get all cars in the user's wishlist with all necessary details
    $query = "
        SELECT 
            c.id, c.name, c.brand, c.seats, c.price_per_day, c.availability_status,
            GROUP_CONCAT(DISTINCT ci.image) AS images
        FROM wishlists w
        INNER JOIN cars c ON w.car_id = c.id
        LEFT JOIN car_images ci ON c.id = ci.car_id
        WHERE w.user_id = :user_id
        GROUP BY c.id
    ";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $wishlistCars = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Wishlist - Ride With Me</title>
    <link rel="stylesheet" href="css/rentcars.css">
    <link rel="stylesheet" href="css/homepage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS for the wishlist button and pop-up */
        .wishlist-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(69, 57, 57, 0.7);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
            color: #ccc;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10;
        }

        .wishlist-btn:hover {
            background: rgba(68, 60, 60, 0.9);
            color: #e70808;
        }

        .wishlist-btn.wished {
            color: #e70808;
        }

        .wishlist-btn.wished:hover {
            color: #e70808;
        }

        .car-image-collage {
            position: relative;
        }

        .popup-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: none;
            opacity: 0;
            transition: opacity 0.5s, transform 0.5s;
            transform: translateY(-20px);
        }

        .popup-message.show {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>

<body>
    <?php require_once 'header.php'; ?>

    <div id="popup-notification" class="popup-message"></div>

    <div class="luxury-container">
        <h1 id="category-title">My Wishlist</h1>

        <?php if (!$isLoggedIn): ?>
            <p style="text-align: center;">Please <a href="login.php">log in</a> to view your wishlist.</p>
        <?php elseif (empty($wishlistCars)): ?>
            <p style="text-align: center;">Your wishlist is currently empty. Start adding cars from the <a href="rentcars.php">Cars page</a>!</p>
        <?php else: ?>
            <div class="luxury-grid">
                <?php foreach ($wishlistCars as $car): ?>
                    <?php
                    $images = $car['images'] ? explode(',', $car['images']) : ['default.jpg'];
                    $stackImages = array_slice($images, 0, 10);
                    ?>
                    <div class="luxury-card">
                        <div class="car-image-collage">
                            <button class="wishlist-btn wished" data-car-id="<?= htmlspecialchars($car['id']); ?>" title="Remove from Wishlist">
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
                                <strong>Price:</strong> $<?= htmlspecialchars($car['price_per_day']); ?>/day
                            </p>
                            <p>Status: <span class="status <?= $car['availability_status']; ?>">
                                    <?= ucfirst($car['availability_status']); ?>
                                </span></p>
                            <button class="rent-btn" <?= ($car['availability_status'] !== 'available') ? 'disabled' : ''; ?>>
                                <?= ($car['availability_status'] === 'available') ? 'Rent Now' : 'Not Available'; ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        const wishlistButtons = document.querySelectorAll('.wishlist-btn');
        const popup = document.getElementById('popup-notification');

        wishlistButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const carId = btn.getAttribute('data-car-id');
                const action = 'remove';

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
                            // Remove the car card from the page
                            btn.closest('.luxury-card').remove();

                            // Show popup notification
                            popup.textContent = data.message;
                            popup.style.backgroundColor = '#4CAF50';
                            popup.classList.add('show');
                            setTimeout(() => {
                                popup.classList.remove('show');
                            }, 3000);
                        } else {
                            // Show error pop-up
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