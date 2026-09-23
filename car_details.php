<?php
session_start();
require_once 'dbconnect.php'; // Ensure your dbconnect.php file uses PDO

// Generate a CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Validate car ID from the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: rentcars.php');
    exit;
}

$car_id = $_GET['id'];
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$isLoggedIn = $user_id !== null;

// Query to get main car details
$carDetailsQuery = "SELECT * FROM cars WHERE id = :car_id";
$stmtCar = $pdo->prepare($carDetailsQuery);
$stmtCar->bindValue(':car_id', $car_id, PDO::PARAM_INT);
$stmtCar->execute();
$car = $stmtCar->fetch(PDO::FETCH_ASSOC);

// If car is not found, exit
if (!$car) {
    echo "<h1>Car not found.</h1>";
    exit;
}

// Query to get car images
$imagesQuery = "SELECT image FROM car_images WHERE car_id = :car_id";
$stmtImages = $pdo->prepare($imagesQuery);
$stmtImages->bindValue(':car_id', $car_id, PDO::PARAM_INT);
$stmtImages->execute();
$images = $stmtImages->fetchAll(PDO::FETCH_COLUMN);

// Query to get car features
$featuresQuery = "SELECT feature_name, feature_value FROM car_features WHERE car_id = :car_id";
$stmtFeatures = $pdo->prepare($featuresQuery);
$stmtFeatures->bindValue(':car_id', $car_id, PDO::PARAM_INT);
$stmtFeatures->execute();
$features = $stmtFeatures->fetchAll(PDO::FETCH_ASSOC);

// Query to get driver information
$driverQuery = "SELECT name, status FROM drivers WHERE car_id = :car_id";
$stmtDriver = $pdo->prepare($driverQuery);
$stmtDriver->bindValue(':car_id', $car_id, PDO::PARAM_INT);
$stmtDriver->execute();
$driver = $stmtDriver->fetch(PDO::FETCH_ASSOC);

// Query to check if this is the user's first booking
$userBookingsCount = 0;
if ($isLoggedIn) {
    $userBookingsQuery = "SELECT COUNT(*) FROM bookings WHERE user_id = :user_id AND status = 'completed'";
    $stmtUserBookings = $pdo->prepare($userBookingsQuery);
    $stmtUserBookings->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmtUserBookings->execute();
    $userBookingsCount = $stmtUserBookings->fetchColumn();
}
$isFirstTimeUser = $userBookingsCount == 0;

// Query to get the user's available coupons
$coupons = [];
if ($isLoggedIn) {
    $couponsQuery = "SELECT id, coupon_value FROM user_coupons WHERE user_id = :user_id AND is_used = 0 ORDER BY created_at DESC";
    $stmtCoupons = $pdo->prepare($couponsQuery);
    $stmtCoupons->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmtCoupons->execute();
    $coupons = $stmtCoupons->fetchAll(PDO::FETCH_ASSOC);
}

// Query to get customer reviews
$reviewQuery = "
    SELECT cr.*, u.name AS user_name
    FROM car_reviews cr
    JOIN users u ON cr.user_id = u.id
    WHERE cr.car_id = :car_id
    ORDER BY cr.created_at DESC
";
$reviewStmt = $pdo->prepare($reviewQuery);
$reviewStmt->bindValue(':car_id', $car_id, PDO::PARAM_INT);
$reviewStmt->execute();
$reviews = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($car['brand'] . ' ' . $car['name']); ?> - Details</title>
    <link rel="stylesheet" href="css/car_details.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>

    </style>
</head>

