-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 19, 2025 at 09:57 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `car_rental`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `car_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `discount_applied` tinyint(1) NOT NULL DEFAULT 0,
  `coupon_id` int(11) DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `pickup_address` varchar(255) NOT NULL,
  `pickup_lat` decimal(10,8) DEFAULT NULL,
  `pickup_lon` decimal(11,8) DEFAULT NULL,
  `dropoff_lat` decimal(10,8) DEFAULT NULL,
  `dropoff_lon` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `car_id`, `start_date`, `end_date`, `total_price`, `discount_applied`, `coupon_id`, `status`, `created_at`, `pickup_address`, `pickup_lat`, `pickup_lon`, `dropoff_lat`, `dropoff_lon`) VALUES
(1, 1, 1, '2025-09-17', '2025-09-25', 928.80, 1, NULL, 'completed', '2025-09-17 09:35:25', '', 16.82374910, 96.13066550, 16.79840000, 96.14960000),
(2, 3, 2, '2025-09-17', '2025-09-20', 294.30, 1, NULL, 'completed', '2025-09-17 17:29:42', '', 16.82345789, 96.13074039, 16.79840000, 96.14960000),
(3, 3, 2, '2025-09-27', '2025-10-10', 1275.30, 1, NULL, 'completed', '2025-09-17 17:56:14', '', 16.81607650, 96.15460777, 16.79840000, 96.14960000);

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `brand` varchar(50) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `seats` int(11) DEFAULT NULL,
  `price_per_day` decimal(10,2) DEFAULT NULL,
  `availability_status` enum('available','booked','maintenance') DEFAULT 'available',
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `fuel_type` enum('petrol','diesel','electric','hybrid') NOT NULL DEFAULT 'petrol'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`id`, `name`, `brand`, `category_id`, `company_id`, `seats`, `price_per_day`, `availability_status`, `image`, `description`, `fuel_type`) VALUES
(1, 'Mercedes-Benz A-Class 2019', 'Mercedes-Benz', NULL, 1, 4, 129.00, 'available', NULL, 'Experience luxury and performance with the Mercedes-Benz A-Class 2019. This compact executive car combines sleek design, advanced technology, and smooth handling to make every drive enjoyable. Equipped with a comfortable interior, premium features, and fuel-efficient performance, it’s perfect for city trips or long drives. Rent this stylish Mercedes-Benz for a sophisticated and reliable driving experience.', 'diesel'),
(2, 'Mercedes-Benz A-Class 2018', 'Mercedes-Benz', NULL, 1, 4, 109.00, 'available', NULL, 'Drive in style with the Mercedes-Benz A-Class 2018 — a perfect mix of elegance, comfort, and modern technology. This premium hatchback offers a smooth ride, advanced safety features, and a well-crafted interior designed for both city cruising and longer journeys. With its sleek design and reliable performance, the A-Class 2018 is the ideal choice for travelers who want luxury and convenience in one package.', 'diesel'),
(3, 'Land Rover Discovery Sport 2017', 'Land Rover', NULL, 2, 4, 214.00, 'available', NULL, 'Experience the perfect balance of power, luxury, and versatility with the Land Rover Discovery Sport 2017. Built for adventure and comfort, this premium SUV offers a spacious interior, advanced safety features, and exceptional off-road capability. Whether you’re navigating city streets or exploring rugged terrain, the Discovery Sport delivers a smooth and confident drive. Ideal for family trips, group travel, or anyone seeking a stylish and capable SUV.', 'petrol'),
(4, 'Tesla Model Y 2022', 'Tesla', NULL, 2, 4, 195.00, 'available', NULL, 'Step into the future of driving with the Tesla Model Y 2022. This all-electric premium SUV offers impressive range, instant acceleration, and advanced autopilot features for a smooth and effortless ride. With a sleek design, spacious interior, and cutting-edge technology, the Model Y is perfect for eco-conscious travelers who want both luxury and performance. Whether for business trips or family adventures, enjoy zero-emission driving without compromising comfort or style.', 'electric'),
(6, 'Land Rover Range Rover Velar 2020', 'Land Rover', NULL, 2, 5, 134.00, 'available', NULL, 'Elevate your driving experience with the Land Rover Range Rover Velar 2020. This luxury SUV combines cutting-edge technology, refined design, and powerful performance to deliver a smooth and commanding ride. Featuring a spacious, high-end interior and advanced safety systems, the Velar is perfect for city driving, long road trips, or off-road adventures. Rent the Range Rover Velar 2020 for unmatched comfort, style, and versatility.', 'diesel');

-- --------------------------------------------------------

--
-- Table structure for table `car_categories`
--

CREATE TABLE `car_categories` (
  `car_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `car_categories`
