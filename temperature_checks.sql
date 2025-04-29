-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 29, 2025 at 07:48 PM
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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `temperature_checks`
--
ALTER TABLE `temperature_checks`
  ADD PRIMARY KEY (`check_id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `checked_by_user_id` (`checked_by_user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `temperature_checks`
--
ALTER TABLE `temperature_checks`
  MODIFY `check_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

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
