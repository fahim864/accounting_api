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
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `setting_name` text NOT NULL,
  `company_name` text NOT NULL,
  `shopid` int(11) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `gsttin` longtext DEFAULT NULL,
  `company_logo` text NOT NULL,
  `phone_number` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `admin_id`, `setting_name`, `company_name`, `shopid`, `address`, `gsttin`, `company_logo`, `phone_number`) VALUES
(1, 4, 'DCBD', 'Disseminare Consulting Pvt (BD.) Ltd.', NULL, 'H-41/1, Road-1, Block-A, Niketon, GUlshan-1, Dhaka.', '1111111111', '', '9876543210'),
(2, 5, 'DCBD', 'Disseminare Consulting Pvt (BD.) Ltd.', NULL, 'H-41/1, Road-1, Block-A, Niketon, GUlshan-1, Dhaka.', '1111111111', '', '9876543210');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
