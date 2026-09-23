<?php
// send_availability_notifications.php
// This script checks for all newly available cars and sends notifications.
// You can run this file manually or set it up as a periodic cron job.

require_once 'dbconnect.php';
require_once 'mail.php'; // Your email function file

echo "Starting batch check for new car availability...\n";

try {
    // Step 1: Find all users who have wishlisted cars that are now available
    // and have not yet been notified.
    $query = "
        SELECT 
            w.user_id, u.email, u.name AS user_name,
            c.name AS car_name, c.brand, c.id AS car_id
        FROM wishlists w
        JOIN users u ON w.user_id = u.id
        JOIN cars c ON w.car_id = c.id
        WHERE c.availability_status = 'available' AND w.notified_status = 'not_notified'
    ";

    $stmt = $pdo->query($query);
    $notifications_to_send = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($notifications_to_send)) {
        echo "Found " . count($notifications_to_send) . " total notifications to send.\n";

        // Step 2: Loop through each notification and send the email
        foreach ($notifications_to_send as $item) {
            $toEmail = $item['email'];
            $subject = 'A Wishlisted Car is Now Available!';
            $body = "Hi " . htmlspecialchars($item['user_name']) . ",<br><br>"
                . "The car you added to your wishlist, the <b>" . htmlspecialchars($item['brand']) . " " . htmlspecialchars($item['car_name']) . "</b>, is now available for rent!<br><br>"
                . "Visit our website to book it now.<br><br>"
                . "Best regards,<br>"
                . "The Lux Drive Team";

            echo "Attempting to send email to " . htmlspecialchars($item['user_name']) . " for car " . htmlspecialchars($item['car_name']) . "...\n";
            $emailSent = sendNotificationEmail($toEmail, $subject, $body);

            if ($emailSent) {
                // Step 3: Update the database to prevent re-notification
                $update_query = "
                    UPDATE wishlists
                    SET notified_status = 'notified'
                    WHERE user_id = :user_id AND car_id = :car_id
                ";
                $update_stmt = $pdo->prepare($update_query);
                $update_stmt->execute([
                    ':user_id' => $item['user_id'],
                    ':car_id' => $item['car_id']
                ]);
                echo "SUCCESS: Notification sent and wishlist status updated.\n";
            } else {
                echo "FAILURE: Failed to send notification to " . htmlspecialchars($item['user_name']) . ". Check your email and PHPMailer settings.\n";
            }
        }
    } else {
        echo "No new availability changes for wishlisted cars.\n";
    }

    // Optional Step: Reset notification status for cars that are no longer available
    $reset_query = "
        UPDATE wishlists w
        JOIN cars c ON w.car_id = c.id
        SET w.notified_status = 'not_notified'
        WHERE c.availability_status != 'available' AND w.notified_status = 'notified'
    ";
    $pdo->exec($reset_query);
    echo "Notification statuses for unavailable cars have been reset.\n";
} catch (PDOException $e) {
    error_log("Database error in notification script: " . $e->getMessage());
    echo "FATAL ERROR: A database error occurred.\n";
    echo "Error message: " . $e->getMessage() . "\n";
}
