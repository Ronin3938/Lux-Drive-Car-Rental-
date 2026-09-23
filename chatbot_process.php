<?php
session_start();
require_once 'dbconnect.php'; // your PDO $pdo connection
header('Content-Type: application/json');

// Helper: safe get car rows with conditions and params
function getCarDetails($conditions = [], $params = [], $limit = 5)
{
    global $pdo;
    $limit = (int)$limit;
    $sql = "SELECT id, brand, name, price_per_day, availability_status, description, seats, category_id
            FROM cars";
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    $sql .= " LIMIT $limit";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Logged-in user ID
$userId = $_SESSION['user_id'] ?? null;

// Main handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $raw = trim($_POST['message']);
    $msg = strtolower($raw);
    $response = "I'm not sure how to respond to that. Can you please be more specific?";
    $options = [];

    // --- Simple greetings / keywords ---
    if (preg_match('/\b(hello|hi|hey)\b/', $msg)) {
        $response = "Hi there! 👋 How can I assist you today?";
        $options = ["View Prices", "Available Cars", "Booking Instructions", "Contact Support"];
    } elseif (strpos($msg, 'bye') !== false) {
        $response = "Goodbye! Let me know if you need anything else.";
    } elseif (strpos($msg, 'rent') !== false) {
        $response = "You can rent a car on our Rent Cars page.";
        $options = ["View Prices", "Available Cars", "Booking Instructions"];
    } elseif (in_array($msg, ['view prices', 'prices', 'price'])) {
        $cars = getCarDetails([], [], 8);
        if ($cars) {
            $lines = ["Here are some prices:"];
            foreach ($cars as $car) $lines[] = "- {$car['brand']} {$car['name']}: \${$car['price_per_day']} / day";
            $response = implode("\n", $lines);
        } else $response = "Sorry, no cars available right now.";
        $options = ["Available Cars", "Booking Instructions"];
    } elseif (in_array($msg, ['available cars', 'cars', 'available'])) {
        $cars = getCarDetails(["availability_status = 'available'"], [], 10);
        if ($cars) {
            $lines = ["Available cars:"];
            foreach ($cars as $car) $lines[] = "- {$car['brand']} {$car['name']} ({$car['seats']} seats) - \${$car['price_per_day']}/day";
            $response = implode("\n", $lines);
        } else $response = "No cars available right now.";
        $options = ["View Prices", "Booking Instructions"];
    } elseif (strpos($msg, 'booking') !== false || strpos($msg, 'how to book') !== false) {
        $response = "📌 How to book:\n1) Log in\n2) Choose car\n3) Pick dates\n4) Enter pickup/dropoff\n5) Pay & confirm";
        $options = ["View Prices", "Available Cars", "Contact Support"];
    } elseif (strpos($msg, 'support') !== false || strpos($msg, 'help') !== false) {
        $response = "Contact support at support@luxxy.com or +123-456-7890.";
    }

    // --- User-specific queries ---
    elseif ($userId && (strpos($msg, 'my reward') !== false || strpos($msg, 'my points') !== false)) {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(points),0) AS total FROM rewards WHERE user_id = ?");
        $stmt->execute([$userId]);
        $points = $stmt->fetchColumn();
        $response = "🎁 You currently have {$points} reward points.";
    } elseif ($userId && strpos($msg, 'my bookings') !== false) {
        $stmt = $pdo->prepare("SELECT b.id, c.brand, c.name, b.start_date, b.end_date, b.status
                               FROM bookings b 
                               JOIN cars c ON b.car_id = c.id
                               WHERE b.user_id = ?
                               ORDER BY b.start_date DESC LIMIT 3");
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) {
            $lines = ["📅 Your last bookings:"];
            foreach ($rows as $r) {
                $lines[] = "- {$r['brand']} {$r['name']} ({$r['start_date']} → {$r['end_date']}) [{$r['status']}]";
            }
            $response = implode("\n", $lines);
        } else $response = "You have no bookings yet.";
    } elseif ($userId && strpos($msg, 'my profile') !== false) {
        $stmt = $pdo->prepare("SELECT first_name,last_name,email,phone FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($u) {
            $response = "👤 Profile Info:\nName: {$u['first_name']} {$u['last_name']}\nEmail: {$u['email']}\nPhone: {$u['phone']}";
        } else $response = "Profile not found.";
    }

    // --- Smart filters: seats ---
    if (preg_match('/\b(\d+)\s*(?:-?\s*(?:seat|seats|seater|seaters))\b/i', $msg, $m)) {
        $seats = (int)$m[1];
        $cars = getCarDetails(["seats = :seats"], [":seats" => $seats], 10);
        if ($cars) {
            $lines = ["Cars with {$seats} seats:"];
            foreach ($cars as $car) {
                $lines[] = "- {$car['brand']} {$car['name']} — \${$car['price_per_day']}/day — Status: " . ucfirst($car['availability_status']);
            }
            $response = implode("\n", $lines);
            $options = ["Available Cars", "View Prices", "Booking Instructions"];
        } else $response = "Sorry — no cars with {$seats} seats found right now.";
        echo json_encode(['message' => $response, 'options' => $options]);
        exit;
    }

    // --- Smart filters: price (e.g., "under $50") ---
    if (preg_match('/under ?\s*\$?(\d+)/i', $msg, $m)) {
        $price = (float)$m[1];
        $cars = getCarDetails(["price_per_day <= :price"], [":price" => $price], 10);
        if ($cars) {
            $lines = ["Cars under \${$price}/day:"];
            foreach ($cars as $car) $lines[] = "- {$car['brand']} {$car['name']} — \${$car['price_per_day']}/day";
            $response = implode("\n", $lines);
        } else $response = "No cars found under \${$price}/day.";
        echo json_encode(['message' => $response, 'options' => $options]);
        exit;
    }

    // --- Smart filters: brand ---
    if (preg_match('/\b(toyota|honda|bmw|mercedes|tesla|audi|nissan)\b/i', $msg, $m)) {
        $brand = strtolower($m[1]);
        $cars = getCarDetails(["LOWER(brand) = :brand"], [":brand" => $brand], 10);
        if ($cars) {
            $lines = [ucfirst($brand) . " cars:"];
            foreach ($cars as $car) $lines[] = "- {$car['brand']} {$car['name']} — \${$car['price_per_day']}/day";
            $response = implode("\n", $lines);
        } else $response = "No {$brand} cars available right now.";
        echo json_encode(['message' => $response, 'options' => $options]);
        exit;
    }

    // --- Fallback keyword search ---
    $words = preg_split('/\s+/', $msg);
    $clauses = [];
    $params = [];
    foreach ($words as $i => $word) {
        $param = ":word$i";
        $clauses[] = "(LOWER(name) LIKE $param OR LOWER(brand) LIKE $param)";
        $params[$param] = "%$word%";
    }
    if (!empty($clauses)) {
        $where = implode(" OR ", $clauses);
        $cars = getCarDetails([$where], $params, 6);
        if ($cars) {
            $car = $cars[0];
            $response = "{$car['brand']} {$car['name']} — \${$car['price_per_day']}/day\nSeats: {$car['seats']}\nStatus: {$car['availability_status']}\n{$car['description']}";
        }
    }

    echo json_encode(['message' => $response, 'options' => $options]);
    exit;
}

// If reached without POST/message
echo json_encode(['message' => 'Invalid request.']);
exit;
