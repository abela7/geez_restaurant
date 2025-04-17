-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 17, 2025 at 10:56 PM
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
-- Database: `admi_geez_restaurant`
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
(1, 'admin', '$2y$10$2W/ck3zFb1F1Yu.44nxnwOlYiTVl4JubyQPAQIoS2NSgDKF8dywym', 'Abel Demssie', NULL, 'Ab', 'admin', 1, '2025-04-15 23:40:37', '2025-04-09 02:58:10', NULL),
(4, 'Michael', '$2y$10$jDjUQsrJQPSAH2WoferrmufqrYXkdaq6kHGdEy7im.ZOb6FfAxOCy', 'Michael Werkneh', NULL, 'RM', 'admin', 1, NULL, '2025-04-09 03:00:30', NULL),
(5, 'Ruth', '$2y$10$r2XhskKlz0kcWJFpMSQS/uouFKhC9.qlwodlnNtPF/JGZGI1IZgUK', 'Ruth Alemu', NULL, 'HC', 'staff', 1, NULL, '2025-04-09 03:00:30', NULL),
(6, 'Mahlet', '$2y$10$.cQJe3OkedhEwv/SBQ8NB.MLuOUjZqoxh2OW9gWPIDhcIekZ9FtKW', 'Mahlet Zerfu', NULL, 'KS', 'staff', 1, NULL, '2025-04-09 03:00:30', NULL),
(7, 'Yonas', '$2y$10$H31N0s2wkPuJkiRBr3pqNesDjK7BoeEMk2UYG2LDp6Bz2viMmksBm', 'Kibrom Zenebe', NULL, 'SS', 'staff', 0, NULL, '2025-04-09 03:00:30', NULL),
(8, 'sara', '$2y$10$jEfGGOqO37M10Mnbfr8KmO.YHK/Fi2qc7/tYF4kcmoBAMpYI6TjJO', 'Sara Teshome', NULL, NULL, 'manager', 1, NULL, '0000-00-00 00:00:00', NULL);

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
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `cleaning_log`
--
ALTER TABLE `cleaning_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cleaning_task`
--
ALTER TABLE `cleaning_task`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `equipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
  MODIFY `check_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
