-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 22, 2025 at 09:23 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fdm_expenses`
--

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employeeId` int(11) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('Employee','Manager','Admin','Finance') NOT NULL DEFAULT 'Employee',
  `username` varchar(50) NOT NULL,
  `passwordHash` varchar(255) NOT NULL,
  `manager` int(11) DEFAULT NULL,
  `loggedIn` date DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employeeId`, `firstName`, `lastName`, `email`, `role`, `username`, `passwordHash`, `manager`, `loggedIn`, `created_at`) VALUES
(3, 'Test', 'Manager', 'testmanager@gmail.com', 'Manager', 'testmanager', '$2y$10$u5q1LHxArgbnsYBOCFfvW.r2/vsBw2WUHtsABPokvJlSWC7useebW', NULL, '2025-03-22', '2025-03-21 16:03:58'),
(7, 'John', 'Smith', 'johnsmith@fdm.com', 'Finance', 'johnsmith', 'f', 3, '2000-01-27', '2025-03-21 16:35:50');

-- --------------------------------------------------------

--
-- Table structure for table `expense_claims`
--

CREATE TABLE `expense_claims` (
  `claimId` int(11) NOT NULL,
  `employeeId` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` varchar(255) NOT NULL,
  `category` varchar(50) NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `evidenceFile` varchar(255) NOT NULL,
  `receipt` varchar(255) NOT NULL,
  `currency` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE expense_claims 
MODIFY status ENUM('Pending', 'Approved', 'Rejected', 'Reimbursed') DEFAULT 'Pending';

-- Dumping data for table `expense_claims`
-- INSERT INTO `expense_claims` 
-- (`claimId`, `employeeId`, `amount`, `description`, `category`, `status`, `date`, `evidenceFile`, `receipt`, `currency`) 
-- VALUES 
-- (13, 7, 34.56, 'food', 'Food', 'Pending', '2025-03-22 18:36:10', 'uploads/67df031a15369_drizzle-background.jpg', '', 'GBP');

-- Extra Approved Claims
INSERT INTO `expense_claims` (`employeeId`, `amount`, `description`, `category`, `status`, `date`, `evidenceFile`, `receipt`, `currency`) VALUES
(7, 12.50, 'Team lunch', 'Food', 'Approved', NOW(), 'uploads/evidence1.jpg', '', 'GBP'),
(7, 75.00, 'Train to client site', 'Travel', 'Approved', NOW(), 'uploads/evidence2.jpg', '', 'GBP'),
(7, 19.99, 'Notebook', 'Office Supplies', 'Approved', NOW(), 'uploads/evidence3.jpg', '', 'GBP'),
(7, 200.00, 'Hotel for training', 'Accommodation', 'Approved', NOW(), 'uploads/evidence4.jpg', '', 'GBP'),
(7, 5.00, 'Coffee for team meeting', 'Food', 'Approved', NOW(), 'uploads/evidence5.jpg', '', 'GBP'),
(7, 60.00, 'Client dinner', 'Food', 'Approved', NOW(), 'uploads/evidence6.jpg', '', 'GBP'),
(7, 8.40, 'Fuel reimbursement', 'Fuel', 'Approved', NOW(), 'uploads/evidence7.jpg', '', 'GBP'),
(7, 15.00, 'Printer paper', 'Office Supplies', 'Approved', NOW(), 'uploads/evidence8.jpg', '', 'GBP'),
(7, 3.00, 'Bus fare', 'Travel', 'Approved', NOW(), 'uploads/evidence9.jpg', '', 'GBP'),
(7, 99.99, 'External mouse', 'Office Supplies', 'Approved', NOW(), 'uploads/evidence10.jpg', '', 'GBP');

-- Extra Pending Claims
INSERT INTO `expense_claims` (`employeeId`, `amount`, `description`, `category`, `status`, `date`, `evidenceFile`, `receipt`, `currency`) VALUES
(7, 45.00, 'Taxi to office', 'Travel', 'Pending', NOW(), 'uploads/evidence11.jpg', '', 'GBP'),
(7, 10.00, 'Snacks for event', 'Food', 'Pending', NOW(), 'uploads/evidence12.jpg', '', 'GBP'),
(7, 22.22, 'Stationery set', 'Office Supplies', 'Pending', NOW(), 'uploads/evidence13.jpg', '', 'GBP');

-- Extra Rejected Claims
INSERT INTO `expense_claims` (`employeeId`, `amount`, `description`, `category`, `status`, `date`, `evidenceFile`, `receipt`, `currency`) VALUES
(7, 150.00, 'Personal phone charger', 'Office Supplies', 'Rejected', NOW(), 'uploads/evidence14.jpg', '', 'GBP'),
(7, 500.00, 'Out-of-policy dinner', 'Food', 'Rejected', NOW(), 'uploads/evidence15.jpg', '', 'GBP'),
(7, 5.50, 'Unverified parking ticket', 'Travel', 'Rejected', NOW(), 'uploads/evidence16.jpg', '', 'GBP');

-- Extra Reimbursed Claims
INSERT INTO `expense_claims` (`employeeId`, `amount`, `description`, `category`, `status`, `date`, `evidenceFile`, `receipt`, `currency`) VALUES
(7, 23.45, 'Lunch with new hire', 'Food', 'Reimbursed', NOW(), 'uploads/evidence17.jpg', '', 'GBP'),
(7, 100.00, 'Conference travel', 'Travel', 'Reimbursed', NOW(), 'uploads/evidence18.jpg', '', 'GBP'),
(7, 80.00, 'Office chair', 'Office Supplies', 'Reimbursed', NOW(), 'uploads/evidence19.jpg', '', 'GBP');



-- --------------------------------------------------------

--
-- Table structure for table `managers`
--

CREATE TABLE `managers` (
  `managerId` int(11) NOT NULL,
  `spendingLimit` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `managers`
--

INSERT INTO `managers` (`managerId`, `spendingLimit`) VALUES
(3, 10000.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employeeId`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `manager` (`manager`);

--
-- Indexes for table `expense_claims`
--
ALTER TABLE expense_claims

  ADD COLUMN temp_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY;

UPDATE expense_claims 

  SET claimId = temp_id;

ALTER TABLE expense_claims

  DROP COLUMN temp_id;

ALTER TABLE expense_claims

  ADD PRIMARY KEY (`claimId`);

ALTER TABLE expense_claims

  MODIFY claimId INT(11) NOT NULL AUTO_INCREMENT;
--
-- Indexes for table `managers`
--
ALTER TABLE `managers`
  ADD PRIMARY KEY (`managerId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employeeId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `expense_claims`
--
ALTER TABLE `expense_claims`
  MODIFY `claimId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`manager`) REFERENCES `employees` (`employeeId`) ON DELETE SET NULL;

--
-- Constraints for table `expense_claims`
--
ALTER TABLE `expense_claims`
  ADD CONSTRAINT `expense_claims_ibfk_1` FOREIGN KEY (`employeeId`) REFERENCES `employees` (`employeeId`) ON DELETE CASCADE;

--
-- Constraints for table `managers`
--
ALTER TABLE `managers`
  ADD CONSTRAINT `managers_ibfk_1` FOREIGN KEY (`managerID`) REFERENCES `employees` (`employeeId`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
