-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 19, 2022 at 01:30 PM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 7.3.33

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES latin1 */;

--
-- Database: `accounting_api_v2`
--

-- --------------------------------------------------------

--
-- Table structure for table `goods_master`
--
-- Creation: May 19, 2022 at 03:08 AM
-- Last update: May 19, 2022 at 04:50 AM
--

DROP TABLE IF EXISTS `goods_master`;
CREATE TABLE IF NOT EXISTS `goods_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL,
  `goods_name` text NOT NULL,
  `gst_category` text NOT NULL,
  `hsn_code` varchar(255) NOT NULL,
  `gst_applicable` varchar(255) NOT NULL,
  `effective_start_date` datetime NOT NULL,
  `effective_end_date` datetime DEFAULT NULL,
  `tracking` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- RELATIONSHIPS FOR TABLE `goods_master`:
--

--
-- Dumping data for table `goods_master`
--

INSERT DELAYED IGNORE INTO `goods_master` (`id`, `admin_id`, `goods_name`, `gst_category`, `hsn_code`, `gst_applicable`, `effective_start_date`, `effective_end_date`, `tracking`) VALUES
(1, 6, 'Fahimt2341new', 'Fahimt4r1234new', 'fadelos234weq', '55wqr', '2022-05-19 08:38:51', '2022-05-19 09:10:48', '12'),
(3, 6, 'Fahimt2341new312213', 'Fahimt4r1234new', 'fadelos234weq', '55wqr', '2022-05-19 08:40:48', NULL, '12'),
(4, 6, 'Fahim33', 'Fahim', 'fahim@gmail.com', '10', '2022-05-19 10:20:05', NULL, NULL),
(5, 6, 'Fahim333', 'Fahim', 'fahim@gmail.com', '12', '2022-05-19 10:20:05', NULL, NULL),
(6, 6, 'Fahim336', 'Fahim', 'fahim@gmail.com', '14', '2022-05-19 10:20:05', NULL, NULL);


--
-- Metadata
--
USE `phpmyadmin`;

--
-- Metadata for table goods_master
--
-- Error reading data for table phpmyadmin.pma__table_uiprefs: #1100 - Table 'pma__table_uiprefs' was not locked with LOCK TABLES
-- Error reading data for table phpmyadmin.pma__tracking: #1100 - Table 'pma__tracking' was not locked with LOCK TABLES
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
