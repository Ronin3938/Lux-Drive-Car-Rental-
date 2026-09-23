<?php
session_start();
require_once 'dbconnect.php';

// Check if a user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$current_date = new DateTime();

// Get user's total reward points
$points_query = $pdo->prepare("SELECT SUM(points) FROM rewards WHERE user_id = ?");
$points_query->execute([$user_id]);
$reward_points = $points_query->fetchColumn() ?: 0;

// Fetch all bookings with first car image from car_images table
$bookings_query = "
    SELECT
        b.id AS booking_id, b.start_date, b.end_date, b.total_price, b.status,
        c.name AS car_name, c.brand, c.price_per_day,
        ci.image
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    LEFT JOIN (
        SELECT car_id, image 
        FROM car_images
        GROUP BY car_id
    ) ci ON c.id = ci.car_id
    WHERE b.user_id = ?
    ORDER BY b.start_date DESC
";
$bookings_stmt = $pdo->prepare($bookings_query);
$bookings_stmt->execute([$user_id]);
$all_bookings = $bookings_stmt->fetchAll(PDO::FETCH_ASSOC);

$pending_bookings = [];
$accepted_bookings = [];
$completed_bookings = [];

foreach ($all_bookings as $booking) {
    switch ($booking['status']) {
        case 'pending':
            $pending_bookings[] = $booking;
            break;
        case 'accepted':
        case 'confirmed':
            $accepted_bookings[] = $booking;
            break;
        case 'completed':
            $completed_bookings[] = $booking;
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Booking History - Lux Drive</title>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        h1,
        h2 {
            text-align: center;
            margin-bottom: 1rem;
        }

        .reward-points-card {
            background-color: #03612cff;
            color: #fff;
            font-weight: 600;
            font-size: 1.25rem;
            text-align: center;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .booking-section {
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .booking-card {
            display: flex;
            align-items: center;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .booking-card img {
            width: 150px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 1.5rem;
        }

        .booking-details {
            flex-grow: 1;
        }

        .booking-details h3 {
            color: #066614;
            margin-bottom: 0.5rem;
        }

        .booking-details p {
            margin-bottom: 0.25rem;
        }

        .status-badge {
            font-weight: bold;
            text-transform: uppercase;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #a16207;
        }

        .status-accepted {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-completed {
            background-color: #e5e7eb;
            color: #4b5563;
        }

        .status-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .cancel-btn {
            background-color: #dc3545;
            color: #fff;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .cancel-btn:hover {
            background-color: #c82333;
        }

        .popup-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
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

        .text-center {
            text-align: center;
        }

        .text-gray {
            color: #555;
        }

        .mb-8 {
            margin-bottom: 2rem;
        }

        .mt-8 {
            margin-top: 2rem;
        }

        /* Custom Confirmation Modal */
        #confirmModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }

        #confirmModal .modal-content {
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.3s ease-in-out;
        }

        #confirmModal h3 {
            margin-bottom: 1rem;
            color: #333;
            font-size: 1.2rem;
        }

        #confirmModal p {
            margin-bottom: 1.5rem;
            color: #555;
            font-size: 1rem;
        }

        #confirmModal .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        #confirmModal .btn {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            cursor: pointer;
            transition: background 0.3s;
        }

        #confirmModal .btn-cancel {
            background: #6c757d;
            color: #fff;
        }

        #confirmModal .btn-cancel:hover {
            background: #5a6268;
        }

        #confirmModal .btn-confirm {
            background: #dc3545;
            color: #fff;
        }

        #confirmModal .btn-confirm:hover {
            background: #c82333;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <?php require_once 'header.php'; ?>

    <div id="popup-notification" class="popup-message"></div>

    <div class="container mt-8">
        <h1>My Booking History</h1>

        <div class="reward-points-card">
            You have <strong><?= htmlspecialchars($reward_points); ?></strong> reward points!
        </div>

        <?php if (!$user_id): ?>
            <div class="text-center text-gray">Please log in to view your booking history.</div>
        <?php elseif (empty($all_bookings)): ?>
            <div class="text-center text-gray">You have no bookings yet. Start by renting a car!</div>
        <?php else: ?>
            <!-- Pending Bookings -->
            <div class="booking-section">
                <h2>Pending Bookings</h2>
                <?php if (empty($pending_bookings)): ?>
                    <p class="text-gray text-center">You have no upcoming bookings.</p>
                <?php else: ?>
                    <?php foreach ($pending_bookings as $booking): ?>
                        <div class="booking-card">
                            <img src="uploads/car_images/<?= htmlspecialchars($booking['image'] ?? 'default.jpg'); ?>" alt="<?= htmlspecialchars($booking['brand'] . ' ' . $booking['car_name']); ?>">
                            <div class="booking-details">
                                <h3><?= htmlspecialchars($booking['brand'] . ' ' . $booking['car_name']); ?></h3>
                                <p><strong>From:</strong> <?= htmlspecialchars($booking['start_date']); ?></p>
                                <p><strong>To:</strong> <?= htmlspecialchars($booking['end_date']); ?></p>
                                <p><strong>Total Price:</strong> $<?= htmlspecialchars(number_format($booking['total_price'], 2)); ?></p>
                                <p><strong>Status:</strong> <span class="status-badge status-pending">Pending</span></p>
                            </div>
                            <button class="cancel-btn" data-booking-id="<?= htmlspecialchars($booking['booking_id']); ?>">Cancel</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <!-- Custom Cancel Confirmation Modal -->
            <div id="confirmModal">
                <div class="modal-content">
                    <h3>Cancel Booking</h3>
                    <p>Are you sure you want to cancel this booking?</p>
                    <div class="modal-buttons">
                        <button class="btn btn-cancel" id="cancelNo">No</button>
                        <button class="btn btn-confirm" id="cancelYes">Yes, Cancel</button>
                    </div>
                </div>
            </div>


            <!-- Accepted Bookings -->
            <div class="booking-section">
                <h2>Accepted Bookings</h2>
                <?php if (empty($accepted_bookings)): ?>
                    <p class="text-gray text-center">You have no active bookings.</p>
                <?php else: ?>
                    <?php foreach ($accepted_bookings as $booking): ?>
                        <div class="booking-card">
                            <img src="uploads/car_images/<?= htmlspecialchars($booking['image'] ?? 'default.jpg'); ?>" alt="<?= htmlspecialchars($booking['brand'] . ' ' . $booking['car_name']); ?>">
                            <div class="booking-details">
                                <h3><?= htmlspecialchars($booking['brand'] . ' ' . $booking['car_name']); ?></h3>
                                <p><strong>From:</strong> <?= htmlspecialchars($booking['start_date']); ?></p>
                                <p><strong>To:</strong> <?= htmlspecialchars($booking['end_date']); ?></p>
                                <p><strong>Total Price:</strong> $<?= htmlspecialchars(number_format($booking['total_price'], 2)); ?></p>
                                <p><strong>Status:</strong> <span class="status-badge status-accepted">Accepted</span></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Completed Bookings -->
            <div class="booking-section">
                <h2>Completed Bookings</h2>
                <?php if (empty($completed_bookings)): ?>
                    <p class="text-gray text-center">You have no completed bookings.</p>
                <?php else: ?>
                    <?php foreach ($completed_bookings as $booking): ?>
                        <div class="booking-card">
                            <img src="uploads/car_images/<?= htmlspecialchars($booking['image'] ?? 'default.jpg'); ?>" alt="<?= htmlspecialchars($booking['brand'] . ' ' . $booking['car_name']); ?>">
                            <div class="booking-details">
                                <h3><?= htmlspecialchars($booking['brand'] . ' ' . $booking['car_name']); ?></h3>
                                <p><strong>From:</strong> <?= htmlspecialchars($booking['start_date']); ?></p>
                                <p><strong>To:</strong> <?= htmlspecialchars($booking['end_date']); ?></p>
                                <p><strong>Total Price:</strong> $<?= htmlspecialchars(number_format($booking['total_price'], 2)); ?></p>
                                <p><strong>Status:</strong> <span class="status-badge status-completed">Completed</span></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        const showPopup = (message, type) => {
            const popup = document.getElementById('popup-notification');
            popup.textContent = message;
            popup.className = `popup-message show ${type}`;
            setTimeout(() => {
                popup.classList.remove('show');
            }, 3000);
        };

        let bookingToCancel = null;

        document.querySelectorAll('.cancel-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                bookingToCancel = e.target.dataset.bookingId;
                document.getElementById('confirmModal').style.display = 'flex';
            });
        });

        document.getElementById('cancelNo').addEventListener('click', () => {
            document.getElementById('confirmModal').style.display = 'none';
            bookingToCancel = null;
        });

        document.getElementById('cancelYes').addEventListener('click', async () => {
            if (!bookingToCancel) return;

            try {
                const response = await fetch('cancel_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        booking_id: bookingToCancel
                    })
                });
                const data = await response.json();
                if (data.status === 'success') {
                    showPopup('Booking successfully cancelled!', 'success');
                    document.querySelector(`[data-booking-id="${bookingToCancel}"]`).closest('.booking-card').remove();
                } else {
                    showPopup(data.message, 'error');
                }
            } catch (err) {
                console.error(err);
                showPopup('An error occurred while cancelling the booking.', 'error');
            }

            document.getElementById('confirmModal').style.display = 'none';
            bookingToCancel = null;
        });
    </script>

    <?php require_once 'footer.php'; ?>
</body>

</html>