<body>
    <?php require_once 'header.php'; ?>
    <div id="popup-notification" class="popup-message"></div>
    <div class="car-detail-container">
        <div class="car-header">
            <h1><?= htmlspecialchars($car['brand'] . ' ' . $car['name']); ?></h1>
            <h2>$<?= htmlspecialchars($car['price_per_day']); ?> per day</h2>
        </div>

        <?php $images = !empty($images) ? $images : ['default.jpg']; ?>
        <div class="slideshow-container">
            <?php foreach ($images as $index => $img) : ?>
                <img src="uploads/car_images/<?= htmlspecialchars($img); ?>" class="mySlides" alt="<?= htmlspecialchars($car['name']); ?>">
            <?php endforeach; ?>
            <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
            <a class="next" onclick="plusSlides(1)">&#10095;</a>
        </div>

        <div class="car-details-grid">
            <div class="car-info-card">
                <h3>Car Information</h3>
                <div class="description-box">
                    <?= nl2br(htmlspecialchars($car['description'])); ?>
                </div>
                <div class="feature-list">
                    <div class="feature-item">
                        <i class="fas fa-chair"></i>
                        <p>Seats: <?= htmlspecialchars($car['seats']); ?></p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <p>Status: <span class="status <?= htmlspecialchars($car['availability_status']); ?>"><?= ucfirst(htmlspecialchars($car['availability_status'])); ?></span></p>
                    </div>
                    <?php if ($driver) : ?>
                        <div class="feature-item">
                            <i class="fas fa-user-tie"></i>
                            <p>Driver: <?= htmlspecialchars($driver['name']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="car-features-section">
                    <h3>Car Features</h3>
                    <div class="feature-list">
                        <?php foreach ($features as $feature) : ?>
                            <div class="feature-item">
                                <i class="fas fa-info-circle"></i>
                                <p><?= htmlspecialchars($feature['feature_name']) ?>:
                                    <?= htmlspecialchars($feature['feature_value']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
            <div class="booking-section">
                <div class="booking-form">
                    <h3>Book This Car</h3>
                    <form id="bookingForm" onsubmit="return false;">
                        <input type="hidden" name="car_id" value="<?= htmlspecialchars($car_id); ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token); ?>">
                        <div class="date-picker-group">
                            <div class="input-group">
                                <label for="startDate">Start Date</label>
                                <input type="date" id="startDate" name="startDate" class="date-input" required>
                            </div>
                            <div class="input-group">
                                <label for="endDate">End Date</label>
                                <input type="date" id="endDate" name="endDate" class="date-input" required>
                            </div>
                        </div>
                        <div class="location-picker-group">
                            <div class="input-group">
                                <label for="pickupAddress">Pickup Address</label>
                                <input type="text" id="pickupAddress" placeholder="Enter full address or select on map" class="location-input" required>
                            </div>
                            <div class="input-group">
                                <label>Set Pickup Location (click on map)</label>
                                <div id="pickupMap"></div>
                                <input type="hidden" id="pickupLat" name="pickupLat" required>
                                <input type="hidden" id="pickupLon" name="pickupLon" required>
                            </div>
                        </div>
                        <div class="location-picker-group">
                            <div class="input-group">
                                <label for="dropoffSelect">Drop-off Location</label>
                                <select id="dropoffSelect" name="dropoffSelect">
                                    <option value="">Select a popular place</option>
                                </select>
                            </div>
                            <div class="input-group">
                                <label>Or set Drop-off Location (click on map)</label>
                                <div id="dropoffMap"></div>
                                <input type="hidden" id="dropoffLat" name="dropoffLat">
                                <input type="hidden" id="dropoffLon" name="dropoffLon">
                            </div>
                        </div>
                        <?php if ($isLoggedIn && $isFirstTimeUser) : ?>
                            <div class="discount-info">
                                <input type="checkbox" id="discountCheckbox" name="apply_discount">
                                <label for="discountCheckbox">Get 10% First-Time User Discount</label>
                            </div>
                        <?php endif; ?>
                        <div class="price-card">
                            <h4>Total Price</h4>
                            <span class="total-price">$<span id="totalPrice">0.00</span></span>
                        </div>
                        <?php if ($isLoggedIn && !empty($coupons)) : ?>
                            <div class="coupon-section">
                                <label for="couponSelect">Apply Coupon:</label>
                                <select id="couponSelect" name="coupon_id" class="coupon-select">
                                    <option value="">Select a coupon</option>
                                    <?php foreach ($coupons as $coupon) : ?>
                                        <option value="<?= htmlspecialchars($coupon['id']); ?>"> $<?= htmlspecialchars($coupon['coupon_value']); ?> off</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                        <div class="btn-group">
                            <button class="rent-btn" id="bookNowBtn" type="submit" <?= ($car['availability_status'] !== 'available') ? 'disabled' : ''; ?>>
                                <?= ($car['availability_status'] === 'available') ? 'Book Now' : 'Not Available'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="reviews">
            <h3>Customer Reviews</h3>

            <?php if ($isLoggedIn): ?>
                <div class="review-form">
                    <h4>Write a Review</h4>
                    <form id="reviewForm" onsubmit="return false;">
                        <input type="hidden" name="car_id" value="<?= htmlspecialchars($car_id); ?>">
                        <div class="rating-stars">
                            <label>Rating:</label>
                            <div class="stars">
                                <i class="fas fa-star" data-value="1"></i>
                                <i class="fas fa-star" data-value="2"></i>
                                <i class="fas fa-star" data-value="3"></i>
                                <i class="fas fa-star" data-value="4"></i>
                                <i class="fas fa-star" data-value="5"></i>
                            </div>
                            <input type="hidden" id="rating" name="rating" required>
                        </div>

                        <textarea id="reviewText" placeholder="Write your review..." required></textarea>
                        <button type="submit" class="review-btn">Submit Review</button>
                    </form>
                </div>
            <?php else: ?>
                <p><a href="login.php">Log in</a> to write a review.</p>
            <?php endif; ?>

            <h4>Recent Reviews</h4>
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <h4><?= htmlspecialchars($review['user_name']); ?></h4>
                        <p><?= htmlspecialchars($review['review_text']); ?></p>
                        <small>Rating: <?= str_repeat("⭐", $review['rating']); ?> (<?= $review['rating']; ?>/5)</small><br>
                        <small><?= htmlspecialchars($review['created_at']); ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No reviews yet. Be the first to leave one!</p>
            <?php endif; ?>
        </div>
    </div>
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <p id="modal-message"></p>
        </div>
    </div>
    <div id="paymentModal" class="modal payment-modal">
        <div class="modal-content payment-content">
            <span class="close-btn">&times;</span>
            <h4>Select a Payment Method</h4>
            <div class="payment-options">
                <button class="payment-btn" data-method="credit_card">
                    <i class="fas fa-credit-card"></i><br>Credit Card
                </button>
                <button class="payment-btn" data-method="paypal">
                    <i class="fab fa-paypal"></i><br>PayPal
                </button>
                <button class="payment-btn" data-method="bank_transfer">
                    <i class="fas fa-university"></i><br>Bank Transfer
                </button>
                <button class="payment-btn" data-method="e_wallet_1">
                    <i class="fas fa-wallet"></i><br>E-Wallet 1
                </button>
                <button class="payment-btn" data-method="cash">
                    <i class="fas fa-money-bill-wave"></i><br>Pay Cash
                </button>
            </div>
        </div>
    </div>

    <div id="confirmationModal" class="modal confirmation-modal">
        <div class="modal-content confirmation-content">
            <h4>Confirm Cash Payment</h4>
            <p>You have selected to pay with cash.</p>
            <p>Your total price is: $<span id="confirmTotalPrice">0.00</span></p>
            <p>Please confirm your booking.</p>
            <div class="confirmation-actions">
                <button id="confirmBtn" class="rent-btn">Confirm</button>
                <button id="cancelBtn" class="rent-btn" style="background-color: #f44336;">Cancel</button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        const carId = <?= json_encode($car_id); ?>;
        const pricePerDay = <?= json_encode($car['price_per_day']); ?>;
        const isFirstTimeUser = <?= json_encode($isFirstTimeUser); ?>;
        const isLoggedIn = <?= json_encode($isLoggedIn); ?>;
        const csrfToken = <?= json_encode($csrf_token); ?>;

        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');
        const pickupAddressInput = document.getElementById('pickupAddress');
        const dropoffSelect = document.getElementById('dropoffSelect');
        const discountCheckbox = document.getElementById('discountCheckbox');
        const totalPriceSpan = document.getElementById('totalPrice');
        const bookNowBtn = document.querySelector('.rent-btn');
        const bookingForm = document.getElementById('bookingForm');

        const modal = document.getElementById('modal');
        const modalMessage = document.getElementById('modal-message');
        const closeModalBtn = document.querySelector('.close-btn');

        const paymentModal = document.getElementById('paymentModal');
        const confirmationModal = document.getElementById('confirmationModal');
        const confirmTotalPriceSpan = document.getElementById('confirmTotalPrice');
        const confirmBtn = document.getElementById('confirmBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const paymentButtons = document.querySelectorAll('.payment-btn');

        let slideIndex = 0;
        let slideInterval;
        let pickupLat = null;
        let pickupLon = null;
        let dropoffLat = null;
        let dropoffLon = null;
        let pickupMarker;
        let dropoffMarker;

        const showSlides = () => {
            const slides = document.getElementsByClassName("mySlides");
            for (let i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
            }
            slideIndex++;
            if (slideIndex > slides.length) {
                slideIndex = 1;
            }
            slides[slideIndex - 1].style.display = "block";
        };

        const plusSlides = (n) => {
            clearInterval(slideInterval);
            const slides = document.getElementsByClassName("mySlides");
            const currentSlide = slideIndex - 1;
            slides[currentSlide].style.display = "none";
            slideIndex += n;
            if (slideIndex > slides.length) {
                slideIndex = 1;
            }
            if (slideIndex < 1) {
                slideIndex = slides.length;
            }
            slides[slideIndex - 1].style.display = "block";
            startAutoSlide();
        };

        const startAutoSlide = () => {
            slideInterval = setInterval(showSlides, 2000);
        };

        const showModal = (message, autoHide = true) => {
            modalMessage.textContent = message;
            modal.style.display = 'block';
            if (autoHide) {
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 2000);
            }
        };

        closeModalBtn.onclick = () => {
            modal.style.display = 'none';
        };

        window.onclick = (event) => {
            if (event.target === modal || event.target === paymentModal || event.target === confirmationModal) {
                modal.style.display = 'none';
                paymentModal.style.display = 'none';
                confirmationModal.style.display = 'none';
            }
        };

        const calculatePrice = () => {
            const start = new Date(startDateInput.value);
            const end = new Date(endDateInput.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (start && end && start >= today && start < end) {
                const timeDiff = end.getTime() - start.getTime();
                const dayDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
                let total = dayDiff * pricePerDay;

                // Apply first-time discount
                if (isFirstTimeUser && discountCheckbox && discountCheckbox.checked) {
                    total *= 0.90;
                }

                // ✅ Apply coupon discount
                const couponSelect = document.getElementById('couponSelect');
                if (couponSelect && couponSelect.value) {
                    const selectedOption = couponSelect.options[couponSelect.selectedIndex].textContent;
                    const match = selectedOption.match(/\$([\d.]+)/);
                    if (match) {
                        const couponValue = parseFloat(match[1]);
                        total = Math.max(0, total - couponValue);
                    }
                }

                totalPriceSpan.textContent = total.toFixed(2);
                confirmTotalPriceSpan.textContent = total.toFixed(2); // update confirmation modal too
            } else {
                totalPriceSpan.textContent = "0.00";
                confirmTotalPriceSpan.textContent = "0.00";
            }
        };
        const couponSelect = document.getElementById('couponSelect');
        if (couponSelect) {
            couponSelect.addEventListener('change', calculatePrice);
        }


        const setMinDate = () => {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            startDateInput.min = `${yyyy}-${mm}-${dd}`;
        };

        startDateInput.addEventListener('change', () => {
            endDateInput.min = startDateInput.value;
            calculatePrice();
        });
        endDateInput.addEventListener('change', calculatePrice);
        if (discountCheckbox) {
            discountCheckbox.addEventListener('change', calculatePrice);
        }

        const pickupMap = L.map('pickupMap').setView([16.8, 96.15], 13);
        const dropoffMap = L.map('dropoffMap').setView([16.8, 96.15], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(pickupMap);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(dropoffMap);

        const geocodeAddress = async (address, map, markerVar) => {
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(address)}&format=json`);
                const data = await response.json();
                if (data && data.length > 0) {
                    const lat = parseFloat(data[0].lat);
                    const lon = parseFloat(data[0].lon);
                    map.setView([lat, lon], 13);
                    if (markerVar === 'pickup') {
                        if (pickupMarker) map.removeLayer(pickupMarker);
                        pickupMarker = L.marker([lat, lon]).addTo(map);
                        document.getElementById('pickupLat').value = lat;
                        document.getElementById('pickupLon').value = lon;
                        showModal('Pickup location updated from address.', true);
                    } else if (markerVar === 'dropoff') {
                        if (dropoffMarker) map.removeLayer(dropoffMarker);
                        dropoffMarker = L.marker([lat, lon]).addTo(map);
                        document.getElementById('dropoffLat').value = lat;
                        document.getElementById('dropoffLon').value = lon;
                        showModal('Drop-off location updated from address.', true);
                    }
                } else {
                    showModal('Address not found. Please click on the map to set the location.', false);
                }
            } catch (error) {
                console.error('Geocoding error:', error);
                showModal('Could not find the address. Please use the map.', false);
            }
        };

        pickupMap.on('click', (e) => {
            if (pickupMarker) {
                pickupMap.removeLayer(pickupMarker);
            }
            pickupLat = e.latlng.lat;
            pickupLon = e.latlng.lng;
            pickupMarker = L.marker([pickupLat, pickupLon]).addTo(pickupMap);
            document.getElementById('pickupLat').value = pickupLat;
            document.getElementById('pickupLon').value = pickupLon;
            showModal(`Pickup location set: Lat ${pickupLat.toFixed(4)}, Lon ${pickupLon.toFixed(4)}`);
        });

        dropoffMap.on('click', (e) => {
            if (dropoffMarker) {
                dropoffMap.removeLayer(dropoffMarker);
            }
            dropoffLat = e.latlng.lat;
            dropoffLon = e.latlng.lng;
            dropoffMarker = L.marker([dropoffLat, dropoffLon]).addTo(dropoffMap);
            document.getElementById('dropoffLat').value = dropoffLat;
            document.getElementById('dropoffLon').value = dropoffLon;
            dropoffSelect.selectedIndex = 0;
            showModal(`Drop-off location set: Lat ${dropoffLat.toFixed(4)}, Lon ${dropoffLon.toFixed(4)}`);
        });

        pickupAddressInput.addEventListener('blur', () => {
            if (pickupAddressInput.value) {
                geocodeAddress(pickupAddressInput.value, pickupMap, 'pickup');
            }
        });

        const fetchPlaces = async () => {
            try {
                const response = await fetch('get_places.php');
                const places = await response.json();
                places.forEach(place => {
                    const option = document.createElement('option');
                    option.value = JSON.stringify({
                        name: place.name,
                        lat: place.latitude,
                        lon: place.longitude
                    });
                    option.textContent = place.name;
                    dropoffSelect.appendChild(option);
                });
            } catch (error) {
                console.error('Failed to fetch places:', error);
                showModal("Failed to load popular places.", false);
            }
        };

        dropoffSelect.addEventListener('change', () => {
            const selectedOption = dropoffSelect.value;
            if (selectedOption) {
                const place = JSON.parse(selectedOption);
                dropoffLat = place.lat;
                dropoffLon = place.lon;
                document.getElementById('dropoffLat').value = dropoffLat;
                document.getElementById('dropoffLon').value = dropoffLon;
                if (dropoffMarker) {
                    dropoffMap.removeLayer(dropoffMarker);
                }
                dropoffMarker = L.marker([dropoffLat, dropoffLon]).addTo(dropoffMap);
                dropoffMap.setView([dropoffLat, dropoffLon], 13);
                showModal(`Drop-off location set to ${place.name}`);
            } else {
                dropoffLat = null;
                dropoffLon = null;
                document.getElementById('dropoffLat').value = '';
                document.getElementById('dropoffLon').value = '';
                if (dropoffMarker) {
                    dropoffMap.removeLayer(dropoffMarker);
                }
            }
        });

        const validateForm = () => {
            if (!startDateInput.value || !endDateInput.value) {
                showModal("Please select both a start and end date.", false);
                return false;
            }
            if (new Date(startDateInput.value) >= new Date(endDateInput.value)) {
                showModal("The end date must be after the start date.", false);
                return false;
            }
            if (!document.getElementById('pickupLat').value || !document.getElementById('pickupLon').value) {
                showModal("Please enter a pickup address and/or click on the map to set the pickup location.", false);
                return false;
            }
            if (!document.getElementById('dropoffLat').value || !document.getElementById('dropoffLon').value) {
                showModal("Please select a drop-off location from the list or on the map.", false);
                return false;
            }
            return true;
        };

        // New booking process event listener
        bookingForm.addEventListener('submit', (e) => {
            e.preventDefault();
            if (!validateForm()) {
                return;
            }
            if (!isLoggedIn) {
                showModal("Please log in to book a car.", false);
                return;
            }
            // Open the payment modal
            paymentModal.style.display = 'flex';
        });

        // Event listener for payment method buttons
        paymentButtons.forEach(button => {
            button.addEventListener('click', () => {
                const paymentMethod = button.getAttribute('data-method');
                // Show confirmation modal for all payment methods
                paymentModal.style.display = 'none';
                confirmTotalPriceSpan.textContent = totalPriceSpan.textContent;
                confirmationModal.style.display = 'flex';
            });
        });

        // Event listener for cash confirmation buttons
        confirmBtn.addEventListener('click', () => {
            confirmationModal.style.display = 'none';
            bookCar('cash', parseFloat(totalPriceSpan.textContent));
        });

        cancelBtn.addEventListener('click', () => {
            confirmationModal.style.display = 'none';
            showModal('Booking cancelled.');
        });

        const stars = document.querySelectorAll('.stars i');
        const ratingInput = document.getElementById('rating');

        stars.forEach(star => {
            star.addEventListener('mouseover', () => {
                const val = parseInt(star.getAttribute('data-value'));
                highlightStars(val);
            });

            star.addEventListener('mouseout', () => {
                highlightStars(ratingInput.value);
            });

            star.addEventListener('click', () => {
                const val = parseInt(star.getAttribute('data-value'));
                ratingInput.value = val; // set hidden input value
                highlightStars(val);
            });
        });

        function highlightStars(val) {
            stars.forEach(star => {
                const starVal = parseInt(star.getAttribute('data-value'));
                if (starVal <= val) {
                    star.classList.add('selected');
                } else {
                    star.classList.remove('selected');
                }
            });
        }

        // Review form submission
        const reviewForm = document.getElementById('reviewForm');
        if (reviewForm) {
            reviewForm.addEventListener('submit', (e) => {
                e.preventDefault(); // prevent default form submit
                const rating = document.getElementById('rating').value;
                const reviewText = document.getElementById('reviewText').value.trim();

                if (!rating || !reviewText) {
                    showModal("Please provide a rating and review.", false);
                    return;
                }

                fetch('add_review.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            car_id: carId,
                            rating: rating,
                            review_text: reviewText
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showModal("✅ Review submitted!", true);
                            setTimeout(() => location.reload(), 1500); // reload to show new review
                        } else {
                            showModal("❌ " + data.message, false);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        showModal("❌ Failed to submit review.", false);
                    });
            });
        }




        // Main function to send booking data to the server
        // Main function to send booking data to the server
        const bookCar = (paymentMethod, amount) => {
            const data = {
                car_id: carId,
                start_date: startDateInput.value,
                end_date: endDateInput.value,
                pickup_address: pickupAddressInput.value,
                pickup_lat: document.getElementById('pickupLat').value,
                pickup_lon: document.getElementById('pickupLon').value,
                dropoff_lat: document.getElementById('dropoffLat').value,
                dropoff_lon: document.getElementById('dropoffLon').value,
                total_price: amount,
                apply_discount: (discountCheckbox && discountCheckbox.checked) ? 1 : 0,
                coupon_id: document.getElementById('couponSelect') ? document.getElementById('couponSelect').value : '',
                payment_method: paymentMethod,
                csrf_token: csrfToken
            };

            fetch('book_car.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(res => res.text()) // 👈 read raw
                .then(raw => {
                    console.log("RAW booking response:", raw); // Debug log
                    let json;
                    try {
                        json = JSON.parse(raw);
                    } catch (err) {
                        throw new Error("Server did not return valid JSON. Raw response logged.");
                    }
                    return json;
                })
                .then(response => {
                    console.log("Parsed booking JSON:", response);
                    if (response.success) {
                        showModal("Booking successful! Redirecting...", false);
                        setTimeout(() => window.location.href = 'booking_history.php', 2000);
                    } else {
                        showModal("Booking failed: " + response.message, false);
                    }
                })
                .catch(error => {
                    console.error("Booking fetch error:", error);
                    showModal("❌ JS error: " + error.message, false);
                });
        };


        startAutoSlide();
        setMinDate();
        fetchPlaces();
    </script>
    <?php require_once 'footer.php'; ?>
</body>

</html>