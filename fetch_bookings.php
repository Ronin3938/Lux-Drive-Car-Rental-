<?php
require_once 'dbconnect.php';

// Get filter values
$bookingStatus = isset($_GET['booking_status']) ? trim($_GET['booking_status']) : '';

// Build query
$bookingQuery = "
    SELECT
        b.id, b.start_date, b.end_date, b.total_price, b.status,
        b.pickup_address, b.dropoff_lon, b.dropoff_lat, b.pickup_lon, b.pickup_lat,
        u.name AS user_name,
        c.name AS car_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN cars c ON b.car_id = c.id
    WHERE 1=1
";

$params = [];

if ($bookingStatus !== '') {
    $bookingQuery .= " AND b.status = :status";
    $params[':status'] = $bookingStatus;
}

$stmt = $pdo->prepare($bookingQuery);
$stmt->execute($params);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output HTML table rows
foreach ($bookings as $booking) {
    echo "<tr>
        <td>" . htmlspecialchars($booking['id']) . "</td>
        <td>" . htmlspecialchars($booking['user_name']) . "</td>
        <td>" . htmlspecialchars($booking['car_name']) . "</td>
        <td>" . htmlspecialchars($booking['start_date']) . "</td>
        <td>" . htmlspecialchars($booking['end_date']) . "</td>
        <td>" . htmlspecialchars($booking['pickup_lat'] ?? 'N/A') . "</td>
        <td>" . htmlspecialchars($booking['pickup_lon'] ?? 'N/A') . "</td>
        <td>" . htmlspecialchars($booking['dropoff_lat'] ?? 'N/A') . "</td>
        <td>" . htmlspecialchars($booking['dropoff_lon'] ?? 'N/A') . "</td>
        <td>$" . htmlspecialchars($booking['total_price']) . "</td>
        <td>" . htmlspecialchars($booking['status']) . "</td>
        <td>
            <form method='post' class='action-form' onsubmit=\"return confirm('Delete this booking?');\" action='booking_process.php'>
                <input type='hidden' name='delete_booking_id' value='" . htmlspecialchars($booking['id']) . "'>
                <button type='submit' name='delete_booking' class='btn-delete'>Delete</button>
            </form>
        </td>
    </tr>";
}
