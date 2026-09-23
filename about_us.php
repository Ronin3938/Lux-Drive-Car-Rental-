<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Lux Drive</title>
    <link rel="stylesheet" href="css/rentcars.css">
    <link rel="stylesheet" href="css/homepage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .about-container {
            max-width: 1500px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .about-container h1,
        .about-container h2 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
            font-size: 2.5rem;
            /* Bigger font size for headings */
        }

        .about-container h2 {
            font-size: 2rem;
            /* Bigger font size for subheadings */
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-top: 40px;
        }

        .about-container p,
        .about-container li {
            font-size: 1.2rem;
            line-height: 1.8;
            margin-bottom: 20px;
            color: #555;
            text-align: justify;
            text-indent: 20px;
        }

        .about-container strong {
            color: #000;
        }

        .section {
            margin-bottom: 30px;
        }

        .image-section {
            text-align: center;
            margin: 30px 0;
        }

        .image-section img {
            max-width: 50%;
            height: auto;
            border-radius: 10%;
            box-shadow: 0 4px 8px linear-gradient(135deg, #05d563ff 0%, #04f1b2ff 100%);
        }

        .image-section2 {
            text-align: center;
            margin: 30px 0;
        }

        .image-section2 img {
            max-width: 30%;
            height: auto;
            border-radius: 10%;
            box-shadow: 0 4px 8px linear-gradient(135deg, #05d563ff 0%, #04f1b2ff 100%);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .about-container {
                margin: 20px;
                padding: 15px;
            }

            .about-container h1,
            .about-container h2 {
                font-size: 2rem;
            }

            .about-container h2 {
                font-size: 1.5rem;
            }

            .about-container p,
            .about-container li {
                font-size: 1rem;
                text-indent: 20px;
            }
        }
    </style>
</head>

<body>
    <?php require_once 'header.php'; ?>

    <div class="about-container">
        <h1>About Lux Drive</h1>
        <div class="image-section">
            <img src="homepageimg/logo.jpg" alt="Lux Drive banner">
        </div>
        <p>Welcome to Lux Drive, the premier platform for high-quality car rentals. We're dedicated to a simple mission: providing a seamless, reliable, and exceptional car rental experience for everyone. We believe that getting behind the wheel of your next vehicle should be effortless, whether it's for a quick trip or a long-term journey.</p>

        <div class="section">
            <h2>How It Works</h2>
            <p>At Lux Drive, we operate on a <strong>B2B2C (Business-to-Business-to-Consumer)</strong> model that creates a win-win for everyone. Our platform acts as the central hub, connecting verified car rental companies directly with you, the customer.</p>
            <p><strong>For Rental Companies:</strong> We provide a powerful, low-cost solution to grow your business. By listing your fleet on Lux Drive, you gain instant visibility to a wide audience of potential renters. We handle all the heavy lifting—from managing listings and bookings to processing payments—allowing you to focus on what you do best: providing great cars and service. In return, we simply charge a commission on each successful booking.</p>
            <p><strong>For Customers:</strong> We give you the power of choice and convenience. Instead of sifting through countless individual websites, you can explore, compare, and book a wide range of high-quality vehicles from multiple trusted companies, all in one place. Our platform simplifies the entire process, ensuring a smooth and user-friendly experience from start to finish.</p>
            <p>This unique structure is designed for efficiency, trust, and scalability. It allows us to offer you an extensive selection of cars while helping our partners thrive in the market.</p>
        </div>

        <div class="section">
            <h2>Loyalty Programs & Incentives</h2>
            <p>At Lux Drive, we deeply value our customers and believe in rewarding your loyalty. Our commitment goes beyond a single transaction; we want to build a lasting relationship with you.</p>
            <p><strong>How We Reward You:</strong></p>
            <ul>
                <li><strong>Reward Points:</strong> Every time you book a car with Lux Drive, you'll earn points that can be redeemed for discounts on future rentals. The more you travel with us, the more you save.</li>
                <li><strong>Exclusive Discounts:</strong> As a valued member, you'll receive access to special discounts and promotions available only to our loyal customers.</li>
                <li><strong>Personalized Offers:</strong> By understanding your preferences, we can tailor offers and promotions that match your travel needs, ensuring you get the best value on the vehicles you love.</li>
            </ul>
            <p>These programs not only provide extra value but also help us better understand what you’re looking for, allowing us to continuously improve your Lux Drive experience.</p>
        </div>

        <div class="section">
            <div class="image-section2">
                <img src="homepageimg/donation.jpg" alt="Lux Drive banner">
            </div>
            <h2>Donations: Driving Change, Together</h2>
            <p>At Lux Drive, our mission extends beyond the road. We are committed to making a positive impact on the communities we serve. A portion of every booking made through our platform is donated to local environmental protection funds or charities focused on safe driving education.</p>
            <p>By choosing Lux Drive, you are not only securing a quality car rental but also contributing to a meaningful cause. Together, we can drive a better future.</p>
        </div>

        <div class="section">
            <h2>Why Choose Lux Drive?</h2>
            <ul>
                <li><strong>Quality & Service:</strong> We prioritize car quality and service excellence. Our network includes only vetted rental companies committed to providing well-maintained vehicles and professional service.</li>
                <li><strong>Convenience:</strong> Our user-friendly platform makes it easy to find, compare, and book your perfect car in just a few clicks.</li>
                <li><strong>Trust:</strong> With our secure booking and payment system, you can rent with confidence, knowing every transaction is handled safely.</li>
                <li><strong>Variety:</strong> Gain access to a diverse fleet of vehicles, from economy cars for city travel to luxury SUVs for an upscale experience.</li>
            </ul>
        </div>
        <p style="text-align: center;">At Lux Drive, we're not just a booking platform—we're a community built on a shared passion for a better car rental experience. Start your journey with us today.</p>
    </div>

    <?php require_once 'footer.php'; ?>
</body>

</html>