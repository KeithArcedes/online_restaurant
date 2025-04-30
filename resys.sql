-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 30, 2025 at 07:28 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `resys`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `customer_id`, `menu_id`, `quantity`) VALUES
(13, 5, 13, 1),
(14, 5, 15, 3),
(15, 5, 14, 1),
(21, 6, 15, 1),
(22, 6, 13, 1),
(23, 6, 14, 1),
(24, 1, 15, 1),
(25, 1, 13, 2),
(26, 1, 14, 1);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `user_id`, `first_name`, `last_name`) VALUES
(1, 1, 'Keith', 'Arcedes'),
(2, 2, 'Joshua', 'Lacsi'),
(3, 3, 'super', 'admin'),
(4, 4, 'Sherwin', 'Pagalilawan'),
(5, 5, 'Amiel', 'Diega'),
(6, 6, 'john', 'doe');

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `menu_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `avail` enum('Available','NotAvailable','','') NOT NULL,
  `category` enum('sizzling','beverage','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`menu_id`, `name`, `description`, `price`, `image_url`, `avail`, `category`) VALUES
(13, 'tapsilog', 'beef and egg', 80.00, '../uploads/menu/6811a4b60eda7_681124a35b529_tapsilog.jpg', 'Available', 'sizzling'),
(14, 'mango beverage', 'fresh mango drink', 50.00, '../uploads/menu/6811bc398de68_images.jpg', 'Available', 'beverage'),
(15, 'hotsilog', 'hotdog and egg', 65.50, '../uploads/menu/68124bb84a376_download.jpg', 'Available', 'sizzling');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `sent_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `subtotal` double NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `order_date` datetime DEFAULT current_timestamp(),
  `delivery_address` varchar(255) NOT NULL,
  `contact_number` varchar(50) NOT NULL,
  `special_instructions` varchar(100) NOT NULL,
  `payment_method` enum('paymongo','cod','','') NOT NULL,
  `delivery_fee` double NOT NULL,
  `tax` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `total_amount`, `subtotal`, `status`, `order_date`, `delivery_address`, `contact_number`, `special_instructions`, `payment_method`, `delivery_fee`, `tax`) VALUES
(1, 1, 50.00, 0, 'cancelled', '2025-04-30 14:16:31', '', '', '', 'paymongo', 0, 0),
(2, 1, 50.00, 0, 'cancelled', '2025-04-30 14:17:16', '', '', '', 'paymongo', 0, 0),
(7, 1, 509.20, 410, 'cancelled', '2025-04-30 21:37:28', 'Hda.Mimi', '09090967334', 'asdasd', 'cod', 50, 49.199999999999996),
(8, 1, 509.20, 410, 'completed', '2025-04-30 21:44:00', 'St. Martin Phase 1', '09090967334', 'asdasd', 'cod', 50, 49.199999999999996),
(9, 1, 106.00, 50, 'confirmed', '2025-04-30 21:45:18', 'Hda.Mimi', '09090967334', 'asd', 'paymongo', 50, 6),
(11, 1, 218.00, 150, 'completed', '2025-04-30 21:49:31', 'Hda.Mimi', '09090967334', 'aa', 'cod', 50, 18),
(12, 5, 268.96, 195.5, 'confirmed', '2025-05-01 00:20:35', 'Hda.Mimi', '09090967334', 'asd', 'paymongo', 50, 23.46),
(14, 5, 415.68, 326.5, 'pending', '2025-05-01 00:21:45', 'Hda.Mimi', '09090967334', 'sda', 'cod', 50, 39.18),
(15, 6, 268.96, 195.5, 'completed', '2025-05-01 01:08:02', 'Hda.Mimi', '09090967334', 'asdasd', 'cod', 50, 23.46),
(16, 1, 358.56, 275.5, 'cancelled', '2025-05-01 01:26:07', 'Hda.Mimi', '09090967334', 'asd', 'cod', 50, 33.06);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `item_total` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `menu_id`, `quantity`, `price`, `item_total`) VALUES
(3, 7, 14, 5, 50.00, 250),
(4, 7, 13, 2, 80.00, 160),
(5, 8, 14, 5, 50.00, 250),
(6, 8, 13, 2, 80.00, 160),
(7, 9, 14, 1, 50.00, 50),
(9, 11, 14, 3, 50.00, 150),
(10, 12, 13, 1, 80.00, 80),
(11, 12, 15, 1, 65.50, 65.5),
(12, 12, 14, 1, 50.00, 50),
(16, 14, 13, 1, 80.00, 80),
(17, 14, 15, 3, 65.50, 196.5),
(18, 14, 14, 1, 50.00, 50),
(19, 15, 15, 1, 65.50, 65.5),
(20, 15, 13, 1, 80.00, 80),
(21, 15, 14, 1, 50.00, 50),
(22, 16, 15, 1, 65.50, 65.5),
(23, 16, 13, 2, 80.00, 160),
(24, 16, 14, 1, 50.00, 50);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `amount_paid` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','completed','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `payment_method`, `payment_date`, `amount_paid`, `status`) VALUES
(1, 7, 'cod', '2025-04-30 21:37:28', 0.00, 'pending'),
(2, 8, 'cod', '2025-04-30 21:44:00', 0.00, 'pending'),
(3, 9, 'paymongo', '2025-04-30 21:45:18', 106.00, 'completed'),
(5, 11, 'cod', '2025-04-30 21:49:31', 0.00, 'pending'),
(6, 12, 'paymongo', '2025-05-01 00:20:35', 268.96, 'completed'),
(8, 14, 'cod', '2025-05-01 00:21:45', 0.00, 'pending'),
(9, 15, 'cod', '2025-05-01 01:08:02', 0.00, 'pending'),
(10, 16, 'cod', '2025-05-01 01:26:07', 0.00, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password`, `role`) VALUES
(1, 'keith.arcedes@csav.edu.ph', '$2y$10$eQPDa9XQaHBCVnpAh1j.RuHCcAFlhMWaZmVwaaYXobs25ix4oDIY2', 'customer'),
(2, 'joshualacsi@gmail.com', '$2y$10$neWM5vPkWsC9VimSQTwB8urGNkcHPtPqTo0NCaOijYaeURnj1Ywa6', 'customer'),
(3, 'admin@sample.com', '$2y$10$ANvlr/XaPxnSNpkk5xqhWOlLeoZh85eOf/4Fhi2RLWQPESu2Fwxd.', 'admin'),
(4, 'sherwin@gmail.com', '$2y$10$ec99E1rst8owO6SMm0woDO45DRPcwjVwHMOtBGT6Bf.QTcOdgZ2uu', 'customer'),
(5, 'amiel@gmail.com', '$2y$10$BDFicwDzzrmc3R5LOO0vxe2WBIooRPXJCtOEKHFV4r2x7QBNSz9re', 'customer'),
(6, 'john@gmail.com', '$2y$10$qXk3GOcWvbAeGJadHk/1ZO1mHevqQbc3k8fmsIc9SFzL13zoPr4Bu', 'customer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`menu_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `menu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`menu_id`);

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`menu_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
