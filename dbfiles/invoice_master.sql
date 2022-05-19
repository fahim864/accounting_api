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
-- Table structure for table `invoice_master`
--
-- Creation: May 19, 2022 at 05:55 AM
-- Last update: May 19, 2022 at 06:08 AM
--

DROP TABLE IF EXISTS `invoice_master`;
CREATE TABLE IF NOT EXISTS `invoice_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `invoice_no` text NOT NULL,
  `customer_id` text NOT NULL,
  `invoice_date` date NOT NULL,
  `org_amt_w/o tax` int(128) NOT NULL,
  `tax_amt` int(128) NOT NULL,
  `total_amt` int(128) NOT NULL,
  `discount%` int(11) NOT NULL,
  `discountorgamt` int(11) NOT NULL,
  `net_amt` int(128) NOT NULL,
  `returned_amount_w/o_tax` int(11) DEFAULT NULL,
  `tax_returned_amnt` int(11) DEFAULT NULL,
  `amount_paid` int(128) NOT NULL,
  `amount_due` int(128) NOT NULL,
  `payment_status` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

--
-- RELATIONSHIPS FOR TABLE `invoice_master`:
--

--
-- Dumping data for table `invoice_master`
--

INSERT DELAYED IGNORE INTO `invoice_master` (`id`, `admin_id`, `invoice_no`, `customer_id`, `invoice_date`, `org_amt_w/o tax`, `tax_amt`, `total_amt`, `discount%`, `discountorgamt`, `net_amt`, `returned_amount_w/o_tax`, `tax_returned_amnt`, `amount_paid`, `amount_due`, `payment_status`) VALUES
(1, 0, 'INV_1', 'C10001', '2022-04-06', 20, 3, 23, 0, 0, 23, NULL, NULL, 0, 0, 'saved_draft'),
(2, 0, 'INV_2', 'C10001', '2022-04-06', 12, 2, 14, 0, 0, 14, NULL, NULL, 0, 14, 'incomplete'),
(3, 0, 'INV_3', 'C10001', '2022-04-06', 180, 27, 207, 0, 0, 207, NULL, NULL, 207, 0, 'complete'),
(4, 0, 'INV_4', 'C10001', '2022-04-06', 15, 2, 17, 0, 0, 17, NULL, NULL, 17, 0, 'complete'),
(5, 0, 'INV_5', 'C10001', '2022-04-23', 100, 0, 100, 0, 0, 100, NULL, NULL, 0, 0, 'saved_draft'),
(6, 0, 'INV_6', 'C10001', '2022-04-23', 882, 0, 882, 0, 0, 882, NULL, NULL, 0, 0, 'saved_draft'),
(7, 6, 'INV_7', 'C10001', '2022-05-09', 120, 0, 120, 10, 12, 108, NULL, NULL, 108, 0, 'complete'),
(8, 0, 'INV_8', 'C10001', '2022-05-09', 6000, 900, 6900, 0, 0, 6900, NULL, NULL, 6900, 0, 'complete');


--
-- Metadata
--
USE `phpmyadmin`;

--
-- Metadata for table invoice_master
--
-- Error reading data for table phpmyadmin.pma__table_uiprefs: #1100 - Table 'pma__table_uiprefs' was not locked with LOCK TABLES
-- Error reading data for table phpmyadmin.pma__tracking: #1100 - Table 'pma__tracking' was not locked with LOCK TABLES
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
