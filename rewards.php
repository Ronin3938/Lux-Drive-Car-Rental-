<?php
session_start();
require_once 'dbconnect.php';

$user_id = $_SESSION['user_id'] ?? null;
$reward_points = 0;

if ($user_id) {
    // Fetches the single row for the user's total points to ensure consistency with the backend script
    $query = "SELECT points FROM rewards WHERE user_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $reward_points = $stmt->fetchColumn() ?? 0;
}

// Define the available exchange options
$exchange_options = [
    1000 => 100,
    2000 => 220,
    3000 => 350,
    5000 => 600,
    10000 => 1500
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Rewards</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            line-height: 1.5;
            letter-spacing: normal;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto 0;
            padding: 2rem;
        }

        .reward-card {
            background-color: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
        }

        .exchange-card {
            background-color: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            text-align: center;
        }

        .coupon-card {
            background: linear-gradient(to right, #f9f9e0, #fff);
            border: 2px dashed #f59e0b;
            border-radius: 1rem;
            padding: 2rem;
            margin-top: 2rem;
            text-align: center;
            display: none;
            /* Initially hidden */
            animation: fadeIn 0.5s ease-in-out;
        }

        .coupon-value {
            font-size: 3rem;
            font-weight: 700;
            color: #f59e0b;
            margin: 0;
        }

        .coupon-text {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
        }

        .page-title {
            font-size: 2.25rem;
            font-weight: 700;
            text-align: center;
            color: #1f2937;
            margin-bottom: 2rem;
        }

        .sub-text {
            font-size: 1.25rem;
            color: #4b5563;
            margin-bottom: 1rem;
        }

        .points-display {
            font-size: 4rem;
            font-weight: 700;
            color: #f59e0b;
            margin-bottom: 1.5rem;
        }

        .info-text {
            color: #6b7280;
            margin-bottom: 1rem;
        }

        .exchange-buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1rem;
        }

        .exchange-buttons button {
            background-color: #f59e0b;
            color: white;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .exchange-buttons button:hover {
            background-color: #e38a0a;
        }

        #message {
            margin-top: 1rem;
            font-weight: 600;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<?php require_once 'header.php'; ?>

<body>

    <div class="container">
        <h1 class="page-title">My Rewards</h1>
        <div class="reward-card">
            <p class="sub-text">Your current reward points:</p>
            <p class="points-display"><?= htmlspecialchars($reward_points); ?></p>
            <p class="info-text">Earn **10 points** for every **$1** spent. Exchange points for amazing coupons!</p>
        </div>

        <div class="exchange-card">
            <h2 class="sub-text">Exchange Points for Coupons</h2>
            <div class="exchange-buttons">
                <?php foreach ($exchange_options as $points => $value): ?>
                    <button class="exchange-btn" data-points="<?= $points; ?>" data-value="<?= $value; ?>">
                        <?= htmlspecialchars(number_format($points)); ?> Points for $<?= htmlspecialchars($value); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <div id="message"></div>
            <div id="couponDisplay" class="coupon-card">
                <p class="coupon-text">Congratulations! You've received a new coupon:</p>
                <p class="coupon-value" id="couponValue"></p>
                <p class="info-text">Use this coupon on your next booking.</p>
            </div>
        </div>
    </div>

    <?php require_once 'footer.php'; ?>
    <script>
        document.querySelectorAll('.exchange-btn').forEach(button => {
            button.addEventListener('click', function() {
                const pointsToExchange = parseInt(this.dataset.points);
                const couponValue = parseInt(this.dataset.value);
                const messageDiv = document.getElementById('message');
                const currentPoints = <?= $reward_points; ?>;
                const couponDisplay = document.getElementById('couponDisplay');
                const couponValueEl = document.getElementById('couponValue');

                if (pointsToExchange > currentPoints) {
                    messageDiv.textContent = 'You do not have enough points to make this exchange.';
                    messageDiv.style.color = '#dc2626';
                    return;
                }

                messageDiv.textContent = 'Processing exchange...';
                messageDiv.style.color = '#4b5563';
                couponDisplay.style.display = 'none';

                const formData = new FormData();
                formData.append('points_to_exchange', pointsToExchange);
                formData.append('coupon_value', couponValue);

                fetch('exchange_points.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            messageDiv.textContent = data.message;
                            messageDiv.style.color = '#10b981';

                            couponValueEl.textContent = '$' + couponValue;
                            couponDisplay.style.display = 'block';

                            // Reload the page after a short delay to show the updated points
                            setTimeout(() => window.location.reload(), 3000);
                        } else {
                            messageDiv.textContent = data.message;
                            messageDiv.style.color = '#dc2626';
                        }
                    })
                    .catch(error => {
                        messageDiv.textContent = 'An unexpected error occurred. Please try again.';
                        messageDiv.style.color = '#dc2626';
                        console.error('Error:', error);
                    });
            });
        });
    </script>
</body>

</html>