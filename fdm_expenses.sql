-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 08, 2025 at 12:39 AM
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
(3, 'Test', 'Manager', 'testmanager@gmail.com', 'Manager', 'testmanager', '$2y$10$u5q1LHxArgbnsYBOCFfvW.r2/vsBw2WUHtsABPokvJlSWC7useebW', NULL, '2025-04-07', '2025-03-21 16:03:58'),
(7, 'John', 'Smith', 'johnsmith@fdm.com', 'Finance', 'johnsmith', 'f', 3, '2000-01-27', '2025-03-21 16:35:50'),
(9, 'Admin', 'McAdmin', 'admin@fdm.co.uk', 'Admin', 'admin', '$2y$10$EIEDd/5H6qa7qJvrvjx9E.6nTDXQFRNI8WY9ywRSZ4GqLGJwFQyvK', 3, '2025-04-07', '2025-04-07 21:43:51'),
(10, 'Steve', 'Clinton', 'stevec@fdm.co.uk', 'Finance', 'steve', '$2y$10$hyoXLmLJyTaWsLHAklrLgeor2g1o6yBLTZU5wKqvl4ebstpLhid5e', 3, '2025-04-07', '2025-04-07 22:00:18'),
(11, 'Carol', 'Hendricks', 'chendricks@fdm.co.uk', 'Employee', 'chendricks', '$2y$10$/DFW8b8is8zxyQL1fzfg/.v2rAZmGBJ/p4LAOrSos/oua4adayEr2', 3, '2025-04-07', '2025-04-07 22:01:54');

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
  `status` enum('Pending','Approved','Rejected','Reimbursed') DEFAULT 'Pending',
  `managerMessage` varchar(500) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `evidenceFile` varchar(255) NOT NULL,
  `receipt` varchar(255) NOT NULL,
  `currency` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expense_claims`
--

INSERT INTO `expense_claims` (`claimId`, `employeeId`, `amount`, `description`, `category`, `status`, `date`, `evidenceFile`, `receipt`, `currency`) VALUES
(1, 7, 12.50, 'Team lunch', 'Food', 'Approved', '2025-04-07 21:22:32', 'uploads/evidence1.jpg', '', 'GBP'),
(2, 7, 75.00, 'Train to client site', 'Travel', 'Approved', '2025-04-07 21:22:32', 'uploads/evidence2.jpg', '', 'GBP'),
(3, 7, 19.99, 'Notebook', 'Office Supplies', 'Approved', '2025-04-07 21:22:32', 'uploads/evidence3.jpg', '', 'GBP'),
(4, 7, 200.00, 'Hotel for training', 'Accommodation', 'Approved', '2025-04-07 21:22:32', 'uploads/evidence4.jpg', '', 'GBP'),
(5, 7, 5.00, 'Coffee for team meeting', 'Food', 'Approved', '2025-04-07 21:22:32', 'uploads/evidence5.jpg', '', 'GBP'),
(6, 7, 60.00, 'Client dinner', 'Food', 'Approved', '2025-04-07 21:22:32', 'uploads/evidence6.jpg', '', 'GBP'),
(7, 7, 8.40, 'Fuel reimbursement', 'Fuel', 'Approved', '2025-04-07 21:22:32', 'uploads/evidence7.jpg', '', 'GBP'),
(8, 7, 15.00, 'Printer paper', 'Office Supplies', 'Approved', '2025-04-07 21:22:32', 'uploads/evidence8.jpg', '', 'GBP'),
(9, 7, 3.00, 'Bus fare', 'Travel', 'Approved', '2025-04-07 21:22:32', 'uploads/evidence9.jpg', '', 'GBP'),
(10, 7, 99.99, 'External mouse', 'Office Supplies', 'Approved', '2025-04-07 21:22:32', 'uploads/evidence10.jpg', '', 'GBP'),
(11, 7, 45.00, 'Taxi to office', 'Travel', 'Pending', '2025-04-07 21:22:32', 'uploads/evidence11.jpg', '', 'GBP'),
(12, 7, 10.00, 'Snacks for event', 'Food', 'Pending', '2025-04-07 21:22:32', 'uploads/evidence12.jpg', '', 'GBP'),
(13, 7, 22.22, 'Stationery set', 'Office Supplies', 'Pending', '2025-04-07 21:22:32', 'uploads/evidence13.jpg', '', 'GBP'),
(14, 7, 150.00, 'Personal phone charger', 'Office Supplies', 'Rejected', '2025-04-07 21:22:32', 'uploads/evidence14.jpg', '', 'GBP'),
(15, 7, 500.00, 'Out-of-policy dinner', 'Food', 'Rejected', '2025-04-07 21:22:32', 'uploads/evidence15.jpg', '', 'GBP'),
(16, 7, 5.50, 'Unverified parking ticket', 'Travel', 'Rejected', '2025-04-07 21:22:32', 'uploads/evidence16.jpg', '', 'GBP'),
(17, 7, 23.45, 'Lunch with new hire', 'Food', 'Reimbursed', '2025-04-07 21:22:32', 'uploads/evidence17.jpg', '', 'GBP'),
(18, 7, 100.00, 'Conference travel', 'Travel', 'Reimbursed', '2025-04-07 21:22:32', 'uploads/evidence18.jpg', '', 'GBP'),
(19, 7, 80.00, 'Office chair', 'Office Supplies', 'Reimbursed', '2025-04-07 21:22:32', 'uploads/evidence19.jpg', '', 'GBP');

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

-- --------------------------------------------------------

--
-- Table structure for table `sys_log`
--

CREATE TABLE `sys_log` (
  `logId` int(11) NOT NULL,
  `employeeId` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `role` enum('Employee','Manager','Finance', 'Admin') NOT NULL,
  `event` varchar(255) NOT NULL,
  `eventTime` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
ALTER TABLE `expense_claims`
  ADD PRIMARY KEY (`claimId`),
  ADD KEY `expense_claims_ibfk_1` (`employeeId`);

--
-- Indexes for table `managers`
--
ALTER TABLE `managers`
  ADD PRIMARY KEY (`managerId`);

--
-- Indexes for table `sys_log`
--
ALTER TABLE `sys_log`
  ADD PRIMARY KEY (`logId`),
  ADD KEY `employeeId` (`employeeId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employeeId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `expense_claims`
--
ALTER TABLE `expense_claims`
  MODIFY `claimId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `sys_log`
--
ALTER TABLE `sys_log`
  MODIFY `logId` int(11) NOT NULL AUTO_INCREMENT;

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
  ADD CONSTRAINT `managers_ibfk_1` FOREIGN KEY (`managerId`) REFERENCES `employees` (`employeeId`) ON DELETE CASCADE;

--
-- Constraints for table `sys_log`
--
ALTER TABLE `sys_log`
  ADD CONSTRAINT `sys_log_ibfk_1` FOREIGN KEY (`employeeId`) REFERENCES `employees` (`employeeId`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
