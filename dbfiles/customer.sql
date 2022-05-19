-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2022 at 08:58 AM
-- Server version: 10.4.20-MariaDB
-- PHP Version: 7.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `accounting_api_v2`
--

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `customer_id` varchar(255) NOT NULL,
  `customer_type` text NOT NULL,
  `customer_name` text NOT NULL,
  `customer_email` text NOT NULL,
  `customer_phone` text NOT NULL,
  `customer_creation` datetime NOT NULL,
  `customer_eff_sdc_start_date` datetime NOT NULL,
  `customer_eff_sdc_end_date` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`id`, `admin_id`, `customer_id`, `customer_type`, `customer_name`, `customer_email`, `customer_phone`, `customer_creation`, `customer_eff_sdc_start_date`, `customer_eff_sdc_end_date`) VALUES
(1, 4, 'C10001', 'C', 'dummy_buyers', 'dummybuyers@gmail.com', '9999999999', '2021-10-21 17:06:39', '2021-10-21 17:06:39', '2022-05-17 16:01:00'),
(2, 4, 'S10001', 'S', 'dummy_suppliers', 'dummysupplies@gmail.com', '1111111111', '2021-10-21 17:10:45', '2021-10-21 17:10:45', NULL),
(3, 4, 'C10002', 'C', 'dummy2', 'dummy2@gmail.com', '8965231478', '2022-04-20 11:58:34', '2022-04-20 11:58:34', '2022-05-17 15:58:04'),
(4, 4, 'C10003', 'C', 'Fahim', 'fahim@gmail.com', '0174812345', '2022-05-17 13:48:37', '2022-05-17 13:48:37', NULL),
(5, 4, 'C10004', 'C', 'Fahim', 'fahim@gmail.com', '0174812345', '2022-05-17 13:48:37', '2022-05-17 13:48:37', NULL),
(6, 4, 'C10005', 'C', 'Fahim', 'fahim@gmail.com', '9876543210', '2022-05-17 14:29:25', '2022-05-17 14:29:25', NULL),
(7, 4, 'C10006', 'C', 'Fahim', 'fahim@gmail.com', '9876543211', '2022-05-17 14:29:34', '2022-05-17 14:29:34', NULL),
(8, 4, 'C10007', 'C', 'Fahim', 'fahim@gmail.com', '9876543212', '2022-05-17 15:07:43', '2022-05-17 15:07:43', NULL),
(9, 4, 'C10002', 'C', 'Fahim', 'fahim@gmail.com', '9876543213', '2022-05-17 15:28:04', '2022-05-17 15:28:04', '2022-05-17 15:58:55'),
(10, 4, 'C10002', 'C', 'Fahim', 'fahim@gmail.com', '8965231478', '2022-05-17 15:28:55', '2022-05-17 15:28:55', '2022-05-17 16:10:17'),
(11, 4, 'C10001', 'C', 'Fahim', 'fahim@gmail.com', '9876543213', '2022-05-17 15:31:00', '2022-05-17 15:31:00', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`,`customer_eff_sdc_start_date`),
  ADD UNIQUE KEY `id` (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
