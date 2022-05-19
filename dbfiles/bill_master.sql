-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 19, 2022 at 01:29 PM
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
-- Table structure for table `bill_master`
--
-- Creation: May 19, 2022 at 06:39 AM
-- Last update: May 19, 2022 at 06:40 AM
--

DROP TABLE IF EXISTS `bill_master`;
CREATE TABLE IF NOT EXISTS `bill_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `bill_no` text NOT NULL,
  `customer_id` text NOT NULL,
  `bill_date` date NOT NULL,
  `amt_w/o_tax` int(128) NOT NULL,
  `tax_amt` int(128) NOT NULL,
  `total_amt` int(128) NOT NULL,
  `discount%` int(11) NOT NULL,
  `net_amt` int(128) NOT NULL,
  `returned_amount_w/o_tax` int(11) DEFAULT NULL,
  `tax_returned_amnt` int(11) DEFAULT NULL,
  `amt_paid` int(128) NOT NULL,
  `amt_due` int(128) NOT NULL,
  `payment_status` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- RELATIONSHIPS FOR TABLE `bill_master`:
--

--
-- Dumping data for table `bill_master`
--

INSERT DELAYED IGNORE INTO `bill_master` (`id`, `admin_id`, `bill_no`, `customer_id`, `bill_date`, `amt_w/o_tax`, `tax_amt`, `total_amt`, `discount%`, `net_amt`, `returned_amount_w/o_tax`, `tax_returned_amnt`, `amt_paid`, `amt_due`, `payment_status`) VALUES
(1, 6, 'BILL_1', 'S10001', '2022-04-06', 40, 6, 46, 0, 46, NULL, NULL, 10, 36, 'incomplete'),
(2, 6, 'BILL_2', 'S10001', '2022-04-06', 12, 2, 14, 0, 14, NULL, NULL, 14, 0, 'complete'),
(3, 6, 'BILL_3', 'S10001', '2022-04-14', 1000, 0, 1000, 0, 1000, NULL, NULL, 0, 1000, 'saved_draft'),
(4, 6, 'BILL_4', 'S10001', '2022-04-21', 120, 0, 120, 0, 120, NULL, NULL, 0, 120, 'saved_draft'),
(5, 6, 'BILL_5', 'S10001', '2022-04-21', 140, 0, 140, 0, 140, NULL, NULL, 0, 140, 'saved_draft');


--
-- Metadata
--
USE `phpmyadmin`;

--
-- Metadata for table bill_master
--
-- Error reading data for table phpmyadmin.pma__table_uiprefs: #1100 - Table 'pma__table_uiprefs' was not locked with LOCK TABLES
-- Error reading data for table phpmyadmin.pma__tracking: #1100 - Table 'pma__tracking' was not locked with LOCK TABLES
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
