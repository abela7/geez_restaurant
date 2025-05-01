-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 01, 2025 at 03:37 PM
-- Server version: 10.11.11-MariaDB-ubu2204
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `admi_geez_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `food_waste_log`
--

CREATE TABLE `food_waste_log` (
  `waste_id` int(11) NOT NULL,
  `food_item` varchar(100) NOT NULL,
  `waste_type` varchar(50) NOT NULL,
  `reason` varchar(100) NOT NULL,
  `weight_kg` decimal(8,2) NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `waste_date` date NOT NULL,
  `action_taken` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `recorded_by_user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_waste_log`
--

INSERT INTO `food_waste_log` (`waste_id`, `food_item`, `waste_type`, `reason`, `weight_kg`, `cost`, `waste_date`, `action_taken`, `notes`, `recorded_by_user_id`, `created_at`, `updated_at`) VALUES
(1, 'Shiro Powder', 'Service', 'Customer Return', 1.26, 12.59, '2023-08-21', 'Reviewed preparation process and adjusted seasoning', 'Platter returned due to dietary restrictions not accommodated', 8, '2023-08-21 14:40:17', '2023-08-21 14:40:17'),
(2, 'Injera Firfir', 'Preparation', 'Overproduction', 1.98, 19.78, '2023-08-22', 'Discarded according to waste protocol', 'Too many combination platters prepped for quiet evening', 8, '2023-08-22 18:33:50', '2023-08-22 18:33:50'),
(3, 'Injera', 'Storage', 'Overproduction', 0.79, 3.15, '2023-08-28', 'Staff meal', 'Excess prepared for anticipated large group that canceled', 8, '2023-08-28 14:04:56', '2023-08-28 14:04:56'),
(4, 'Berbere Spice', 'Service', 'Customer Return', 0.79, 6.72, '2023-09-03', 'Reviewed preparation process and adjusted seasoning', 'Doro Wot returned for being undercooked', 8, '2023-09-03 12:57:33', '2023-09-03 12:57:33'),
(5, 'Misir Wot', 'Service', 'Quality Control', 1.26, 12.59, '2023-09-23', 'Updated storage procedures', 'Injera texture not meeting standards', 6, '2023-09-23 22:52:53', '2023-09-23 22:52:53'),
(6, 'Gomen', 'Service', 'Overproduction', 0.85, 7.64, '2023-09-27', 'Staff meal', 'Excess prepared for slower than expected service', 5, '2023-09-27 12:30:01', '2023-09-27 12:30:01'),
(7, 'Fish Dulet', 'Storage', 'Spoiled', 1.02, 13.25, '2023-09-29', 'Discarded according to waste protocol', 'Off odor detected in prepared stew', 5, '2023-09-29 09:05:17', '2023-09-29 09:05:17'),
(8, 'Lamb Tibs', 'Storage', 'Contamination', 0.23, 3.45, '2023-10-11', 'Deep cleaned affected area and re-trained staff', 'Foreign object found during final inspection', 5, '2023-10-11 15:06:01', '2023-10-11 15:06:01'),
(9, 'Misir Wot', 'Service', 'Overproduction', 1.84, 18.38, '2023-11-01', 'Staff meal', 'Excess prepared for anticipated large group that canceled', 8, '2023-11-01 11:55:24', '2023-11-01 11:55:24'),
(10, 'Gomen', 'Preparation', 'Overproduction', 0.42, 3.78, '2023-11-03', 'Discarded according to waste protocol', 'Too many combination platters prepped for quiet evening', 5, '2023-11-03 10:22:04', '2023-11-03 10:22:04'),
(11, 'Lamb Tibs', 'Preparation', 'Overproduction', 0.35, 5.25, '2023-11-11', 'Staff meal', 'Excess prepared for anticipated large group that canceled', 6, '2023-11-11 18:52:25', '2023-11-11 18:52:25'),
(12, 'Vegetarian Combo', 'Preparation', 'Overproduction', 0.65, 22.74, '2023-11-23', 'Staff meal', 'Excess prepared for anticipated large group that canceled', 6, '2023-11-23 18:34:39', '2023-11-23 18:34:39'),
(13, 'Mahberawi', 'Preparation', 'Overproduction', 0.47, 21.15, '2023-12-02', 'Staff meal', 'Excess prepared for anticipated large group that canceled', 8, '2023-12-02 13:10:44', '2023-12-02 13:10:44'),
(14, 'Zilzil Tibs', 'Storage', 'Spoiled', 0.23, 3.45, '2023-12-04', 'Discarded according to waste protocol', 'Off odor detected in prepared stew', 5, '2023-12-04 09:27:42', '2023-12-04 09:27:42'),
(15, 'Vegetarian Combo', 'Service', 'Customer Return', 1.28, 44.79, '2023-12-12', 'Reviewed preparation process and adjusted seasoning', 'Customer claimed Kitfo was not fresh', 8, '2023-12-12 17:11:02', '2023-12-12 17:11:02'),
(16, 'Kitfo', 'Preparation', 'Spoiled', 0.64, 9.59, '2023-12-27', 'Discarded according to waste protocol', 'Visual inspection showed signs of spoilage', 6, '2023-12-27 21:51:37', '2023-12-27 21:51:37'),
(17, 'Awaze Tibs', 'Storage', 'Overproduction', 1.72, 24.06, '2023-12-29', 'Discarded according to waste protocol', 'Excess prepared for slower than expected service', 5, '2023-12-29 19:16:04', '2023-12-29 19:16:04'),
(18, 'Sambusa', 'Service', 'Customer Return', 0.21, 1.26, '2024-02-05', 'Reviewed preparation process and adjusted seasoning', 'Incorrect dish served and returned untouched', 8, '2024-02-05 19:29:09', '2024-02-05 19:29:09'),
(19, 'Lamb Tibs', 'Service', 'Expired', 1.50, 22.49, '2024-02-09', 'Discarded according to waste protocol', 'Prepped ingredients stored too long', 5, '2024-02-09 18:18:34', '2024-02-09 18:18:34'),
(20, 'Injera', 'Preparation', 'Quality Control', 1.41, 5.63, '2024-02-17', 'Updated storage procedures', 'Stew viscosity too thin for service standards', 5, '2024-02-17 19:44:15', '2024-02-17 19:44:15'),
(21, 'Kocho', 'Service', 'Customer Return', 0.51, 6.11, '2024-03-05', 'Reviewed preparation process and adjusted seasoning', 'Incorrect dish served and returned untouched', 8, '2024-03-05 15:24:39', '2024-03-05 15:24:39'),
(22, 'Kategna', 'Storage', 'Overproduction', 0.82, 5.73, '2024-03-09', 'Staff meal', 'Excess prepared for slower than expected service', 5, '2024-03-09 22:54:48', '2024-03-09 22:54:48'),
(23, 'Zilzil Tibs', 'Storage', 'Quality Control', 1.22, 18.29, '2024-03-16', 'Updated storage procedures', 'Injera texture not meeting standards', 6, '2024-03-16 08:46:58', '2024-03-16 08:46:58'),
(24, 'Kik Alicha', 'Service', 'Customer Return', 0.30, 3.00, '2024-03-24', 'Reviewed preparation process and adjusted seasoning', 'Customer reported Awaze Tibs too spicy', 8, '2024-03-24 21:09:27', '2024-03-24 21:09:27'),
(25, 'Berbere Spice', 'Service', 'Customer Return', 0.88, 7.48, '2024-04-03', 'Reviewed preparation process and adjusted seasoning', 'Customer reported Awaze Tibs too spicy', 8, '2024-04-03 09:30:21', '2024-04-03 09:30:21'),
(26, 'Beyaynetu', 'Service', 'Overproduction', 1.50, 17.99, '2024-04-03', 'Staff meal', 'Excess prepared for slower than expected service', 6, '2024-04-03 18:52:14', '2024-04-03 18:52:14'),
(27, 'Injera', 'Service', 'Customer Return', 1.78, 7.10, '2024-04-08', 'Reviewed preparation process and adjusted seasoning', 'Doro Wot returned for being undercooked', 6, '2024-04-08 21:12:33', '2024-04-08 21:12:33'),
(28, 'Fosolia', 'Service', 'Customer Return', 1.13, 10.16, '2024-04-09', 'Reviewed preparation process and adjusted seasoning', 'Incorrect dish served and returned untouched', 8, '2024-04-09 15:01:00', '2024-04-09 15:01:00'),
(29, 'Azifa', 'Storage', 'Expired', 1.61, 11.25, '2024-04-16', 'Discarded according to waste protocol', 'Item past use-by date during inventory check', 5, '2024-04-16 21:09:42', '2024-04-16 21:09:42'),
(30, 'Gomen', 'Service', 'Customer Return', 1.34, 12.05, '2024-05-19', 'Reviewed preparation process and adjusted seasoning', 'Customer claimed Kitfo was not fresh', 6, '2024-05-19 12:42:15', '2024-05-19 12:42:15'),
(31, 'Ge\'ez Special', 'Preparation', 'Overproduction', 0.49, 20.58, '2024-05-21', 'Staff meal', 'Excess prepared for slower than expected service', 5, '2024-05-21 21:17:56', '2024-05-21 21:17:56'),
(32, 'Zilzil Tibs', 'Storage', 'Spoiled', 1.66, 24.88, '2024-05-23', 'Discarded according to waste protocol', 'Raw meat showed discoloration', 8, '2024-05-23 20:00:19', '2024-05-23 20:00:19'),
(33, 'Awaze Sauce', 'Preparation', 'Spoiled', 0.93, 5.57, '2024-05-30', 'Discarded according to waste protocol', 'Off odor detected in prepared stew', 6, '2024-05-30 19:54:29', '2024-05-30 19:54:29'),
(34, 'Yefsik Mahberawi', 'Service', 'Overproduction', 0.40, 22.00, '2024-06-08', 'Staff meal', 'Too many combination platters prepped for quiet evening', 8, '2024-06-08 14:45:02', '2024-06-08 14:45:02'),
(35, 'Fish Dulet', 'Storage', 'Preparation Error', 0.48, 6.24, '2024-06-22', 'Re-trained staff on proper preparation techniques', 'Injera too thin and tore during cooking', 5, '2024-06-22 16:40:02', '2024-06-22 16:40:02'),
(36, 'Special Tibs', 'Service', 'Customer Return', 1.65, 26.38, '2024-06-30', 'Reviewed preparation process and adjusted seasoning', 'Customer reported Awaze Tibs too spicy', 5, '2024-06-30 19:57:25', '2024-06-30 19:57:25'),
(37, 'Atkilt Wot', 'Service', 'Customer Return', 1.67, 16.68, '2024-07-06', 'Reviewed preparation process and adjusted seasoning', 'Customer claimed Kitfo was not fresh', 8, '2024-07-06 20:11:25', '2024-07-06 20:11:25'),
(38, 'Ge\'ez Special', 'Storage', 'Contamination', 1.37, 57.53, '2024-07-08', 'Deep cleaned affected area and re-trained staff', 'Allergen contamination in vegetarian dish preparation', 8, '2024-07-08 16:50:01', '2024-07-08 16:50:01'),
(39, 'Shiro Powder', 'Service', 'Expired', 0.41, 4.10, '2024-07-20', 'Discarded according to waste protocol', 'Item past use-by date during inventory check', 6, '2024-07-20 13:36:12', '2024-07-20 13:36:12'),
(40, 'Berbere Spice', 'Service', 'Customer Return', 0.94, 7.99, '2024-08-12', 'Reviewed preparation process and adjusted seasoning', 'Customer reported Awaze Tibs too spicy', 8, '2024-08-12 19:41:22', '2024-08-12 19:41:22'),
(41, 'Sambusa', 'Service', 'Overproduction', 0.87, 5.21, '2024-08-13', 'Staff meal', 'Excess prepared for slower than expected service', 5, '2024-08-13 15:50:02', '2024-08-13 15:50:02'),
(42, 'Mahberawi', 'Service', 'Customer Return', 0.92, 41.39, '2024-08-24', 'Reviewed preparation process and adjusted seasoning', 'Customer reported Awaze Tibs too spicy', 8, '2024-08-24 17:19:54', '2024-08-24 17:19:54'),
(43, 'Azifa', 'Preparation', 'Expired', 1.02, 7.13, '2024-09-18', 'Discarded according to waste protocol', 'Item past use-by date during inventory check', 5, '2024-09-18 14:46:32', '2024-09-18 14:46:32'),
(44, 'Misir Wot', 'Preparation', 'Expired', 0.15, 1.50, '2024-09-18', 'Discarded according to waste protocol', 'Prepped ingredients stored too long', 5, '2024-09-18 09:52:03', '2024-09-18 09:52:03'),
(45, 'Kitfo', 'Storage', 'Quality Control', 0.56, 8.39, '2024-10-04', 'Updated storage procedures', 'Berbere batch inconsistent with house flavor profile', 6, '2024-10-04 14:59:17', '2024-10-04 14:59:17'),
(46, 'Misir Wot', 'Service', 'Customer Return', 0.60, 5.99, '2024-10-05', 'Reviewed preparation process and adjusted seasoning', 'Doro Wot returned for being undercooked', 5, '2024-10-05 13:28:17', '2024-10-05 13:28:17'),
(47, 'Beyaynetu', 'Preparation', 'Overproduction', 0.60, 7.19, '2024-10-07', 'Discarded according to waste protocol', 'Excess prepared for slower than expected service', 5, '2024-10-07 10:01:00', '2024-10-07 10:01:00'),
(48, 'Mitmita Spice', 'Preparation', 'Spoiled', 1.38, 11.73, '2024-10-15', 'Discarded according to waste protocol', 'Off odor detected in prepared stew', 8, '2024-10-15 18:28:00', '2024-10-15 18:28:00'),
(49, 'Awaze Sauce', 'Service', 'Quality Control', 1.22, 7.31, '2024-10-15', 'Updated storage procedures', 'Stew viscosity too thin for service standards', 5, '2024-10-15 08:32:47', '2024-10-15 08:32:47'),
(50, 'Awaze Tibs', 'Service', 'Customer Return', 1.61, 22.52, '2024-11-13', 'Reviewed preparation process and adjusted seasoning', 'Doro Wot returned for being undercooked', 8, '2024-11-13 19:38:24', '2024-11-13 19:38:24'),
(51, 'Rice with Beef', 'Service', 'Customer Return', 0.57, 7.40, '2024-11-25', 'Reviewed preparation process and adjusted seasoning', 'Incorrect dish served and returned untouched', 6, '2024-11-25 13:35:18', '2024-11-25 13:35:18'),
(52, 'Azifa', 'Storage', 'Spoiled', 1.74, 12.16, '2024-11-30', 'Discarded according to waste protocol', 'Visual inspection showed signs of spoilage', 5, '2024-11-30 21:58:55', '2024-11-30 21:58:55'),
(53, 'Fosolia', 'Storage', 'Quality Control', 0.43, 3.87, '2024-12-07', 'Updated storage procedures', 'Berbere batch inconsistent with house flavor profile', 5, '2024-12-07 11:51:05', '2024-12-07 11:51:05'),
(54, 'Shiro', 'Storage', 'Preparation Error', 0.40, 4.40, '2024-12-16', 'Re-trained staff on proper preparation techniques', 'Berbere added in excess to stew', 8, '2024-12-16 12:26:50', '2024-12-16 12:26:50'),
(55, 'Kitfo', 'Storage', 'Overproduction', 1.97, 29.53, '2024-12-20', 'Staff meal', 'Excess prepared for anticipated large group that canceled', 5, '2024-12-20 13:31:59', '2024-12-20 13:31:59'),
(56, 'Misir Wot', 'Service', 'Customer Return', 1.74, 17.38, '2025-01-05', 'Reviewed preparation process and adjusted seasoning', 'Customer claimed Kitfo was not fresh', 5, '2025-01-05 13:30:26', '2025-01-05 13:30:26'),
(57, 'Kitfo', 'Service', 'Customer Return', 1.27, 19.04, '2025-01-12', 'Reviewed preparation process and adjusted seasoning', 'Platter returned due to dietary restrictions not accommodated', 6, '2025-01-12 10:35:27', '2025-01-12 10:35:27'),
(58, 'Lamb Tibs', 'Service', 'Preparation Error', 0.96, 14.39, '2025-01-15', 'Re-trained staff on proper preparation techniques', 'Kitfo over-seasoned with mitmita', 5, '2025-01-15 16:30:48', '2025-01-15 16:30:48'),
(59, 'Gomen', 'Service', 'Customer Return', 0.36, 3.24, '2025-01-19', 'Reviewed preparation process and adjusted seasoning', 'Customer reported Awaze Tibs too spicy', 8, '2025-01-19 21:28:45', '2025-01-19 21:28:45'),
(60, 'Yefsik Mahberawi', 'Service', 'Customer Return', 0.82, 45.09, '2025-01-27', 'Reviewed preparation process and adjusted seasoning', 'Customer claimed Kitfo was not fresh', 6, '2025-01-27 15:29:16', '2025-01-27 15:29:16'),
(61, 'Kik Alicha', 'Preparation', 'Overproduction', 1.61, 16.08, '2025-02-03', 'Staff meal', 'Excess prepared for anticipated large group that canceled', 6, '2025-02-03 09:07:24', '2025-02-03 09:07:24'),
(62, 'Vegetarian Combo', 'Service', 'Customer Return', 0.42, 14.70, '2025-02-28', 'Reviewed preparation process and adjusted seasoning', 'Customer reported Awaze Tibs too spicy', 8, '2025-02-28 10:41:34', '2025-02-28 10:41:34'),
(63, 'Lamb Tibs', 'Preparation', 'Overproduction', 1.92, 28.78, '2025-03-12', 'Discarded according to waste protocol', 'Excess prepared for slower than expected service', 8, '2025-03-12 21:43:43', '2025-03-12 21:43:43'),
(64, 'Niter Kibbeh', 'Storage', 'Spoiled', 1.03, 7.73, '2025-03-30', 'Discarded according to waste protocol', 'Off odor detected in prepared stew', 8, '2025-03-30 12:20:30', '2025-03-30 12:20:30'),
(65, 'Awaze Sauce', 'Preparation', 'Spoiled', 1.27, 7.61, '2025-04-01', 'Discarded according to waste protocol', 'Off odor detected in prepared stew', 8, '2025-04-01 08:19:08', '2025-04-01 08:19:08'),
(66, 'Beyaynetu', 'Service', 'Customer Return', 0.17, 2.04, '2025-04-06', 'Reviewed preparation process and adjusted seasoning', 'Platter returned due to dietary restrictions not accommodated', 8, '2025-04-06 18:43:21', '2025-04-06 18:43:21'),
(67, 'Ayib', 'Preparation', 'Quality Control', 1.26, 6.29, '2025-04-11', 'Updated storage procedures', 'Stew viscosity too thin for service standards', 6, '2025-04-11 09:07:41', '2025-04-11 09:07:41'),
(68, 'Dulet', 'Preparation', 'Overproduction', 0.77, 10.77, '2025-04-17', 'Discarded according to waste protocol', 'Too many combination platters prepped for quiet evening', 8, '2025-04-17 11:41:35', '2025-04-17 11:41:35'),
(69, 'Mitmita Spice', 'Service', 'Customer Return', 0.38, 3.23, '2025-04-25', 'Reviewed preparation process and adjusted seasoning', 'Customer claimed Kitfo was not fresh', 8, '2025-04-25 20:44:34', '2025-04-25 20:44:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `food_waste_log`
--
ALTER TABLE `food_waste_log`
  ADD PRIMARY KEY (`waste_id`),
  ADD KEY `recorded_by_user_id` (`recorded_by_user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `food_waste_log`
--
ALTER TABLE `food_waste_log`
  MODIFY `waste_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `food_waste_log`
--
ALTER TABLE `food_waste_log`
  ADD CONSTRAINT `food_waste_log_ibfk_1` FOREIGN KEY (`recorded_by_user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
