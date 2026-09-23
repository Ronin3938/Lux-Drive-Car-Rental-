<?php
require_once 'dbconnect.php';
session_start();

// Verify user ID and role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$company_name = '';
$cars = [];
$bookings = [];
$payments = [];
$reviews = [];
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'cars';

try {
    // Get company information based on user ID
    $stmt = $pdo->prepare("SELECT id, company_name FROM companies WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($company) {
        $company_id = $company['id'];
        $company_name = $company['company_name'];

        if ($active_tab === 'cars') {
            // Get cars with driver name
            $stmt = $pdo->prepare("
                SELECT 
                    c.id, 
                    c.name, 
                    c.brand, 
                    c.seats, 
                    c.price_per_day, 
                    c.availability_status, 
                    c.image, 
                    c.fuel_type,
                    IFNULL(d.name, 'N/A') AS driver_name,
                    IFNULL(ROUND(AVG(r.rating) / 5 * 100, 2), 0) AS rating_percent
                FROM cars AS c
                LEFT JOIN car_reviews AS r ON r.car_id = c.id
                LEFT JOIN drivers AS d ON d.car_id = c.id
                WHERE c.company_id = ?
                GROUP BY c.id
            ");
            $stmt->execute([$company_id]);
            $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($active_tab === 'bookings') {
            // Get bookings for the company's cars
            $stmt = $pdo->prepare("
                SELECT 
                    b.id AS booking_id, 
                    u.name AS customer_name, 
                    c.name AS car_name, 
                    c.brand AS car_brand,
                    b.start_date, 
                    b.end_date, 
                    b.total_price, 
                    b.status AS booking_status
                FROM bookings AS b
                JOIN cars AS c ON b.car_id = c.id
                JOIN users AS u ON b.user_id = u.id
                WHERE c.company_id = ?
                ORDER BY b.created_at DESC
            ");
            $stmt->execute([$company_id]);
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($active_tab === 'payments') {
            // Get payments for the company's cars
            $stmt = $pdo->prepare("
                SELECT
                    p.id AS payment_id,
                    p.status AS payment_status,
                    p.amount,
                    b.id AS booking_id,
                    b.total_price,
                    u.name AS customer_name,
                    c.name AS car_name,
                    c.brand AS car_brand
                FROM payments AS p
                JOIN bookings AS b ON p.booking_id = b.id
                JOIN cars AS c ON b.car_id = c.id
                JOIN users AS u ON b.user_id = u.id
                WHERE c.company_id = ?
                ORDER BY b.created_at DESC
            ");
            $stmt->execute([$company_id]);
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($active_tab === 'reviews') {
            // Get reviews for the company's cars
            $stmt = $pdo->prepare("
                SELECT 
                    r.id AS review_id,
                    r.car_id,
                    c.name AS car_name,
                    c.brand AS car_brand,
                    u.name AS customer_name,
                    r.rating,
                    r.review_text,
                    r.created_at
                FROM car_reviews AS r
                JOIN cars AS c ON r.car_id = c.id
                JOIN users AS u ON r.user_id = u.id
                WHERE c.company_id = ?
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$company_id]);
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Company Dashboard - Lux Drive</title>
    <link rel="stylesheet" href="css/companies_dashboard.css">
</head>

<body>
    <div class="dashboard-container">
        <div class="header">
            <h1><?php echo htmlspecialchars($company_name); ?> Dashboard</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <div id="messageBox" class="message-box"></div>

        <div class="tab-bar">
            <a href="?tab=cars" class="<?php echo $active_tab === 'cars' ? 'active' : ''; ?>">Cars</a>
            <a href="?tab=bookings" class="<?php echo $active_tab === 'bookings' ? 'active' : ''; ?>">Bookings</a>
            <a href="?tab=payments" class="<?php echo $active_tab === 'payments' ? 'active' : ''; ?>">Payments</a>
            <a href="?tab=reviews" class="<?php echo $active_tab === 'reviews' ? 'active' : ''; ?>">Reviews</a>
        </div>

        <?php if ($active_tab === 'cars'): ?>
            <h2>Your Fleet</h2>
            <input type="text" id="carSearch" placeholder="Search cars..." onkeyup="filterCars()" style="margin-bottom:10px; padding:5px;">
            <?php if (!empty($cars)): ?>
                <table id="carsTable">
                    <thead>
                        <tr>
                            <th>Car</th>
                            <th>Seats</th>
                            <th>Price per Day</th>
                            <th>Fuel Type</th>
                            <th>Status</th>
                            <th>Driver</th>
                            <th>Rating %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cars as $car): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($car['brand'] . ' ' . $car['name']); ?></td>
                                <td><?php echo htmlspecialchars($car['seats']); ?></td>
                                <td>$<?php echo htmlspecialchars($car['price_per_day']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($car['fuel_type'])); ?></td>
                                <td>
                                    <select class="status-dropdown" onchange="updateCarStatus(this.value, <?php echo $car['id']; ?>)">
                                        <option value="available" <?php echo ($car['availability_status'] === 'available') ? 'selected' : ''; ?>>Available</option>
                                        <option value="booked" <?php echo ($car['availability_status'] === 'booked') ? 'selected' : ''; ?>>Booked</option>
                                        <option value="maintenance" <?php echo ($car['availability_status'] === 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                                    </select>
                                </td>
                                <td><?php echo htmlspecialchars($car['driver_name'] ?? 'N/A'); ?></td>
                                <td><?php echo $car['rating_percent'] . '%'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-content">No cars registered for this company.</p>
            <?php endif; ?>


        <?php elseif ($active_tab === 'bookings'): ?>
            <h2>Bookings Overview</h2>
            <select id="bookingFilter" onchange="filterBookings()" style="margin-bottom:10px; padding:5px;">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="cancelled">Cancelled</option>
                <option value="completed">Completed</option>
            </select>
            <?php if (!empty($bookings)): ?>
                <table id="bookingsTable">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Car</th>
                            <th>Dates</th>
                            <th>Total Price</th>
                            <th>Booking Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                                <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['car_brand'] . ' ' . $booking['car_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['start_date']) . ' to ' . htmlspecialchars($booking['end_date']); ?></td>
                                <td>$<?php echo htmlspecialchars($booking['total_price']); ?></td>
                                <td>
                                    <select class="status-dropdown" onchange="updateBookingStatus(this.value, <?php echo $booking['booking_id']; ?>)">
                                        <option value="pending" <?php echo ($booking['booking_status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo ($booking['booking_status'] === 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="cancelled" <?php echo ($booking['booking_status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                        <option value="completed" <?php echo ($booking['booking_status'] === 'completed') ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-content">No bookings for your cars yet.</p>
            <?php endif; ?>

        <?php elseif ($active_tab === 'payments'): ?>
            <h2>Payments Overview</h2>
            <select id="paymentFilter" onchange="filterPayments()" style="margin-bottom:10px; padding:5px;">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="paid">Paid</option>
                <option value="fail">Fail</option>
            </select>
            <?php if (!empty($payments)): ?>
                <table id="paymentsTable">
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Car</th>
                            <th>Amount</th>
                            <th>Payment Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($payment['payment_id']); ?></td>
                                <td><?php echo htmlspecialchars($payment['booking_id']); ?></td>
                                <td><?php echo htmlspecialchars($payment['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($payment['car_brand'] . ' ' . $payment['car_name']); ?></td>
                                <td>$<?php echo htmlspecialchars($payment['amount']); ?></td>
                                <td>
                                    <select class="status-dropdown" onchange="updatePaymentStatus(this.value, <?php echo $payment['payment_id']; ?>)">
                                        <option value="pending" <?php echo ($payment['payment_status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="paid" <?php echo ($payment['payment_status'] === 'paid') ? 'selected' : ''; ?>>Paid</option>
                                        <option value="fail" <?php echo ($payment['payment_status'] === 'fail') ? 'selected' : ''; ?>>Fail</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-content">No payments recorded for your cars yet.</p>
            <?php endif; ?>

        <?php elseif ($active_tab === 'reviews'): ?>
            <h2>Car Reviews</h2>
            <select id="reviewFilter" onchange="filterReviews()" style="margin-bottom:10px; padding:5px;">
                <option value="">All Ratings</option>
                <option value="5">★★★★★ (5 Stars)</option>
                <option value="4">★★★★☆ (4 Stars)</option>
                <option value="3">★★★☆☆ (3 Stars)</option>
                <option value="2">★★☆☆☆ (2 Stars)</option>
                <option value="1">★☆☆☆☆ (1 Star)</option>
            </select>

            <?php if (!empty($reviews)): ?>
                <table id="reviewsTable">
                    <thead>
                        <tr>
                            <th>Review ID</th>
                            <th>Customer</th>
                            <th>Car</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $review): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($review['review_id']); ?></td>
                                <td><?php echo htmlspecialchars($review['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($review['car_brand'] . ' ' . $review['car_name']); ?></td>
                                <td data-rating="<?php echo $review['rating']; ?>">
                                    <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $review['rating']) ? '★' : '☆'; ?>
                                </td>
                                <td><?php echo htmlspecialchars($review['review_text']); ?></td>
                                <td><?php echo htmlspecialchars($review['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-content">No reviews for your cars yet.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        const messageBox = document.getElementById('messageBox');

        function showMessage(message, type) {
            messageBox.textContent = message;
            messageBox.className = 'message-box ' + type;
            messageBox.style.display = 'block';
            setTimeout(() => {
                messageBox.style.display = 'none';
            }, 5000);
        }

        function updateCarStatus(newStatus, carId) {
            const formData = new FormData();
            formData.append('car_id', carId);
            formData.append('status', newStatus);

            fetch('update_car_status.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage('Car status updated successfully!', 'success');
                    } else {
                        showMessage('Error updating car status: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('An error occurred while updating the car status.', 'error');
                });
        }

        function updateBookingStatus(newStatus, bookingId) {
            const formData = new FormData();
            formData.append('booking_id', bookingId);
            formData.append('status', newStatus);

            fetch('update_booking_status.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage('Booking status updated successfully!', 'success');
                        window.location.reload();
                    } else {
                        showMessage('Error updating booking status: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('An error occurred while updating the booking status.', 'error');
                });
        }

        function updatePaymentStatus(newStatus, paymentId) {
            const formData = new FormData();
            formData.append('payment_id', paymentId);
            formData.append('status', newStatus);

            fetch('update_payment_status.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage('Payment status updated successfully!', 'success');
                        window.location.reload();
                    } else {
                        showMessage('Error updating payment status: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('An error occurred while updating the payment status.', 'error');
                });
        }

        function filterCars() {
            const input = document.getElementById('carSearch').value.toLowerCase();
            const rows = document.querySelectorAll('#carsTable tbody tr');
            rows.forEach(row => {
                const text = row.cells[0].textContent.toLowerCase();
                row.style.display = text.includes(input) ? '' : 'none';
            });
        }

        function filterBookings() {
            const value = document.getElementById('bookingFilter').value.toLowerCase();
            const rows = document.querySelectorAll('#bookingsTable tbody tr');
            rows.forEach(row => {
                const status = row.querySelector('.status-dropdown').value.toLowerCase();
                row.style.display = value === '' || status === value ? '' : 'none';
            });
        }

        function filterPayments() {
            const value = document.getElementById('paymentFilter').value.toLowerCase();
            const rows = document.querySelectorAll('#paymentsTable tbody tr');
            rows.forEach(row => {
                const status = row.querySelector('.status-dropdown').value.toLowerCase();
                row.style.display = value === '' || status === value ? '' : 'none';
            });
        }

        function filterReviews() {
            const value = document.getElementById('reviewFilter').value;
            const rows = document.querySelectorAll('#reviewsTable tbody tr');
            rows.forEach(row => {
                const rating = row.querySelector('td[data-rating]').getAttribute('data-rating');
                row.style.display = value === '' || rating === value ? '' : 'none';
            });
        }
    </script>
</body>

</html>