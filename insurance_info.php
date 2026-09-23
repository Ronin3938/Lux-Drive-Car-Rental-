<?php
session_start();
require_once 'dbconnect.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insurance Information - LuxDrive</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .container h1,
        .container h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 20px;
        }

        .container h1 {
            font-size: 2.5em;
        }

        .container h2 {
            font-size: 1.8em;
            margin-top: 40px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
        }

        .container p {
            font-size: 1.1em;
            color: #555;
            margin-bottom: 20px;
            text-indent: 20px;
        }

        /* Basic responsive styling */
        @media (max-width: 768px) {
            .container {
                margin: 20px;
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <?php require_once 'header.php'; ?>

    <div class="container">
        <h1>Insurance Coverage</h1>
        <p>At LuxDrive, your safety and peace of mind are our top priorities. All our services come with a comprehensive insurance package that protects the vehicle, our professional drivers, and you, our valued customer.</p>

        <h2>Our Vehicle Insurance</h2>
        <p>Every vehicle in our fleet is fully covered by a **comprehensive insurance policy**. This policy protects against various risks, including damage to the vehicle, theft, and natural disasters. You can rest assured that our fleet is maintained to the highest standards and is fully protected at all times.</p>

        <h2>Professional Driver Insurance</h2>
        <p>All our professional chauffeurs are covered by our company's insurance policy. This includes coverage for third-party liability, ensuring that you and your property are protected in the unlikely event of an incident. Our drivers are trained to prioritize safety, and this coverage provides an extra layer of protection for every journey.</p>

        <h2>Your Customer Protection</h2>
        <p>Your journey with us is fully insured from the moment you step into the car until you reach your destination. Our insurance coverage extends to you, the passenger, providing protection against any injuries that may occur during the service. This means you can enjoy a worry-free ride, knowing that your safety is our utmost concern.</p>

        <h2>No Extra Cost to You</h2>
        <p>Unlike many other services, our insurance coverage is **included** in the price of your booking. There are no hidden fees or extra costs for purchasing additional insurance. The price you see is the final price, and it includes full protection for the vehicle, the driver, and yourself.</p>

        <p>By choosing LuxDrive, you are choosing a service built on trust, transparency, and a commitment to your safety. Thank you for riding with us.</p>
    </div>

    <?php require_once 'footer.php'; ?>
</body>

</html>