--

INSERT INTO `car_categories` (`car_id`, `category_id`) VALUES
(1, 1),
(1, 5),
(2, 1),
(2, 5),
(3, 1),
(3, 4),
(4, 3),
(4, 4),
(6, 1),
(6, 4);

-- --------------------------------------------------------

--
-- Table structure for table `car_features`
--

CREATE TABLE `car_features` (
  `id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `feature_name` varchar(255) NOT NULL,
  `feature_value` enum('Safety','Device connectivity','Convenience','Additional features') DEFAULT 'Safety'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `car_features`
--

INSERT INTO `car_features` (`id`, `car_id`, `feature_name`, `feature_value`) VALUES
(1, 1, 'Adaptive cruise control', 'Safety'),
(2, 1, 'Blind spot warning', 'Safety'),
(3, 1, 'AUX input', 'Device connectivity'),
(4, 1, 'Bluetooth', 'Device connectivity'),
(5, 1, 'USB charger', 'Device connectivity'),
(6, 1, 'USB input', 'Device connectivity'),
(7, 1, 'GPS', 'Convenience'),
(8, 1, 'Heated seats', 'Additional features'),
(9, 2, 'Adaptive cruise control', 'Safety'),
(10, 2, 'Backup camera', 'Safety'),
(11, 2, 'AUX input', 'Device connectivity'),
(12, 2, 'USB charger', 'Device connectivity'),
(13, 2, 'GPS', 'Convenience'),
(14, 2, 'Front & Rear Parking Sensors', 'Additional features'),
(15, 2, 'First Aid Kit (Below Boot)', 'Additional features'),
(16, 3, 'Adaptive cruise control', 'Safety'),
(17, 3, 'Backup camera', 'Safety'),
(18, 3, 'AUX input', 'Device connectivity'),
(19, 3, 'Bluetooth', 'Device connectivity'),
(20, 3, 'USB charger', 'Device connectivity'),
(21, 3, 'GPS', 'Convenience'),
(22, 3, 'Sunroof', 'Additional features'),
(23, 3, 'Front & Rear Parking Sensors', 'Additional features'),
(24, 3, 'Must be 25+ to book', 'Additional features'),
(25, 4, 'Adaptive cruise control', 'Safety'),
(26, 4, 'Backup camera', 'Safety'),
(27, 4, 'Blind spot warning', 'Safety'),
(28, 4, 'AUX input', 'Device connectivity'),
(29, 4, 'Bluetooth', 'Device connectivity'),
(30, 4, 'USB charger', 'Device connectivity'),
(31, 4, 'USB input', 'Device connectivity'),
(32, 4, 'GPS', 'Convenience'),
(33, 4, 'Keyless entry', 'Convenience'),
(34, 4, 'Heated seats', 'Additional features'),
(35, 4, 'Long term', 'Additional features'),
(36, 4, 'Sunroof', 'Additional features'),
(49, 6, 'Adaptive cruise control', 'Safety'),
(50, 6, 'Backup camera', 'Safety'),
(51, 6, 'Blind spot warning', 'Safety'),
(52, 6, 'AUX input', 'Device connectivity'),
(53, 6, 'Bluetooth', 'Device connectivity'),
(54, 6, 'USB charger', 'Device connectivity'),
(55, 6, 'USB input', 'Device connectivity'),
(56, 6, 'GPS', 'Convenience'),
(57, 6, 'Sunroof', 'Additional features'),
(58, 6, 'Front & Rear Parking Sensors', 'Additional features'),
(59, 6, 'Front Heated Windscreen', 'Additional features'),
(60, 6, 'Black Roof Rails', 'Additional features'),
(61, 6, 'Front Message Seats', 'Additional features');

-- --------------------------------------------------------

--
-- Table structure for table `car_images`
--

CREATE TABLE `car_images` (
  `id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `car_images`
--

INSERT INTO `car_images` (`id`, `car_id`, `image`) VALUES
(1, 1, 'car_68ca75f3aa2dc5.29983950.jpg'),
(2, 1, 'car_68ca75f3ab5710.04903410.jpg'),
(3, 1, 'car_68ca75f3abc251.95851996.jpg'),
(4, 1, 'car_68ca75f3ac2503.19487488.jpg'),
(5, 1, 'car_68ca75f3ac8cd4.59848243.jpg'),
(6, 2, 'car_68ca7706e5e895.47945973.jpg'),
(7, 2, 'car_68ca7706e64a58.25684456.jpg'),
(8, 2, 'car_68ca7706e6b1c0.62858855.jpg'),
(9, 2, 'car_68ca7706e74a55.60491671.jpg'),
(10, 2, 'car_68ca7706e789b0.89031575.jpg'),
(11, 2, 'car_68ca7706e7de62.90214661.jpg'),
(12, 3, 'car_68ca797f9d5666.84443565.jpg'),
(13, 3, 'car_68ca797f9da911.49501171.jpg'),
(14, 3, 'car_68ca797f9ded46.74653059.jpg'),
(15, 3, 'car_68ca797f9e6004.67472073.jpg'),
(16, 3, 'car_68ca797f9eb594.99992208.jpg'),
(17, 4, 'car_68ca7acba88e35.44865794.jpg'),
(18, 4, 'car_68ca7acba90e64.56297967.jpg'),
(19, 4, 'car_68ca7acba9a3d8.02191426.jpg'),
(20, 4, 'car_68ca7acba9f6e7.85214943.jpg'),
(21, 4, 'car_68ca7acbaa5ca2.29376950.jpg'),
(27, 6, 'car_68cb04b4650987.67553100.jpg'),
(28, 6, 'car_68cb04b466b1a3.46525519.jpg'),
(29, 6, 'car_68cb04b466f747.94350876.jpg'),
(30, 6, 'car_68cb04b4674418.60215561.jpg'),
(31, 6, 'car_68cb04b4678ed0.16069363.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `car_reviews`
--

CREATE TABLE `car_reviews` (
  `id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL,
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `car_reviews`
--

INSERT INTO `car_reviews` (`id`, `car_id`, `user_id`, `rating`, `review_text`, `created_at`) VALUES
(1, 1, 1, 4, 'very good car', '2025-09-17 09:31:19'),
(2, 2, 3, 3, 'Pretty good car but that air condition is not that good', '2025-09-17 18:06:44');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `image`) VALUES
(1, 'Luxury', '68ca682a3e3aa.jpg'),
(2, 'Economy', '68ca6856d00f5.jpg'),
(3, 'EV', '68ca68b83ab28.jpg'),
(4, 'SUV', '68ca6913b19c0.jpg'),
(5, 'Saloon', '68ca692f36f9d.webp'),
(6, 'Family Ride', '68ca694d07a5b.avif');

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `user_id`, `company_name`, `address`, `contact_person`) VALUES
(1, 2, 'One Piece', 'Yangon', 'Sanji'),
(2, 6, 'Robin', 'Yangon', 'Robin'),
(3, 5, 'Info', 'Mandalay', 'John');

-- --------------------------------------------------------

--
-- Table structure for table `contact_details`
--

CREATE TABLE `contact_details` (
  `id` int(11) NOT NULL,
  `contact_type` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone_number` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_details`
--

INSERT INTO `contact_details` (`id`, `contact_type`, `email`, `phone_number`, `address`, `status`, `created_at`, `updated_at`) VALUES
(1, 'General Inquiry', 'info@luxdrive.com', '+1-800-123-4567', '123 Luxury Lane, City, State 12345', 'active', '2025-09-10 20:22:47', '2025-09-10 20:22:47'),
(2, 'Customer Support', 'support@luxdrive.com', '+1-800-987-6543', '123 Luxury Lane, City, State 12345', 'active', '2025-09-10 20:22:47', '2025-09-10 20:22:47'),
(10, 'Marketing', 'ceo@luxdrive.com', '+1-800-444-1112', '25 Luxury Lane, City, State 12345', 'active', '2025-09-15 06:33:37', '2025-09-15 06:33:37');

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `license_number` varchar(50) NOT NULL,
  `car_id` int(11) NOT NULL,
  `status` enum('available','busy','on_leave') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `trip_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`id`, `name`, `phone`, `email`, `image`, `license_number`, `car_id`, `status`, `created_at`, `trip_count`) VALUES
(1, 'Naruto', '0789461006', '', '1758109966_driverm1.jpg', 'B/4241441', 4, 'available', '2025-09-17 09:17:39', 0),
(2, 'Sasuke', '0789461006', '', '1758109691_driver.jpg', 'B/4241441', 1, 'available', '2025-09-17 09:18:04', 1),
(3, 'Sakura', '12341613', '', '1758109782_driverfe.jpg', 'B/4241431', 3, 'available', '2025-09-17 09:19:40', 0),
(4, 'Kakashi', '1321536326', '', '1758132711_drivermen2.jpg', 'B/4244142', 2, 'available', '2025-09-17 09:20:16', 2);

-- --------------------------------------------------------

--
-- Table structure for table `faq`
--

CREATE TABLE `faq` (
  `id` int(11) NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faq`
--

INSERT INTO `faq` (`id`, `question`, `answer`, `category`, `status`, `created_at`, `updated_at`) VALUES
(1, 'What information do I need to book a ride?', 'To book a ride, you will need to provide your pickup location, destination, preferred date and time, and the number of passengers.', 'Bookings', 'active', '2025-09-10 20:15:02', '2025-09-10 20:18:47'),
(2, 'How far in advance should I book my ride?', 'We recommend booking at least 24 hours in advance to ensure availability, especially for high-demand periods or special events.', 'Requirements', 'active', '2025-09-10 20:15:02', '2025-09-10 20:15:02'),
(3, 'What payment methods are accepted?', 'We accept major credit cards, including Visa, MasterCard, and American Express, as well as several popular E-wallets and banking systems for secure transactions.', 'Payment', 'active', '2025-09-10 20:15:02', '2025-09-10 20:15:02'),
(4, 'Can I change or cancel my booking?', 'Yes, you can modify or cancel your booking. A full refund is available if you cancel at before your scheduled pickup time. But you cannot cancel after booking accepted', 'Bookings', 'active', '2025-09-10 20:15:02', '2025-09-10 20:15:02'),
(5, 'What if my flight is delayed?', 'Our drivers track flight information in real-time. We will adjust the pickup time accordingly, so there is no need to worry if your flight is delayed. There are no additional waiting charges for flight delays.', 'Travel', 'active', '2025-09-10 20:15:02', '2025-09-10 20:15:02'),
(6, 'Is an upfront deposit required?', 'No, a security deposit is not required. The full payment is processed at the time of booking to confirm your reservation.', 'Payment', 'active', '2025-09-10 20:15:02', '2025-09-10 20:15:02'),
(7, 'Can I request multiple stops during my trip?', 'Yes, you can request multiple stops. Please provide the details of each stop when you make your booking so we can calculate the total fare accurately.', 'Trip Details', 'active', '2025-09-10 20:15:02', '2025-09-10 20:15:02'),
(8, 'Can I request a specific type of vehicle?', 'Yes, you can choose from a range of vehicles, including sedans, SUVs, and luxury cars, to meet your travel needs. Simply select your preferred vehicle during the booking process.', 'Trip Details', 'active', '2025-09-10 20:15:02', '2025-09-10 20:15:02');

-- --------------------------------------------------------

--
-- Table structure for table `logo`
--

CREATE TABLE `logo` (
  `name` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logo`
--

INSERT INTO `logo` (`name`, `image`) VALUES
('Lux Drive', 'homepageimg/logo.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `payment_method` enum('credit_card','paypal','cash','bank_transfer') DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('paid','pending','failed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `payment_method`, `amount`, `payment_date`, `status`) VALUES
(1, 1, 'cash', 928.80, '2025-09-17 09:35:25', 'paid'),
(2, 2, 'cash', 294.30, '2025-09-17 17:29:42', 'paid'),
(3, 3, 'cash', 1275.30, '2025-09-17 17:56:14', 'paid');

-- --------------------------------------------------------

--
-- Table structure for table `places`
--

CREATE TABLE `places` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category` enum('Kachin','Kayah','Kayin','Chin','Mon','Rakhine','Shan','Yangon','Mandalay','Sagaing','Bago','Tanintharyi','Ayeyarwady','Magway') NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `places`
--

INSERT INTO `places` (`id`, `name`, `description`, `image`, `created_at`, `category`, `latitude`, `longitude`) VALUES
(1, 'Shwedagon Pagoda', 'The Shwedagon is the most sacred Buddhist pagoda in Myanmar, as it is believed to contain relics of the four previous Buddhas of the present kalpa.', NULL, '2025-09-10 07:16:23', 'Yangon', 16.79840000, 96.14960000),
(2, 'Kyaiktiyo Pagoda', 'Mount Kyaiktiyo (Kyite Htee Yoe), famous for the huge golden rock perched at its summit, is one of the three most sacred religious sites in Myanmar, along with the Shwedagon Pagoda and the Mahamuni Temple.', NULL, '2025-09-10 07:46:18', 'Mon', 17.48167600, 97.09818300),
(3, 'Shwezigon Pagoda', 'The royal Shwezigon Pagoda or Shwezigon Paya is a Buddhist stupa located in Nyaung-U, Myanmar. A prototype of Burmese stupas, it consists of a circular gold leaf-gilded stupa surrounded by smaller temples and shrines.', NULL, '2025-09-10 08:23:58', 'Mandalay', 21.19530567, 94.89419000),
(4, 'Phaung Daw Oo Pagoda', 'The Phaung Daw Oo Pagoda is a major attraction in the Inle Lake region. It houses five gilded Buddha images, which are almost unrecognizable as they have been covered with gold leaves by devout Buddhists. ', NULL, '2025-09-10 08:25:50', 'Shan', 20.47444000, 96.89028000),
(5, 'Mrauk U ', 'Mrauk U in Rakhine State, Myanmar, is an ancient city known for its impressive stone pagodas and temples, which are the remnants of the powerful Mrauk U Kingdom. ', NULL, '2025-09-10 08:29:15', 'Rakhine', 20.59352400, 93.19303000),
(6, 'Ngwe Saung', 'Ngwesaung, also spelt Ngwe Hsaung, is a beach resort located 48 km west of Pathein, Ayeyarwady Region, Myanmar. It is the namesake of Ngwesaung Subtownship, Pathein Township. In 2014, the town of Ngwesaung had 10,732 people.', NULL, '2025-09-18 16:33:22', 'Ayeyarwady', 16.86850000, 94.38480000);

-- --------------------------------------------------------

--
-- Table structure for table `place_images`
--

CREATE TABLE `place_images` (
  `id` int(11) NOT NULL,
  `place_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `place_images`
--

INSERT INTO `place_images` (`id`, `place_id`, `image`) VALUES
(1, 1, '68c125c751014_Shwedagon.jpg'),
(2, 2, '68c12cca7f44e_Kyaiktiyo-Pagoda-Golden-rock.jpg'),
(3, 3, '68c1359ed8ec8_Shwezigon-Pagoda-1.jpg'),
(4, 4, '68c1360ecf7e8_Hpaung-Daw-U-Pagoda-near-Inle-Lake.webp'),
(5, 5, '68c136db4f19a_Mrauk Oo Pagoda.jpg'),
(6, 6, '68cc345224ebd_ngweSaung.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `rewards`
--

CREATE TABLE `rewards` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `points` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rewards`
--

INSERT INTO `rewards` (`id`, `user_id`, `points`, `created_at`, `updated_at`) VALUES
(1, 3, 13696, '2025-09-17 18:00:29', '2025-09-17 19:41:29'),
(2, 1, 9288, '2025-09-17 18:00:31', '2025-09-17 18:00:31');

-- --------------------------------------------------------

--
-- Table structure for table `terms_and_conditions`
--

CREATE TABLE `terms_and_conditions` (
  `id` int(11) NOT NULL,
  `version_number` varchar(50) NOT NULL,
  `effective_date` date NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `terms_and_conditions`
--

INSERT INTO `terms_and_conditions` (`id`, `version_number`, `effective_date`, `content`, `created_at`) VALUES
(5, '1.4', '2025-09-14', 'Welcome to Lux Drive. By using our platform, you agree to these Terms and Conditions.\r\n1. Service Description: Our B2B2C platform connects car rental companies with customers.\r\n2. Accounts: You are responsible for all activity under your account. Keep your login details confidential.\r\n3. Bookings and Payments: Bookings are subject to the rental company\'s policies. Lux Drive processes payments and charges a commission on each successful booking.\r\n4. Conduct: You agree to use the platform lawfully and without fraudulent intent.\r\n5. Intellectual Property: All content on Lux Drive, including logos and images, is our property.\r\n6. Liability: Lux Drive is not liable for damages or losses from platform use. Our responsibility is limited to providing the platform itself.\r\n7. Changes to Terms: We may update these Terms at any time and will notify you of changes by posting them on this page.\r\n8. Contact: For any questions, please contact us directly.\r\n', '2025-09-14 11:43:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `role` enum('customer','admin','company') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `otp` varchar(6) DEFAULT NULL,
  `otp_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `latitude`, `longitude`, `gender`, `birthdate`, `profile_picture`, `role`, `created_at`, `otp`, `otp_expires_at`) VALUES
(1, 'Okkar Ko Ko', 'okkarkoko222@gmail.com', '$2y$10$CK3GBnAWwLPt2qc0zWCIO.BkIUws65CRQLidtqgXoqhvHuQ6NBf0m', '0789461006', 'kayta, Yangon, Yangon', 16.78547774, 96.20143717, 'male', '2007-08-09', 'uploads/profile_pics/1758094602_tv-smoke-peaky-blinders-cillian-murphy-wallpaper-preview.jpg', 'admin', '2025-09-17 07:36:42', '312690', '2025-09-18 06:29:44'),
(2, 'Vinsmoke Sanji', 'sanji222111@gmail.com', '$2y$10$4EAek4CJ.ZL2XuSsxagKVe0xuogIHf7pMWgptvQd/C2sAC3le5J16', '09381461992', '12, Yangon, Yangon', 16.82902479, 96.18821945, 'male', '2002-09-20', 'uploads/profile_pics/1758095001_download.jpg', 'company', '2025-09-17 07:43:21', NULL, NULL),
(3, 'Roronora Zoro', 'zoro222@gmail.com', '$2y$10$Z6O6o/BNcJ0cVHKib/ccOeM//4MYcB4JwnkyznU.YdUs2kgT1roQi', '371957154', '32, Yangon, Yangon', 16.82228798, 96.18615973, 'male', '2001-12-23', 'uploads/profile_pic/1758128090_zoro.jpg', 'customer', '2025-09-17 07:46:30', NULL, NULL),
(4, 'Monkey D Luffy', 'luffy222@gmail.com', '$2y$10$iKf0ee.RDA7PgR5tm5iXt.C22KgtmrhkaxlxwjwGHgqjgO4xRBzt6', '313124124', '23, Yangon, Yangon', 16.82968203, 96.19302548, 'male', '2005-12-23', NULL, 'customer', '2025-09-17 07:59:09', NULL, NULL),
(5, 'John Jackson', 'john222@gmail.com', '$2y$10$2.HILNlFlcnsvX5T.bmZBOb8GQ9Pl2XZXLe/lwTvz5cSCKQj8UaVe', '3123124', '1, Yangon, Yangon', 16.83576137, 96.19268219, 'male', '2001-02-01', 'uploads/profile_pics/1758096246_pxfuel (2).jpg', 'company', '2025-09-17 08:04:06', NULL, NULL),
(6, 'Nico Robin', 'robin222@gmail.com', '$2y$10$CNiHd/wpyr5oKFU5ksbBgOSP/sSndrh/FdOu3dwuGBKAIJgUCYPFK', '2413415132', '51, Yangon, Yangon', 16.82918910, 96.19096575, 'female', '1998-02-12', 'uploads/profile_pics/1758097344_pxfuel (2).jpg', 'company', '2025-09-17 08:22:24', NULL, NULL),
(7, 'FingerLand Shank', 'shank222@gmail.com', '$2y$10$ZNs8gQKsL73N0jcavtrH1uiiIPeC57eKd9t6oTZo7E8a0T5A/77YC', '24142424', '26, Yangon, Yangon', 16.83001065, 96.16659052, 'male', '1989-12-02', 'uploads/profile_pics/1758141313_shank.jpg', 'customer', '2025-09-17 20:35:13', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_coupons`
--

CREATE TABLE `user_coupons` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `coupon_value` decimal(10,2) NOT NULL,
  `points_exchanged` int(11) NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_coupons`
--

INSERT INTO `user_coupons` (`id`, `user_id`, `coupon_value`, `points_exchanged`, `is_used`, `created_at`) VALUES
(1, 3, 220.00, 2000, 0, '2025-09-17 18:02:37');

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `user_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notified_status` enum('not_notified','notified') DEFAULT 'not_notified'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlists`
--

INSERT INTO `wishlists` (`user_id`, `car_id`, `created_at`, `notified_status`) VALUES
(1, 1, '2025-09-17 19:00:24', 'notified'),
(1, 4, '2025-09-17 19:00:12', 'notified');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `car_id` (`car_id`),
  ADD KEY `bookings_ibfk_1` (`user_id`),
  ADD KEY `fk_booking_coupon` (`coupon_id`);

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `car_categories`
--
ALTER TABLE `car_categories`
  ADD PRIMARY KEY (`car_id`,`category_id`),
  ADD KEY `fk_category` (`category_id`);

--
-- Indexes for table `car_features`
--
ALTER TABLE `car_features`
  ADD PRIMARY KEY (`id`),
  ADD KEY `car_id` (`car_id`);

--
-- Indexes for table `car_images`
--
ALTER TABLE `car_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `car_id` (`car_id`);

--
-- Indexes for table `car_reviews`
--
ALTER TABLE `car_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `car_id` (`car_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `contact_details`
--
ALTER TABLE `contact_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `car_id` (`car_id`);

--
-- Indexes for table `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logo`
--
ALTER TABLE `logo`
  ADD PRIMARY KEY (`name`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `places`
--
ALTER TABLE `places`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `place_images`
--
ALTER TABLE `place_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `place_id` (`place_id`);

--
-- Indexes for table `rewards`
--
ALTER TABLE `rewards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `terms_and_conditions`
--
ALTER TABLE `terms_and_conditions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `version_number` (`version_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_coupons`
--
ALTER TABLE `user_coupons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`user_id`,`car_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `car_id` (`car_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `car_features`
--
ALTER TABLE `car_features`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `car_images`
--
ALTER TABLE `car_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `car_reviews`
--
ALTER TABLE `car_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `contact_details`
--
ALTER TABLE `contact_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `faq`
--
ALTER TABLE `faq`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `places`
--
ALTER TABLE `places`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `place_images`
--
ALTER TABLE `place_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `rewards`
--
ALTER TABLE `rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `terms_and_conditions`
--
ALTER TABLE `terms_and_conditions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_coupons`
--
ALTER TABLE `user_coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`),
  ADD CONSTRAINT `fk_booking_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `user_coupons` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cars`
--
ALTER TABLE `cars`
  ADD CONSTRAINT `cars_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `cars_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`);

--
-- Constraints for table `car_categories`
--
ALTER TABLE `car_categories`
  ADD CONSTRAINT `fk_car` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `car_features`
--
ALTER TABLE `car_features`
  ADD CONSTRAINT `car_features_ibfk_1` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `car_images`
--
ALTER TABLE `car_images`
  ADD CONSTRAINT `car_images_ibfk_1` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `car_reviews`
--
ALTER TABLE `car_reviews`
  ADD CONSTRAINT `fk_car_reviews_car_id` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_car_reviews_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `drivers`
--
ALTER TABLE `drivers`
  ADD CONSTRAINT `fk_drivers_car_id` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);

--
-- Constraints for table `place_images`
--
ALTER TABLE `place_images`
  ADD CONSTRAINT `place_images_ibfk_1` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rewards`
--
ALTER TABLE `rewards`
  ADD CONSTRAINT `fk_rewards_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_coupons`
--
ALTER TABLE `user_coupons`
  ADD CONSTRAINT `user_coupons_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `fk_wishlist_car_id` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_wishlist_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
