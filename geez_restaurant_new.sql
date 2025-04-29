-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 29, 2025 at 07:50 PM
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
-- Table structure for table `cleaning_locations`
--

CREATE TABLE `cleaning_locations` (
  `location_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cleaning_locations`
--

INSERT INTO `cleaning_locations` (`location_id`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(12, 'Kitchen', 'Establishment: Restaurant, Building: Geez, Kitchen Number: 1', 1, '2025-04-23 01:21:54', '2025-04-23 01:21:54');

-- --------------------------------------------------------

--
-- Table structure for table `cleaning_log`
--

CREATE TABLE `cleaning_log` (
  `log_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `completed_date` date NOT NULL,
  `completed_time` time NOT NULL,
  `completed_by_user_id` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verified_by_user_id` int(11) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cleaning_task`
--

CREATE TABLE `cleaning_task` (
  `task_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `frequency` varchar(50) NOT NULL,
  `instructions` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cleaning_task`
--

INSERT INTO `cleaning_task` (`task_id`, `location_id`, `description`, `frequency`, `instructions`, `is_active`, `created_at`, `updated_at`) VALUES
(18, 12, 'Wash Dirty Dishes, Utensils, Glassware, Pots and Pans', 'Daily', '', 1, '2025-04-25 23:41:16', '2025-04-25 23:41:16'),
(19, 12, 'Thoroughly Clean and Disinfect The Sinks and Taps', 'Daily', '', 1, '2025-04-25 23:42:10', '2025-04-25 23:42:10'),
(20, 12, 'Wash and Sanitize All Counter Tops And Prep Area Surfaces', 'Daily', '', 1, '2025-04-25 23:42:54', '2025-04-25 23:42:54'),
(21, 12, 'Disinfect Touch Points, Light Switches and Handles', 'Daily', '', 1, '2025-04-25 23:43:54', '2025-04-25 23:43:54'),
(22, 12, 'Clean Exterior of Appliances. Check For Spiller Food', 'Daily', '', 1, '2025-04-25 23:45:01', '2025-04-25 23:45:01'),
(23, 12, 'Replace Rags/Cloths and Tea Towels With Clean Ones', 'Daily', '', 1, '2025-04-25 23:46:06', '2025-04-25 23:46:06'),
(24, 12, 'Take out The Rubbish/Trash, Remote Waste and Recycle', 'Daily', '', 1, '2025-04-25 23:46:52', '2025-04-25 23:46:52'),
(25, 12, 'Clean And Disinfect Bins Waste Disposal areas And Trash Cans', 'Daily', '', 1, '2025-04-25 23:47:34', '2025-04-26 00:00:00'),
(26, 12, 'Sweep And Mop Tiled And laminate Floor', 'Daily', '', 1, '2025-04-25 23:48:05', '2025-04-25 23:48:05'),
(27, 12, 'Clean cooker', 'Daily', '', 1, '2025-04-25 23:48:47', '2025-04-25 23:48:47'),
(28, 12, 'Clean Microwave, Including the exterior', 'Daily', '', 1, '2025-04-25 23:49:19', '2025-04-25 23:49:19'),
(29, 12, 'Wipe Down The Wall Wherever there are Spills and Splashes', 'Daily', '', 1, '2025-04-25 23:49:54', '2025-04-25 23:49:54'),
(30, 12, 'Replace Empty Paper Towel Rolls and Cloths Roller Towels', 'Daily', '', 1, '2025-04-25 23:51:27', '2025-04-25 23:51:27'),
(31, 12, 'Refill Soap Dispensers and Hand Sanitizerz', 'Daily', '', 1, '2025-04-25 23:52:36', '2025-04-25 23:52:36'),
(32, 12, 'Clean Cabinets and Pantries', 'Daily', '', 1, '2025-04-25 23:53:34', '2025-04-25 23:53:34'),
(33, 12, 'Sanitize Sponges or Replace Damaged ones with New', 'Daily', '', 1, '2025-04-25 23:54:26', '2025-04-25 23:54:26'),
(34, 12, 'Sort Through Leftover Items in The Fridge/Refrigerator', 'Daily', '', 1, '2025-04-25 23:55:24', '2025-04-25 23:55:24'),
(35, 12, 'Clean out Refrigerator And Wipe Down Shelves And Drawers', 'Daily', '', 1, '2025-04-25 23:56:11', '2025-04-25 23:56:11'),
(36, 12, 'Check Fire Exit Lights And Emergency Lights', 'Daily', '', 1, '2025-04-25 23:57:05', '2025-04-25 23:57:05'),
(37, 12, 'Replace And Change Burned Out Light Bulbs/Broken Lights', 'Daily', '', 1, '2025-04-25 23:57:55', '2025-04-25 23:57:55'),
(38, 12, 'Wash And Clean Doors, Door Frames And Glass', 'Daily', '', 1, '2025-04-26 00:00:51', '2025-04-26 00:00:51'),
(39, 12, 'Pour Drain Cleaner Down Floor And Sink Drains', 'Daily', '', 1, '2025-04-26 00:01:35', '2025-04-26 00:01:35'),
(40, 12, 'Check Cleaning Supplies And Restoke As Necessary', 'Daily', '', 1, '2025-04-26 00:02:11', '2025-04-26 00:02:11'),
(41, 12, 'Sanitize The Tables', 'Daily', '', 1, '2025-04-26 00:02:27', '2025-04-26 00:02:27'),
(42, 12, 'Thoroughly Clean Grouts And Tiles', 'Monthly', '', 1, '2025-04-26 00:07:27', '2025-04-26 00:07:47'),
(43, 12, 'Clean Skirting Boards/Baseboards And Corners', 'Monthly', '', 1, '2025-04-26 00:09:25', '2025-04-26 00:09:25'),
(46, 12, 'Clean Under Refrigerator', 'Monthly', '', 1, '2025-04-26 00:12:24', '2025-04-26 00:12:24'),
(47, 12, 'Inventory. Check What is Outdated and What Needs To Be Restoked', 'Monthly', '', 1, '2025-04-26 00:13:25', '2025-04-26 00:21:45'),
(48, 12, 'Wash And Clean Windows', 'Weekly', '', 1, '2025-04-26 00:14:06', '2025-04-26 14:32:01'),
(49, 12, 'Check Hardware. Door Stops, And Lock Mechanisms', 'Monthly', '', 1, '2025-04-26 00:15:05', '2025-04-26 00:15:05'),
(50, 12, 'Dust and Clean Ceilings, Ceiling Corners and Ceiling Tiels', 'Monthly', '', 1, '2025-04-26 00:16:32', '2025-04-26 00:16:32'),
(51, 12, 'Check For Freezer Items That needs Removing or Restoke', 'Monthly', '', 1, '2025-04-26 00:18:29', '2025-04-26 00:18:29'),
(52, 12, 'Check For Outdated Food in Cabinets and Pantries', 'Monthly', '', 1, '2025-04-26 00:19:09', '2025-04-26 00:19:09'),
(53, 12, 'Disinfect and Clean All The Walls from Top To Bottom', 'Monthly', '', 1, '2025-04-26 00:19:37', '2025-04-26 00:19:37'),
(54, 12, 'Empty Grease Trap', 'Monthly', '', 1, '2025-04-26 00:20:04', '2025-04-26 00:20:04'),
(63, 12, 'Clean Refrigerator Coils to Remove Dust. Unplug First.', 'Monthly', '', 1, '2025-04-26 00:27:02', '2025-04-26 00:27:02');

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `equipment_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `content` varchar(255) DEFAULT NULL COMMENT 'Typical content stored in the equipment',
  `quantity_in_stock` int(11) DEFAULT NULL COMMENT 'Current quantity of items in stock',
  `min_stock_quantity` int(11) DEFAULT NULL COMMENT 'Minimum desired stock quantity',
  `min_temp` decimal(5,1) NOT NULL,
  `max_temp` decimal(5,1) NOT NULL,
  `check_frequency` varchar(50) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`equipment_id`, `name`, `location`, `content`, `quantity_in_stock`, `min_stock_quantity`, `min_temp`, `max_temp`, `check_frequency`, `is_active`, `created_at`, `updated_at`) VALUES
(10, 'Kitchen Main Fridge', 'Kitchen', 'Dairy Products, Vegetables and Fresh meat', NULL, NULL, 18.0, 25.0, 'Daily', 1, '2025-04-23 21:26:43', '2025-04-29 20:15:38'),
(11, 'Frozen Fridge', 'Store Room', 'Frozen Vegetables', NULL, NULL, -18.0, -22.0, 'Daily', 1, '2025-04-23 21:27:58', '2025-04-29 20:18:06');

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

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'company_name', 'Geez Restaurant', '2025-04-09 02:58:10', NULL),
(2, 'company_address', '123 Main Street, Anytown, USA 12345', '2025-04-09 02:58:10', NULL),
(3, 'date_format', 'Y-m-d', '2025-04-09 02:58:10', NULL),
(4, 'time_format', 'H:i', '2025-04-09 02:58:10', NULL),
(5, 'temperature_unit', 'C', '2025-04-09 02:58:10', NULL),
(6, 'weight_unit', 'kg', '2025-04-09 02:58:10', NULL),
(7, 'currency_symbol', '$', '2025-04-09 02:58:10', NULL),
(8, 'items_per_page', '20', '2025-04-09 02:58:10', NULL),
(9, 'installation_date', '2025-04-09', '2025-04-09 02:58:10', NULL),
(10, 'company_phone', '(555) 123-4567', '2025-04-09 03:00:31', '2025-04-09 03:00:31'),
(11, 'company_email', 'info@geezrestaurant.com', '2025-04-09 03:00:31', '2025-04-09 03:00:31'),
(12, 'enable_notifications', '0', '2025-04-09 03:00:31', '2025-04-09 03:00:31'),
(13, 'maintenance_mode', '0', '2025-04-09 03:00:31', '2025-04-09 03:00:31'),
(14, 'sample_data_installed', '1', '2025-04-09 03:00:31', '2025-04-09 03:00:31');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Geez Restaurant Food Hygiene & Safety Management System', '2025-04-09 02:58:24', '2025-04-09 02:58:24'),
(2, 'company_name', 'Geez Restaurant', '2025-04-09 02:58:24', '2025-04-09 02:58:24'),
(3, 'temperature_unit', 'C', '2025-04-09 02:58:24', '2025-04-09 02:58:24'),
(4, 'date_format', 'd/m/Y', '2025-04-09 02:58:24', '2025-04-09 02:58:24'),
(5, 'time_format', 'H:i', '2025-04-09 02:58:24', '2025-04-09 02:58:24'),
(6, 'items_per_page', '20', '2025-04-09 02:58:24', '2025-04-09 02:58:24'),
(7, 'enable_email_notifications', '0', '2025-04-09 02:58:24', '2025-04-09 02:58:24');

-- --------------------------------------------------------

--
-- Table structure for table `temperature_checks`
--

CREATE TABLE `temperature_checks` (
  `check_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `temperature` decimal(5,1) NOT NULL,
  `is_compliant` tinyint(1) NOT NULL,
  `corrective_action` text DEFAULT NULL,
  `check_date` date NOT NULL,
  `check_time` time NOT NULL,
  `checked_by_user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `temperature_checks`
--

INSERT INTO `temperature_checks` (`check_id`, `equipment_id`, `temperature`, `is_compliant`, `corrective_action`, `check_date`, `check_time`, `checked_by_user_id`, `created_at`, `updated_at`) VALUES
(1, 10, 21.0, 1, '', '2023-08-12', '10:34:00', 1, '2025-04-29 20:09:35', NULL),
(2, 10, 20.5, 1, '', '2023-08-13', '10:41:00', 1, '2025-04-29 20:42:54', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `initials` varchar(10) DEFAULT NULL,
  `role` enum('admin','manager','staff') NOT NULL DEFAULT 'staff',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `full_name`, `email`, `initials`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'Abela', '$2y$10$Mxcs13DOiogxhkKYHp7F1eJmjqs5tO0lM7fVwy1lw/egR9Rz4RrjC', 'Abel Demssie', NULL, 'Ab', 'admin', 1, '2025-04-29 18:32:06', '2025-04-09 02:58:10', NULL),
(4, 'Michael', '$2y$10$mbcn1dHgi2/WZSEvVoxho.CyD6tZPIV0nYxGUBRIQsl1ddd7NdCSG', 'Michael Werkneh', NULL, 'RM', 'admin', 1, '2025-04-25 23:38:12', '2025-04-09 03:00:30', NULL),
(5, 'Ruth', '$2y$10$r2XhskKlz0kcWJFpMSQS/uouFKhC9.qlwodlnNtPF/JGZGI1IZgUK', 'Ruth Alemu', NULL, 'HC', 'staff', 1, NULL, '2025-04-09 03:00:30', NULL),
(6, 'Mahlet', '$2y$10$r1v0LMTqXBe.5NhkKpiAYuJZxD40O8BUTw194e0krxTX1VREb07lW', 'Mahlet Zerfu', NULL, 'KS', 'staff', 1, '2025-04-23 01:24:12', '2025-04-09 03:00:30', NULL),
(7, 'Yonas', '$2y$10$BvRA8qOmAeTKMmIefM1afOerGVkjKB4r/m52vIgBH6h0npM07rhQK', 'Kibrom Zenebe', NULL, 'SS', 'staff', 1, NULL, '2025-04-09 03:00:30', NULL),
(8, 'sara', '$2y$10$jEfGGOqO37M10Mnbfr8KmO.YHK/Fi2qc7/tYF4kcmoBAMpYI6TjJO', 'Sara Teshome', NULL, NULL, 'manager', 1, NULL, '0000-00-00 00:00:00', NULL),
(11, 'Tsion', '$2y$10$dx6XNVwJp1wcdpwH2LI14OGW4MaOcmp3NJypym11edV3yvnvE3q.e', 'Tsion Alemayehu', NULL, NULL, 'staff', 1, NULL, '2025-04-25 00:39:20', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cleaning_locations`
--
ALTER TABLE `cleaning_locations`
  ADD PRIMARY KEY (`location_id`);

--
-- Indexes for table `cleaning_log`
--
ALTER TABLE `cleaning_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `completed_by_user_id` (`completed_by_user_id`),
  ADD KEY `verified_by_user_id` (`verified_by_user_id`);

--
-- Indexes for table `cleaning_task`
--
ALTER TABLE `cleaning_task`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`equipment_id`);

--
-- Indexes for table `food_waste_log`
--
ALTER TABLE `food_waste_log`
  ADD PRIMARY KEY (`waste_id`),
  ADD KEY `recorded_by_user_id` (`recorded_by_user_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `temperature_checks`
--
ALTER TABLE `temperature_checks`
  ADD PRIMARY KEY (`check_id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `checked_by_user_id` (`checked_by_user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cleaning_locations`
--
ALTER TABLE `cleaning_locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `cleaning_log`
--
ALTER TABLE `cleaning_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cleaning_task`
--
ALTER TABLE `cleaning_task`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `equipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `food_waste_log`
--
ALTER TABLE `food_waste_log`
  MODIFY `waste_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `temperature_checks`
--
ALTER TABLE `temperature_checks`
  MODIFY `check_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cleaning_log`
--
ALTER TABLE `cleaning_log`
  ADD CONSTRAINT `cleaning_log_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `cleaning_task` (`task_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cleaning_log_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `cleaning_locations` (`location_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cleaning_log_ibfk_3` FOREIGN KEY (`completed_by_user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `cleaning_log_ibfk_4` FOREIGN KEY (`verified_by_user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `cleaning_task`
--
ALTER TABLE `cleaning_task`
  ADD CONSTRAINT `cleaning_task_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `cleaning_locations` (`location_id`) ON DELETE CASCADE;

--
-- Constraints for table `food_waste_log`
--
ALTER TABLE `food_waste_log`
  ADD CONSTRAINT `food_waste_log_ibfk_1` FOREIGN KEY (`recorded_by_user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `temperature_checks`
--
ALTER TABLE `temperature_checks`
  ADD CONSTRAINT `temperature_checks_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`equipment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `temperature_checks_ibfk_2` FOREIGN KEY (`checked_by_user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
