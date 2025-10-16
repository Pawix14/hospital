-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
<<<<<<< HEAD
-- Generation Time: Oct 16, 2025 at 01:14 PM
=======
-- Generation Time: Oct 09, 2025 at 07:34 PM
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
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
-- Database: `myhmsdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `adminusertb`
--

CREATE TABLE `adminusertb` (
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'admin',
  `two_factor_enabled` tinyint(4) DEFAULT 0,
  `two_factor_code` varchar(10) DEFAULT NULL,
  `two_factor_expires` datetime DEFAULT NULL,
  `backup_codes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `adminusertb`
--

INSERT INTO `adminusertb` (`username`, `password`, `email`, `role`, `two_factor_enabled`, `two_factor_code`, `two_factor_expires`, `backup_codes`) VALUES
('admin', 'admin123', 'pmadridano2@gmail.com', 'admin', 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admissiontb`
--

CREATE TABLE `admissiontb` (
  `pid` int(11) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `email` varchar(50) NOT NULL,
  `contact` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `admission_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Admitted',
  `age` int(11) DEFAULT 0,
  `address` text DEFAULT NULL,
  `blood_group` varchar(10) DEFAULT '',
  `medical_history` text DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `assigned_doctor` varchar(50) DEFAULT '',
  `room_number` varchar(20) DEFAULT '',
  `created_by` varchar(50) DEFAULT 'nurse',
  `reason` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
<<<<<<< HEAD
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `main_doctor_id` int(11) DEFAULT NULL,
  `operating_doctor_id` int(11) DEFAULT NULL,
  `operation_notes` text DEFAULT NULL,
  `operation_date` date DEFAULT NULL,
  `operation_time` time DEFAULT NULL,
  `operation_status` enum('Not Scheduled','Scheduled','In Progress','Completed','Cancelled') DEFAULT 'Not Scheduled'
=======
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admissiontb`
--

<<<<<<< HEAD
INSERT INTO `admissiontb` (`pid`, `fname`, `lname`, `gender`, `email`, `contact`, `password`, `admission_date`, `status`, `age`, `address`, `blood_group`, `medical_history`, `allergies`, `assigned_doctor`, `room_number`, `created_by`, `reason`, `created_at`, `updated_at`, `main_doctor_id`, `operating_doctor_id`, `operation_notes`, `operation_date`, `operation_time`, `operation_status`) VALUES
(8, 'last', 'na', 'Male', 'Pmadridano@gmail.com', '09940213443', '$2y$10$ZUSbKAS7Xc.g33Ib5dFtBekunbYKIUHzYNAt.tC5pRQM1kGR68VnO', '2025-10-09', 'Discharged', 20, 'Km6 Upper Balulang', '', NULL, NULL, 'pawix_12', '202', 'nurse', 'back pain', '2025-10-09 03:18:06', '2025-10-09 17:22:51', NULL, NULL, NULL, NULL, NULL, 'Not Scheduled'),
(11, 'Insure', 'inurse', 'Male', 'Insu@gmail.com', '09940213443', '$2y$10$KIOzACrapi5l5Ov8.SIwkujl8lQukaxjo/V.wuuSj8KyKPnMuL1MG', '2025-10-09', 'Discharged', 20, 'Km6 Upper Balulang', '', NULL, NULL, 'pawix_12', '101', 'nurse', 'Test insurance', '2025-10-09 11:52:28', '2025-10-09 17:23:52', NULL, NULL, NULL, NULL, NULL, 'Not Scheduled'),
(13, 'p', 'inurse', 'Male', 'Philhealth@gmail.com', '09940213443', '$2y$10$V.X/yB75.aNg3i6AIjRUueLcfXcyuWvlVz0T1UM9ak2jqMbdLAzdu', '2025-10-09', 'Discharged', 20, 'Km6 Upper Balulang', '', NULL, NULL, 'pawix_12', '402', 'nurse', 'Phil@123', '2025-10-09 12:27:07', '2025-10-09 17:22:57', NULL, NULL, NULL, NULL, NULL, 'Not Scheduled'),
(14, 'Insurance1', 'n', 'Male', 'In@gmail.com', '09940213443', '$2y$10$ZreCkpE/x6In9PnLWQOgKusZBTJxglA9VZ1bnvIxIvIW4jUb.Aqvq', '2025-10-09', 'Discharged', 20, 'Km6 Upper Balulang', '', NULL, NULL, 'pawix_12', '401', 'nurse', 'In@12345', '2025-10-09 12:33:52', '2025-10-09 17:15:07', NULL, NULL, NULL, NULL, NULL, 'Not Scheduled'),
(16, 'qweqwe', 'eqwewq', 'Male', 'qweq@qwe.com', '09123456789', '$2y$10$EARO85.y4IdB7o.wl6BNauGbxpWLnAy3YluQQi7Gt/XvBZRsArMHu', '2025-10-09', 'Discharged', 21, 'qweqwe', '', NULL, NULL, 'pawix_12', '102', 'nurse', 'qweqwe', '2025-10-09 14:05:00', '2025-10-09 17:23:02', NULL, NULL, NULL, NULL, NULL, 'Not Scheduled'),
(17, 'Thursday', 'alas 10', 'Male', 'Thursday@gmail.com', '09999999991', '$2y$10$KcMdFXlBM1neybZ9GnPn9u0bgpoAVA6UVmZlDDtmAvmxtZfVBSDBW', '2025-10-09', 'Discharged', 60, 'Km6 Upper Balulang', '', NULL, NULL, 'pawix_12', '401', 'nurse', 'Thursday', '2025-10-09 17:19:36', '2025-10-09 17:27:47', NULL, NULL, NULL, NULL, NULL, 'Not Scheduled'),
(18, 'Test', 'asd', 'Male', 'paolo12@gmail.com', '09564654456', '$2y$10$UeT0MH42BxaLgO6jZ2QhheAcouQTJepm.BoaiQkILLdmFi9jnPpe2', '2025-10-14', 'Admitted', 40, 'Km6 Upper Balulang', '', NULL, NULL, 'pawix_12', '301', 'nurse', 'qweqwe', '2025-10-14 12:48:47', '2025-10-14 12:48:47', NULL, NULL, NULL, NULL, NULL, 'Not Scheduled');

-- --------------------------------------------------------

--
-- Table structure for table `adverse_reactions`
--

CREATE TABLE `adverse_reactions` (
  `id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `pharmacist_username` varchar(50) NOT NULL,
  `medication_name` varchar(100) NOT NULL,
  `reaction_date` date NOT NULL,
  `reaction_time` time NOT NULL,
  `severity` varchar(20) NOT NULL,
  `symptoms` text NOT NULL,
  `action_taken` text NOT NULL,
  `reported_to_doctor` tinyint(1) DEFAULT 0,
  `doctor_notified` varchar(50) DEFAULT NULL,
  `notification_date` datetime DEFAULT NULL,
  `follow_up_required` tinyint(1) DEFAULT 0,
  `follow_up_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
=======
INSERT INTO `admissiontb` (`pid`, `fname`, `lname`, `gender`, `email`, `contact`, `password`, `admission_date`, `status`, `age`, `address`, `blood_group`, `medical_history`, `allergies`, `assigned_doctor`, `room_number`, `created_by`, `reason`, `created_at`, `updated_at`) VALUES
(8, 'last', 'na', 'Male', 'Pmadridano@gmail.com', '09940213443', '$2y$10$ZUSbKAS7Xc.g33Ib5dFtBekunbYKIUHzYNAt.tC5pRQM1kGR68VnO', '2025-10-09', 'Discharged', 20, 'Km6 Upper Balulang', '', NULL, NULL, 'pawix_12', '202', 'nurse', 'back pain', '2025-10-09 03:18:06', '2025-10-09 17:22:51'),
(11, 'Insure', 'inurse', 'Male', 'Insu@gmail.com', '09940213443', '$2y$10$KIOzACrapi5l5Ov8.SIwkujl8lQukaxjo/V.wuuSj8KyKPnMuL1MG', '2025-10-09', 'Discharged', 20, 'Km6 Upper Balulang', '', NULL, NULL, 'pawix_12', '101', 'nurse', 'Test insurance', '2025-10-09 11:52:28', '2025-10-09 17:23:52'),
(13, 'p', 'inurse', 'Male', 'Philhealth@gmail.com', '09940213443', '$2y$10$V.X/yB75.aNg3i6AIjRUueLcfXcyuWvlVz0T1UM9ak2jqMbdLAzdu', '2025-10-09', 'Discharged', 20, 'Km6 Upper Balulang', '', NULL, NULL, 'pawix_12', '402', 'nurse', 'Phil@123', '2025-10-09 12:27:07', '2025-10-09 17:22:57'),
(14, 'Insurance1', 'n', 'Male', 'In@gmail.com', '09940213443', '$2y$10$ZreCkpE/x6In9PnLWQOgKusZBTJxglA9VZ1bnvIxIvIW4jUb.Aqvq', '2025-10-09', 'Discharged', 20, 'Km6 Upper Balulang', '', NULL, NULL, 'pawix_12', '401', 'nurse', 'In@12345', '2025-10-09 12:33:52', '2025-10-09 17:15:07'),
(16, 'qweqwe', 'eqwewq', 'Male', 'qweq@qwe.com', '09123456789', '$2y$10$EARO85.y4IdB7o.wl6BNauGbxpWLnAy3YluQQi7Gt/XvBZRsArMHu', '2025-10-09', 'Discharged', 21, 'qweqwe', '', NULL, NULL, 'pawix_12', '102', 'nurse', 'qweqwe', '2025-10-09 14:05:00', '2025-10-09 17:23:02'),
(17, 'Thursday', 'alas 10', 'Male', 'Thursday@gmail.com', '09999999991', '$2y$10$KcMdFXlBM1neybZ9GnPn9u0bgpoAVA6UVmZlDDtmAvmxtZfVBSDBW', '2025-10-09', 'Discharged', 60, 'Km6 Upper Balulang', '', NULL, NULL, 'pawix_12', '401', 'nurse', 'Thursday', '2025-10-09 17:19:36', '2025-10-09 17:27:47');
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516

-- --------------------------------------------------------

--
-- Table structure for table `billtb`
--

CREATE TABLE `billtb` (
<<<<<<< HEAD
  `id` int(11) NOT NULL,
=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
  `pid` int(11) NOT NULL,
  `consultation_fees` decimal(10,2) NOT NULL DEFAULT 0.00,
  `lab_fees` decimal(10,2) NOT NULL DEFAULT 0.00,
  `medicine_fees` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'Unpaid',
  `service_charges` decimal(10,2) DEFAULT 0.00,
  `room_charges` decimal(10,2) DEFAULT 0.00,
  `other_charges` decimal(10,2) DEFAULT 0.00,
  `discount` decimal(10,2) DEFAULT 0.00,
  `payment_date` date DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `invoice_generated` tinyint(1) DEFAULT 0,
  `receipt_generated` tinyint(1) DEFAULT 0,
  `insurance_covered` decimal(10,2) DEFAULT 0.00,
<<<<<<< HEAD
  `patient_payable` decimal(10,2) DEFAULT 0.00,
  `cashier_id` int(11) DEFAULT NULL,
  `payment_status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `payment_time` time DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT 0.00
=======
  `patient_payable` decimal(10,2) DEFAULT 0.00
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `billtb`
--

<<<<<<< HEAD
INSERT INTO `billtb` (`id`, `pid`, `consultation_fees`, `lab_fees`, `medicine_fees`, `total`, `status`, `service_charges`, `room_charges`, `other_charges`, `discount`, `payment_date`, `payment_method`, `invoice_generated`, `receipt_generated`, `insurance_covered`, `patient_payable`, `cashier_id`, `payment_status`, `payment_time`, `amount_paid`) VALUES
(1, 8, 800.00, 400.00, 155.00, 2505.00, 'Paid', 600.00, 550.00, 0.00, 0.00, NULL, NULL, 0, 1, 0.00, 2505.00, NULL, 'Pending', NULL, 0.00),
(2, 11, 800.00, 0.00, 0.00, 1050.00, 'Unpaid', 0.00, 250.00, 0.00, 0.00, NULL, NULL, 0, 0, 0.00, 1050.00, NULL, 'Pending', NULL, 0.00),
(3, 13, 800.00, 0.00, 0.00, 1200.00, 'Unpaid', 0.00, 400.00, 0.00, 0.00, NULL, NULL, 0, 0, 600.00, 600.00, NULL, 'Pending', NULL, 0.00),
(4, 14, 800.00, 300.00, 1513.50, 4013.50, 'Paid', 1000.00, 400.00, 0.00, 0.00, NULL, NULL, 0, 0, 180.00, 1020.00, NULL, 'Pending', NULL, 0.00),
(5, 16, 800.00, 0.00, 0.00, 1050.00, 'Paid', 0.00, 250.00, 0.00, 0.00, '2025-10-14', 'Cash', 0, 0, 0.00, 1050.00, 2, 'Approved', '16:40:46', 1050.00),
(6, 17, 800.00, 500.00, 35.00, 2235.00, 'Paid', 500.00, 400.00, 0.00, 0.00, NULL, NULL, 0, 0, 600.00, 600.00, NULL, 'Pending', NULL, 0.00),
(7, 18, 800.00, 0.00, 0.00, 1300.00, 'Unpaid', 0.00, 500.00, 0.00, 0.00, NULL, NULL, 0, 0, 130.00, 1170.00, NULL, 'Pending', NULL, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `cashiertb`
--

CREATE TABLE `cashiertb` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cashiertb`
--

INSERT INTO `cashiertb` (`id`, `username`, `password`, `fname`, `lname`, `email`, `contact`, `created_by`, `status`, `created_at`) VALUES
(1, 'cashier1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', 'cashier@madridano.com', '09999999999', 'admin', 'Active', '2025-10-14 07:22:21'),
(2, 'cashier2', '$2y$10$dRMNUDsFxpfBsV1IY0DDp.11L96Ckit3EV3xkcE.mGHd4XLnbvLz6', 'Cashier', 'Hospital', 'pmadridano2@gmail.com', '09456587115', 'admin', 'Active', '2025-10-14 08:25:24');

-- --------------------------------------------------------

--
-- Table structure for table `compounding_records`
--

CREATE TABLE `compounding_records` (
  `id` int(11) NOT NULL,
  `pid` int(11) DEFAULT NULL,
  `pharmacist_username` varchar(50) NOT NULL,
  `compounding_date` date NOT NULL,
  `compounding_time` time NOT NULL,
  `compound_name` varchar(100) NOT NULL,
  `ingredients` text NOT NULL,
  `quantities` text NOT NULL,
  `instructions` text NOT NULL,
  `quality_check_passed` tinyint(1) DEFAULT 0,
  `quality_checked_by` varchar(50) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `counseling_notes`
--

CREATE TABLE `counseling_notes` (
  `id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `pharmacist_username` varchar(50) NOT NULL,
  `counseling_date` date NOT NULL,
  `counseling_time` time NOT NULL,
  `medication_name` varchar(100) NOT NULL,
  `counseling_type` varchar(50) NOT NULL,
  `notes` text NOT NULL,
  `patient_understanding` varchar(20) DEFAULT 'Not Assessed',
  `follow_up_required` tinyint(1) DEFAULT 0,
  `follow_up_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
=======
INSERT INTO `billtb` (`pid`, `consultation_fees`, `lab_fees`, `medicine_fees`, `total`, `status`, `service_charges`, `room_charges`, `other_charges`, `discount`, `payment_date`, `payment_method`, `invoice_generated`, `receipt_generated`, `insurance_covered`, `patient_payable`) VALUES
(8, 800.00, 400.00, 155.00, 2505.00, 'Paid', 600.00, 550.00, 0.00, 0.00, NULL, NULL, 0, 1, 0.00, 2505.00),
(11, 800.00, 0.00, 0.00, 1050.00, 'Unpaid', 0.00, 250.00, 0.00, 0.00, NULL, NULL, 0, 0, 0.00, 1050.00),
(13, 800.00, 0.00, 0.00, 1200.00, 'Unpaid', 0.00, 400.00, 0.00, 0.00, NULL, NULL, 0, 0, 600.00, 600.00),
(14, 800.00, 300.00, 1513.50, 4013.50, 'Paid', 1000.00, 400.00, 0.00, 0.00, NULL, NULL, 0, 0, 180.00, 1020.00),
(16, 800.00, 0.00, 0.00, 1050.00, 'Unpaid', 0.00, 250.00, 0.00, 0.00, NULL, NULL, 0, 0, 0.00, 1050.00),
(17, 800.00, 500.00, 35.00, 2235.00, 'Paid', 500.00, 400.00, 0.00, 0.00, NULL, NULL, 0, 0, 600.00, 600.00);
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516

-- --------------------------------------------------------

--
-- Table structure for table `diagnosticstb`
--

CREATE TABLE `diagnosticstb` (
  `id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `doctor_name` varchar(100) NOT NULL,
  `symptoms` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `vital_signs` text DEFAULT NULL,
  `physical_examination` text DEFAULT NULL,
  `medical_history` text DEFAULT NULL,
  `diagnostic_tests_ordered` text DEFAULT NULL,
  `treatment_plan` text DEFAULT NULL,
  `created_date` date NOT NULL,
  `created_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diagnosticstb`
--

INSERT INTO `diagnosticstb` (`id`, `pid`, `doctor_name`, `symptoms`, `diagnosis`, `vital_signs`, `physical_examination`, `medical_history`, `diagnostic_tests_ordered`, `treatment_plan`, `created_date`, `created_time`) VALUES
(7, 8, 'pawix_12', 'Red eyes', 'Joint disorders', '97.7 F', '....', 'None', 'wala', '3 weeks ', '2025-10-09', '11:23:38'),
(9, 14, 'pawix_12', 'weqe', 'qeQWeq', 'QWeQ', 'QEqwe', 'qWEQ', 'QWEQE', 'qweqasd', '2025-10-10', '00:29:55'),
(10, 17, 'pawix_12', 'dasda', 'sadas', 'asdas', 'asdasd', 'asdas', 'asdas', 'asdas', '2025-10-10', '01:24:14');

-- --------------------------------------------------------

--
-- Table structure for table `dischargetb`
--

CREATE TABLE `dischargetb` (
  `pid` int(11) NOT NULL,
  `discharge_date` date DEFAULT NULL,
  `approved_by_admin` tinyint(1) NOT NULL DEFAULT 0,
  `discharge_time` time DEFAULT NULL,
  `discharge_summary` text DEFAULT NULL,
  `final_diagnosis` text DEFAULT NULL,
  `discharge_medications` text DEFAULT NULL,
  `follow_up_instructions` text DEFAULT NULL,
  `discharged_by` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `dischargetb`
--

INSERT INTO `dischargetb` (`pid`, `discharge_date`, `approved_by_admin`, `discharge_time`, `discharge_summary`, `final_diagnosis`, `discharge_medications`, `follow_up_instructions`, `discharged_by`) VALUES
(8, '2025-10-09', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(11, '2025-10-09', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(13, '2025-10-09', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(14, '2025-10-09', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(16, '2025-10-09', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(17, '2025-10-09', 1, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `doctortb`
--

CREATE TABLE `doctortb` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact` varchar(15) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `qualification` varchar(200) NOT NULL,
  `experience_years` int(11) DEFAULT 0,
  `consultation_fee` decimal(10,2) DEFAULT 100.00,
  `status` varchar(20) DEFAULT 'Active',
  `created_date` date DEFAULT curdate(),
  `created_by` varchar(50) DEFAULT 'admin',
  `two_factor_enabled` tinyint(4) DEFAULT 0,
  `two_factor_code` varchar(10) DEFAULT NULL,
  `two_factor_expires` datetime DEFAULT NULL,
<<<<<<< HEAD
  `backup_codes` text DEFAULT NULL,
  `is_operating_doctor` tinyint(1) DEFAULT 0
=======
  `backup_codes` text DEFAULT NULL
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctortb`
--

<<<<<<< HEAD
INSERT INTO `doctortb` (`id`, `username`, `password`, `fname`, `lname`, `email`, `contact`, `specialization`, `qualification`, `experience_years`, `consultation_fee`, `status`, `created_date`, `created_by`, `two_factor_enabled`, `two_factor_code`, `two_factor_expires`, `backup_codes`, `is_operating_doctor`) VALUES
(2, 'dr_johnson', 'doctor123', 'Sarah', 'Johnson', 'sarah.johnson@hospital.com', '1234567891', 'Pediatrics', 'MD Pediatrics', 8, 150.00, 'Active', '2025-09-17', 'admin', 1, NULL, NULL, NULL, 0),
(3, 'dr_williams', 'doctor123', 'Michael', 'Williams', 'michael.williams@hospital.com', '1234567892', 'General Medicine', 'MBBS', 5, 100.00, 'On Leave', '2025-09-17', 'admin', 0, NULL, NULL, NULL, 0),
(7, 'pawix_12', 'paolo123', 'Gabriel', 'Madridano', 'pmadridano2@gmail.com', '09940213443', 'Cardiology', 'Ok', 13, 800.00, 'Active', '2025-09-17', 'admin', 1, NULL, NULL, NULL, 0),
(10, 'operating_doc', 'opdoc123', 'Operating', 'Doctor', 'opdoc@hospital.com', '1234567890', 'Surgery', 'MD Surgery', 10, 500.00, 'Active', '2025-10-14', 'admin', 0, NULL, NULL, NULL, 1);
=======
INSERT INTO `doctortb` (`id`, `username`, `password`, `fname`, `lname`, `email`, `contact`, `specialization`, `qualification`, `experience_years`, `consultation_fee`, `status`, `created_date`, `created_by`, `two_factor_enabled`, `two_factor_code`, `two_factor_expires`, `backup_codes`) VALUES
(2, 'dr_johnson', 'doctor123', 'Sarah', 'Johnson', 'sarah.johnson@hospital.com', '1234567891', 'Pediatrics', 'MD Pediatrics', 8, 150.00, 'Active', '2025-09-17', 'admin', 1, NULL, NULL, NULL),
(3, 'dr_williams', 'doctor123', 'Michael', 'Williams', 'michael.williams@hospital.com', '1234567892', 'General Medicine', 'MBBS', 5, 100.00, 'On Leave', '2025-09-17', 'admin', 0, NULL, NULL, NULL),
(7, 'pawix_12', 'paolo123', 'Gabriel', 'Madridano', 'pmadridano2@gmail.com', '09940213443', 'Cardiology', 'Ok', 13, 800.00, 'Active', '2025-09-17', 'admin', 1, NULL, NULL, NULL);
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516

-- --------------------------------------------------------

--
-- Table structure for table `emergency_access_logs`
--

CREATE TABLE `emergency_access_logs` (
  `id` int(11) NOT NULL,
  `staff_username` varchar(50) NOT NULL,
  `staff_role` varchar(20) NOT NULL,
  `reason` text NOT NULL,
  `contact_info` varchar(100) NOT NULL,
  `additional_info` text DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `handled_by` varchar(50) DEFAULT NULL,
  `handled_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `one_time_token` varchar(64) DEFAULT NULL,
  `token_expires` datetime DEFAULT NULL,
  `token_used` tinyint(1) DEFAULT 0,
  `auto_login_used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `emergency_access_logs`
--

INSERT INTO `emergency_access_logs` (`id`, `staff_username`, `staff_role`, `reason`, `contact_info`, `additional_info`, `ip_address`, `status`, `handled_by`, `handled_at`, `created_at`, `one_time_token`, `token_expires`, `token_used`, `auto_login_used`) VALUES
(1, 'lab1', 'lab', 'Lost access to email', '0994021344', '', '::1', 'approved', 'admin', '2025-10-08 13:15:27', '2025-10-08 05:13:44', NULL, NULL, 0, 1),
(2, 'lab2', 'lab', 'Urgent patient care required', '0994021344', '', '::1', 'approved', 'admin', '2025-10-08 13:30:35', '2025-10-08 05:29:05', '485fd95fa37f33435e6ccb951623fecc7ea808a31b8737c2403c4867b18f8a73', '2025-10-08 08:30:35', 0, 1),
(3, 'lab2', 'lab', 'Not receiving verification codes', '0994021344', '', '::1', 'denied', 'admin', '2025-10-08 13:51:29', '2025-10-08 05:34:20', NULL, NULL, 0, 0),
(4, 'nurse1', 'nurse', 'Not receiving verification codes', '0994021344', '', '::1', 'approved', 'admin', '2025-10-08 13:44:34', '2025-10-08 05:44:19', '590739cd321c6a652fe74d13b969b69f631cadb2c592c9b9b26719eed61e65b9', '2025-10-08 08:44:34', 0, 1),
(5, 'pawix_12', 'doctor', 'Not receiving verification codes', '09940213443', '', '::1', 'approved', 'admin', '2025-10-08 22:30:39', '2025-10-08 14:30:07', '60de96133e1b28d6c4470696a1a950182d0bb15e83fef81916aa92a7feac015a', '2025-10-08 17:30:39', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `insurance_companiestb`
--

CREATE TABLE `insurance_companiestb` (
  `insurance_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `contact_person` varchar(100) NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `policy_details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `insurance_companiestb`
--

INSERT INTO `insurance_companiestb` (`insurance_id`, `company_name`, `contact_person`, `contact_number`, `email`, `address`, `policy_details`) VALUES
(1, 'PhilHealth', 'Juan Dela Cruz', '09123456789', 'info@philhealth.gov.ph', 'Manila, Philippines', 'Government health insurance covering basic medical services'),
(2, 'Maxicare', 'Maria Santos', '09187654321', 'support@maxicare.com.ph', 'Makati, Philippines', 'Private health insurance with comprehensive coverage'),
(3, 'Medicard', 'Pedro Reyes', '09234567890', 'contact@medicard.com.ph', 'Quezon City, Philippines', 'Affordable health insurance plans');

-- --------------------------------------------------------

--
<<<<<<< HEAD
-- Table structure for table `inventory_logs`
--

CREATE TABLE `inventory_logs` (
  `id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `action_type` varchar(20) NOT NULL,
  `quantity_changed` int(11) NOT NULL,
  `previous_quantity` int(11) NOT NULL,
  `new_quantity` int(11) NOT NULL,
  `performed_by` varchar(50) NOT NULL,
  `reason` text DEFAULT NULL,
  `log_date` date NOT NULL,
  `log_time` time NOT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `supplier_info` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
-- Table structure for table `invoicetb`
--

CREATE TABLE `invoicetb` (
  `id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `generated_date` date NOT NULL,
  `generated_time` time NOT NULL,
  `generated_by` varchar(100) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(20) DEFAULT 'Generated'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoicetb`
--

INSERT INTO `invoicetb` (`id`, `pid`, `invoice_number`, `generated_date`, `generated_time`, `generated_by`, `total_amount`, `status`) VALUES
(7, 8, 'INV-8-20251009-5763', '2025-10-09', '11:32:57', 'Patient Request', 2505.00, 'Approved'),
(8, 14, 'INV-14-20251009-4856', '2025-10-10', '00:56:05', 'Patient Request', 3411.48, 'Approved'),
(9, 17, 'INV-17-20251009-1230', '2025-10-10', '01:25:44', 'Patient Request', 1117.50, 'Approved');

-- --------------------------------------------------------

--
-- Table structure for table `labtb`
--

CREATE TABLE `labtb` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `fname` varchar(50) DEFAULT '',
  `lname` varchar(50) DEFAULT '',
  `contact` varchar(15) DEFAULT '',
  `department` varchar(100) DEFAULT 'Laboratory',
  `status` varchar(20) DEFAULT 'Active',
  `created_date` date DEFAULT curdate(),
  `created_by` varchar(50) DEFAULT 'admin',
  `two_factor_enabled` tinyint(4) DEFAULT 0,
  `two_factor_code` varchar(10) DEFAULT NULL,
  `two_factor_expires` datetime DEFAULT NULL,
  `backup_codes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `labtb`
--

INSERT INTO `labtb` (`id`, `username`, `password`, `email`, `fname`, `lname`, `contact`, `department`, `status`, `created_date`, `created_by`, `two_factor_enabled`, `two_factor_code`, `two_factor_expires`, `backup_codes`) VALUES
(1, 'lab1', 'lab123', 'pmadridano2@gmail.com', 'Robert', 'Miller', '1234567896', 'Pathology', 'Active', '2025-09-17', 'admin', 1, '747716', '2025-10-09 19:34:59', NULL),
(2, 'lab2', 'lab123', 'pmadridano2@gmail.com', 'Amanda', 'Garcia', '1234567897', 'Radiology', 'Active', '2025-09-17', 'admin', 0, '879534', '2025-10-08 07:40:45', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `labtesttb`
--

CREATE TABLE `labtesttb` (
  `id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `test_name` varchar(100) NOT NULL,
  `suggested_by_doctor` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `scheduled_date` date DEFAULT NULL,
  `completed_date` date DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `requested_date` date DEFAULT NULL,
  `requested_time` time DEFAULT NULL,
  `lab_notes` text DEFAULT NULL,
  `results` text DEFAULT NULL,
  `priority` varchar(20) DEFAULT 'Normal'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `labtesttb`
--

INSERT INTO `labtesttb` (`id`, `pid`, `test_name`, `suggested_by_doctor`, `status`, `scheduled_date`, `completed_date`, `price`, `requested_date`, `requested_time`, `lab_notes`, `results`, `priority`) VALUES
(4, 8, 'CT Scan', 'pawix_12', 'Completed', '2025-10-09', '2025-10-09', 400.00, '2025-10-09', '11:23:57', 'good', 'good', 'Emergency'),
(6, 14, 'Urine Analysis', 'pawix_12', 'Completed', '2025-10-10', '2025-10-09', 300.00, '2025-10-10', '00:30:20', 'ewq', 'eqw', 'Emergency'),
(7, 17, 'MRI Brain', 'pawix_12', 'Completed', '2025-10-10', '2025-10-09', 500.00, '2025-10-10', '01:24:23', 'dsadas', 'asdasdas', 'Emergency');

-- --------------------------------------------------------

--
-- Table structure for table `medicinetb`
--

CREATE TABLE `medicinetb` (
  `id` int(11) NOT NULL,
  `medicine_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `added_by_nurse` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `dosage` text DEFAULT NULL,
  `frequency` text DEFAULT NULL,
  `duration` text DEFAULT NULL,
  `medicine_type` varchar(50) DEFAULT 'oral'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `medicinetb`
--

INSERT INTO `medicinetb` (`id`, `medicine_name`, `quantity`, `added_by_nurse`, `price`, `dosage`, `frequency`, `duration`, `medicine_type`) VALUES
(1, 'Paracetamol', 79, 'nurse1', 15.50, '500mg', 'Twice a day', '', 'oral'),
(2, 'Ibuprofen', 50, 'nurse1', 18.75, '200mg', 'Three times a day', '7 days', 'oral'),
(3, 'Amoxicillin', 75, 'nurse2', 45.00, '250mg', 'Twice a day', '10 days', 'oral'),
(4, 'Aspirin', 190, 'nurse2', 12.25, '100mg', 'Once a day', '3 days', 'oral'),
(5, 'Insulin', 1, 'nurse1', 285.00, '10 units', 'Once a day', '30 days', 'oral'),
(6, 'Vitamin D', 144, 'nurse2', 8.50, 'One tablet', 'Once a day', '30 days', 'oral'),
(7, 'Antihistamine', 80, 'nurse1', 22.80, '10mg', 'Once a day', '7 days', 'oral'),
(8, 'Cough Syrup', 60, 'nurse2', 65.00, '10ml', 'Three times a day', '5 days', 'oral'),
(9, 'Bandages', 479, 'nurse1', 35.00, 'Apply as needed', 'As needed', 'N/A', 'other'),
(10, 'Antiseptic Cream', 9, 'nurse2', 42.50, 'Apply thin layer', 'Twice a day', '7 days', 'oral');

-- --------------------------------------------------------

--
<<<<<<< HEAD
-- Table structure for table `monitoring_reports`
--

CREATE TABLE `monitoring_reports` (
  `id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `pharmacist_username` varchar(50) NOT NULL,
  `medication_name` varchar(100) NOT NULL,
  `monitoring_type` varchar(50) NOT NULL,
  `report_date` date NOT NULL,
  `report_time` time NOT NULL,
  `effectiveness_rating` int(11) DEFAULT NULL,
  `side_effects` text DEFAULT NULL,
  `adherence_level` varchar(20) DEFAULT 'Not Assessed',
  `recommendations` text DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
-- Table structure for table `nursetb`
--

CREATE TABLE `nursetb` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `fname` varchar(50) DEFAULT '',
  `lname` varchar(50) DEFAULT '',
  `contact` varchar(15) DEFAULT '',
  `department` varchar(100) DEFAULT 'General',
  `shift` varchar(20) DEFAULT 'Day',
  `status` varchar(20) DEFAULT 'Active',
  `created_date` date DEFAULT curdate(),
  `created_by` varchar(50) DEFAULT 'admin',
  `two_factor_enabled` tinyint(4) DEFAULT 0,
  `two_factor_code` varchar(10) DEFAULT NULL,
  `two_factor_expires` datetime DEFAULT NULL,
  `backup_codes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `nursetb`
--

INSERT INTO `nursetb` (`id`, `username`, `password`, `email`, `fname`, `lname`, `contact`, `department`, `shift`, `status`, `created_date`, `created_by`, `two_factor_enabled`, `two_factor_code`, `two_factor_expires`, `backup_codes`) VALUES
(1, 'nurse1', 'nurse123', 'pmadridano2@gmail.com', 'Mary', 'Wilson', '1234567893', 'General Ward', 'Day', 'Active', '2025-09-17', 'admin', 1, NULL, NULL, NULL),
<<<<<<< HEAD
(2, 'nurse2', 'nurse123', 'pmadridano2@gmail.com', 'Lisa', 'Brown', '1234567894', 'ICU', 'Night', 'Active', '2025-09-17', 'admin', 0, NULL, NULL, NULL),
=======
(2, 'nurse2', 'nurse123', 'pmadridano2@gmail.com', 'Lisa', 'Brown', '1234567894', 'ICU', 'Night', 'Active', '2025-09-17', 'admin', 1, '057674', '2025-10-09 19:28:04', NULL),
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
(3, 'nurse3', 'nurse123', 'nurse3@hospital.com', 'Jennifer', 'Davis', '1234567895', 'Emergency', 'Day', 'Active', '2025-09-17', 'admin', 1, '399159', '2025-10-09 15:39:08', NULL);

-- --------------------------------------------------------

--
<<<<<<< HEAD
-- Table structure for table `operations`
--

CREATE TABLE `operations` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `requested_by_doctor_id` int(11) NOT NULL,
  `operating_doctor_id` int(11) DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled','pending') DEFAULT 'pending',
  `scheduled_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
-- Table structure for table `patient_chargstb`
--

CREATE TABLE `patient_chargstb` (
  `id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `added_by` varchar(100) NOT NULL,
  `added_date` date NOT NULL,
  `added_time` time NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_chargstb`
--

INSERT INTO `patient_chargstb` (`id`, `pid`, `service_id`, `quantity`, `unit_price`, `total_price`, `added_by`, `added_date`, `added_time`, `description`) VALUES
(3, 8, 3, 2, 300.00, 600.00, 'pawix_12', '2025-10-09', '11:24:12', 'Emergency'),
(5, 14, 5, 4, 250.00, 1000.00, 'pawix_12', '2025-10-09', '20:35:20', ''),
(6, 17, 8, 1, 500.00, 500.00, 'pawix_12', '2025-10-10', '01:24:29', '');

-- --------------------------------------------------------

--
-- Table structure for table `patient_insurancetb`
--

CREATE TABLE `patient_insurancetb` (
  `patient_insurance_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `insurance_id` int(11) NOT NULL,
  `policy_number` varchar(100) NOT NULL,
  `coverage_percent` decimal(5,2) NOT NULL CHECK (`coverage_percent` >= 0 and `coverage_percent` <= 100),
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_insurancetb`
--

INSERT INTO `patient_insurancetb` (`patient_insurance_id`, `patient_id`, `insurance_id`, `policy_number`, `coverage_percent`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(9, 13, 1, 'POL7258', 50.00, '2025-10-09', NULL, 'active', '2025-10-09 12:27:07', '2025-10-09 12:27:07'),
(10, 14, 3, 'POL7789', 15.00, '2025-10-09', NULL, 'active', '2025-10-09 12:33:52', '2025-10-09 12:33:52'),
<<<<<<< HEAD
(11, 17, 1, 'POL7122', 50.00, '2025-10-10', NULL, 'active', '2025-10-09 17:19:36', '2025-10-09 17:19:36'),
(12, 18, 1, 'POL72222', 10.00, '2025-10-14', NULL, 'active', '2025-10-14 12:48:47', '2025-10-14 12:48:47');
=======
(11, 17, 1, 'POL7122', 50.00, '2025-10-10', NULL, 'active', '2025-10-09 17:19:36', '2025-10-09 17:19:36');
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516

-- --------------------------------------------------------

--
-- Table structure for table `patient_roundstb`
--

CREATE TABLE `patient_roundstb` (
  `id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `nurse_username` varchar(100) NOT NULL,
  `round_date` date NOT NULL,
  `round_time` time NOT NULL,
  `vital_signs` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_roundstb`
--

INSERT INTO `patient_roundstb` (`id`, `pid`, `nurse_username`, `round_date`, `round_time`, `vital_signs`, `notes`, `status`, `created_at`) VALUES
(5, 16, 'nurse1', '2025-10-09', '19:16:00', 'qewq', '\nUpdate: sada', 'Completed', '2025-10-09 17:16:41');

-- --------------------------------------------------------

--
<<<<<<< HEAD
-- Table structure for table `patient_transferstb`
--

CREATE TABLE `patient_transferstb` (
  `id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `from_doctor_id` int(11) DEFAULT NULL,
  `to_doctor_id` int(11) NOT NULL,
  `transfer_reason` text DEFAULT NULL,
  `transfer_date` date DEFAULT NULL,
  `transfer_time` time DEFAULT NULL,
  `transferred_by` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
-- Table structure for table `patreg`
--

CREATE TABLE `patreg` (
  `pid` int(11) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `email` varchar(50) NOT NULL,
  `contact` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `age` int(11) DEFAULT 0,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `patreg`
--

INSERT INTO `patreg` (`pid`, `fname`, `lname`, `gender`, `email`, `contact`, `password`, `age`, `address`) VALUES
(1, 'Test', 'insurance', 'Male', 'Insurance@gmail.com', '09123456789', '$2y$10$zwIcx92/idohTpqnvUnjwuzVi2Da32yFxvi29gfVPiSlBmLIqDInW', 20, 'Km6 Upper Balulang'),
(2, 'Test', 'insurance', 'Male', 'insurance@gmail.com', '09940213443', '$2y$10$aVE2omJZa9hTRJQyZjJniOqX57jbo/JkPOCnPX18sK3tTUKi2mlH6', 20, 'Km6 Upper Balulang'),
(3, 'Test', 'insurance', 'Male', 'insurance@gmail.com', '09940213443', '$2y$10$EVFWYgHR.btP5UzI7.rui.Q3En7.D6MDkCtafoGedP7yxHER96rWa', 20, 'Km6 Upper Balulang'),
(4, 'Insurance', 'ins', 'Female', 'insurance@gmail.com', '0994021344', '$2y$10$Fh6Q4ySCKCQOWJoNckgQ0.FtQUw1Z.y4UH89H70UVfbKH4ViYpTlG', 20, 'Km6 Upper Balulang'),
(5, 'Insurance', 'ins', 'Female', 'insurance@gmail.com', '0994021344', '$2y$10$yZV3UC.z/HAb4iHJKFb11ex9VKbzqDhE5yImgDaQ6tl5dxGqv9Ice', 20, 'Km6 Upper Balulang'),
(6, 'Ins ', 'insurance', 'Male', 'insurance@gmail.com', '09940213443', '$2y$10$uMuIhtPOM6QDI29PKBIRNOuT2c9cnD1ZEUT8y6gjcdD0K2.PNESe2', 20, 'Km6 Upper Balulang');

-- --------------------------------------------------------

--
-- Table structure for table `paymentstb`
--

CREATE TABLE `paymentstb` (
  `id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_time` time NOT NULL,
  `processed_by` varchar(100) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paymentstb`
--

INSERT INTO `paymentstb` (`id`, `pid`, `amount`, `payment_method`, `payment_date`, `payment_time`, `processed_by`, `transaction_id`, `notes`, `status`) VALUES
(7, 8, 2505.00, 'Cash', '2025-10-09', '11:47:17', 'Patient Self-Service', NULL, 'Payment request submitted by patient', 'Approved'),
(8, 14, 3411.00, 'Cash', '2025-10-10', '00:54:18', 'Patient Self-Service', NULL, 'Payment request submitted by patient', 'Pending'),
(9, 17, 1117.50, 'Cash', '2025-10-10', '01:26:16', 'Patient Self-Service', NULL, 'Payment request submitted by patient', 'Pending');

-- --------------------------------------------------------

--
<<<<<<< HEAD
-- Table structure for table `pharmacisttb`
--

CREATE TABLE `pharmacisttb` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contact` varchar(50) DEFAULT NULL,
  `fname` varchar(100) DEFAULT NULL,
  `lname` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pharmacisttb`
--

INSERT INTO `pharmacisttb` (`id`, `username`, `password`, `email`, `contact`, `fname`, `lname`, `created_at`) VALUES
(1, 'pharma_user', 'pharma123', 'pharma_user@hospital.com', '09171234567', 'Maria', 'Santos', '2025-10-16 07:27:34');

-- --------------------------------------------------------

--
=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
-- Table structure for table `prestb`
--

CREATE TABLE `prestb` (
  `id` int(11) NOT NULL,
  `doctor` varchar(255) NOT NULL,
  `pid` int(11) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `symptoms` text DEFAULT NULL,
  `allergy` varchar(255) DEFAULT NULL,
  `prescription` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `diagnosis_details` text DEFAULT NULL,
  `prescribed_medicines` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `dosage` text DEFAULT NULL,
  `frequency` text DEFAULT NULL,
<<<<<<< HEAD
  `duration` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `dispensed_by` varchar(50) DEFAULT NULL,
  `dispensed_date` datetime DEFAULT NULL,
  `given_by` varchar(50) DEFAULT NULL,
  `given_date` datetime DEFAULT NULL
=======
  `duration` text DEFAULT NULL
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prestb`
--

<<<<<<< HEAD
INSERT INTO `prestb` (`id`, `doctor`, `pid`, `fname`, `lname`, `symptoms`, `allergy`, `prescription`, `price`, `diagnosis_details`, `prescribed_medicines`, `created_at`, `dosage`, `frequency`, `duration`, `status`, `dispensed_by`, `dispensed_date`, `given_by`, `given_date`) VALUES
(1, 'pawix_12', 2, 'justin', 'nabunturan', NULL, 'Pollen', 'Amoxicillin, Aspirin, Antihistamine, Cough Syrup, Antiseptic Cream', 7.70, 'qwewq', 'Amoxicillin, Aspirin, Antihistamine, Cough Syrup, Antiseptic Cream', '2025-09-17 14:56:23', NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL),
(2, 'pawix_12', 2, 'justin', 'nabunturan', NULL, 'None', 'Amoxicillin', 1.20, 'asdasdas', 'Amoxicillin', '2025-09-17 14:57:54', NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL),
(3, 'pawix_12', 2, 'justin', 'nabunturan', '', 'Latex', 'Antihistamine', 0.60, 'qweqwe', 'Antihistamine', '2025-09-17 15:10:14', NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL),
(4, 'pawix_12', 2, 'justin', 'nabunturan', '', 'Latex', 'Antihistamine', 0.60, 'qweqwe', 'Antihistamine', '2025-09-17 15:10:20', NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL),
(5, 'pawix_12', 2, 'justin', 'nabunturan', '', 'Latex', 'Antihistamine', 0.60, 'qweqwe', 'Antihistamine', '2025-09-17 15:11:47', NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL),
(6, 'pawix_12', 3, 'lebron', 'james', 'wala', 'None', 'Bandages', 2.00, 'wala', 'Bandages', '2025-10-01 08:10:08', '', '', '', 'Pending', NULL, NULL, NULL, NULL),
(7, 'pawix_12', 3, 'lebron', 'james', 'weq', 'None', 'Insulin', 450.00, 'weqwe', 'Insulin', '2025-10-01 08:10:36', '', '', '', 'Pending', NULL, NULL, NULL, NULL),
(8, 'pawix_12', 5, 'test', '2', 'sakit ngipon', 'None', 'Vitamin D', 0.90, 'wala', 'Vitamin D', '2025-10-01 08:33:22', '', '', '', 'Pending', NULL, NULL, NULL, NULL),
(9, 'pawix_12', 8, 'last', 'na', '', 'Penicillin', 'Paracetamol', 155.00, '', 'Paracetamol', '2025-10-09 03:27:58', '500mg', '3 times a day', '7 days', 'Pending', NULL, NULL, NULL, NULL),
(10, 'pawix_12', 10, 'Ins ', 'insurance', '', 'Pollen', 'Aspirin', 122.50, '', 'Aspirin', '2025-10-09 11:13:12', '75mg', '1 time a day', '5 days', 'Pending', NULL, NULL, NULL, NULL),
(11, 'pawix_12', 14, 'Insurance1', 'n', '', 'None', 'Paracetamol, Vitamin D', 180.50, '', 'Paracetamol, Vitamin D', '2025-10-09 12:35:45', '500mg,1000 IU', '3 times a day,1 time a day', '5 days,10 days', 'Pending', NULL, NULL, NULL, NULL),
(12, 'pawix_12', 14, 'Insurance1', 'n', '', 'Penicillin', 'Paracetamol, Antiseptic Cream', 1333.00, '', 'Paracetamol, Antiseptic Cream', '2025-10-09 16:28:34', '500mg,200mg', '3 times a day,2 times a day', ',5 days', 'Pending', NULL, NULL, NULL, NULL),
(13, 'pawix_12', 17, 'Thursday', 'alas 10', '', 'None', 'Bandages', 35.00, '', 'Bandages', '2025-10-09 17:24:40', '', '', '', 'Pending', NULL, NULL, NULL, NULL);
=======
INSERT INTO `prestb` (`id`, `doctor`, `pid`, `fname`, `lname`, `symptoms`, `allergy`, `prescription`, `price`, `diagnosis_details`, `prescribed_medicines`, `created_at`, `dosage`, `frequency`, `duration`) VALUES
(1, 'pawix_12', 2, 'justin', 'nabunturan', NULL, 'Pollen', 'Amoxicillin, Aspirin, Antihistamine, Cough Syrup, Antiseptic Cream', 7.70, 'qwewq', 'Amoxicillin, Aspirin, Antihistamine, Cough Syrup, Antiseptic Cream', '2025-09-17 14:56:23', NULL, NULL, NULL),
(2, 'pawix_12', 2, 'justin', 'nabunturan', NULL, 'None', 'Amoxicillin', 1.20, 'asdasdas', 'Amoxicillin', '2025-09-17 14:57:54', NULL, NULL, NULL),
(3, 'pawix_12', 2, 'justin', 'nabunturan', '', 'Latex', 'Antihistamine', 0.60, 'qweqwe', 'Antihistamine', '2025-09-17 15:10:14', NULL, NULL, NULL),
(4, 'pawix_12', 2, 'justin', 'nabunturan', '', 'Latex', 'Antihistamine', 0.60, 'qweqwe', 'Antihistamine', '2025-09-17 15:10:20', NULL, NULL, NULL),
(5, 'pawix_12', 2, 'justin', 'nabunturan', '', 'Latex', 'Antihistamine', 0.60, 'qweqwe', 'Antihistamine', '2025-09-17 15:11:47', NULL, NULL, NULL),
(6, 'pawix_12', 3, 'lebron', 'james', 'wala', 'None', 'Bandages', 2.00, 'wala', 'Bandages', '2025-10-01 08:10:08', '', '', ''),
(7, 'pawix_12', 3, 'lebron', 'james', 'weq', 'None', 'Insulin', 450.00, 'weqwe', 'Insulin', '2025-10-01 08:10:36', '', '', ''),
(8, 'pawix_12', 5, 'test', '2', 'sakit ngipon', 'None', 'Vitamin D', 0.90, 'wala', 'Vitamin D', '2025-10-01 08:33:22', '', '', ''),
(9, 'pawix_12', 8, 'last', 'na', '', 'Penicillin', 'Paracetamol', 155.00, '', 'Paracetamol', '2025-10-09 03:27:58', '500mg', '3 times a day', '7 days'),
(10, 'pawix_12', 10, 'Ins ', 'insurance', '', 'Pollen', 'Aspirin', 122.50, '', 'Aspirin', '2025-10-09 11:13:12', '75mg', '1 time a day', '5 days'),
(11, 'pawix_12', 14, 'Insurance1', 'n', '', 'None', 'Paracetamol, Vitamin D', 180.50, '', 'Paracetamol, Vitamin D', '2025-10-09 12:35:45', '500mg,1000 IU', '3 times a day,1 time a day', '5 days,10 days'),
(12, 'pawix_12', 14, 'Insurance1', 'n', '', 'Penicillin', 'Paracetamol, Antiseptic Cream', 1333.00, '', 'Paracetamol, Antiseptic Cream', '2025-10-09 16:28:34', '500mg,200mg', '3 times a day,2 times a day', ',5 days'),
(13, 'pawix_12', 17, 'Thursday', 'alas 10', '', 'None', 'Bandages', 35.00, '', 'Bandages', '2025-10-09 17:24:40', '', '', '');
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516

-- --------------------------------------------------------

--
-- Table structure for table `servicestb`
--

CREATE TABLE `servicestb` (
  `id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `service_category` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `servicestb`
--

INSERT INTO `servicestb` (`id`, `service_name`, `service_category`, `price`, `description`, `status`) VALUES
(1, 'General Consultation', 'Consultation', 100.00, 'Basic doctor consultation', 'Active'),
(2, 'Specialist Consultation', 'Consultation', 200.00, 'Specialist doctor consultation', 'Active'),
(3, 'Emergency Consultation', 'Consultation', 300.00, 'Emergency room consultation', 'Active'),
(4, 'Follow-up Consultation', 'Consultation', 75.00, 'Follow-up visit consultation', 'Active'),
(5, 'Surgical Consultation', 'Consultation', 250.00, 'Pre-surgical consultation', 'Active'),
(6, 'Room Charges - General Ward', 'Accommodation', 50.00, 'Daily charge for general ward', 'Active'),
(7, 'Room Charges - Private Room', 'Accommodation', 150.00, 'Daily charge for private room', 'Active'),
(8, 'Room Charges - ICU', 'Accommodation', 500.00, 'Daily charge for ICU', 'Active'),
(9, 'Nursing Care', 'Care', 80.00, 'Daily nursing care charges', 'Active'),
(10, 'Medical Equipment Usage', 'Equipment', 25.00, 'Basic medical equipment usage', 'Active');

<<<<<<< HEAD
-- --------------------------------------------------------

--
-- Table structure for table `surgerytb`
--

CREATE TABLE `surgerytb` (
  `id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `operating_doctor_id` int(11) NOT NULL,
  `surgery_type` varchar(255) NOT NULL,
  `scheduled_date` date NOT NULL,
  `scheduled_time` time NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('Scheduled','In Progress','Completed','Cancelled') DEFAULT 'Scheduled',
  `completed_date` date DEFAULT NULL,
  `completed_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
--
-- Indexes for dumped tables
--

--
-- Indexes for table `adminusertb`
--
ALTER TABLE `adminusertb`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `admissiontb`
--
ALTER TABLE `admissiontb`
  ADD PRIMARY KEY (`pid`);

--
<<<<<<< HEAD
-- Indexes for table `adverse_reactions`
--
ALTER TABLE `adverse_reactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pid` (`pid`);

--
-- Indexes for table `billtb`
--
ALTER TABLE `billtb`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cashier_id` (`cashier_id`),
  ADD KEY `billtb_ibfk_1` (`pid`);

--
-- Indexes for table `cashiertb`
--
ALTER TABLE `cashiertb`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `compounding_records`
--
ALTER TABLE `compounding_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pid` (`pid`);

--
-- Indexes for table `counseling_notes`
--
ALTER TABLE `counseling_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pid` (`pid`);
=======
-- Indexes for table `billtb`
--
ALTER TABLE `billtb`
  ADD PRIMARY KEY (`pid`);
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516

--
-- Indexes for table `diagnosticstb`
--
ALTER TABLE `diagnosticstb`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pid` (`pid`);

--
-- Indexes for table `dischargetb`
--
ALTER TABLE `dischargetb`
  ADD PRIMARY KEY (`pid`);

--
-- Indexes for table `doctortb`
--
ALTER TABLE `doctortb`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `emergency_access_logs`
--
ALTER TABLE `emergency_access_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `insurance_companiestb`
--
ALTER TABLE `insurance_companiestb`
  ADD PRIMARY KEY (`insurance_id`);

--
<<<<<<< HEAD
-- Indexes for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medicine_id` (`medicine_id`);

--
=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
-- Indexes for table `invoicetb`
--
ALTER TABLE `invoicetb`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `pid` (`pid`);

--
-- Indexes for table `labtb`
--
ALTER TABLE `labtb`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `labtesttb`
--
ALTER TABLE `labtesttb`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pid` (`pid`);

--
-- Indexes for table `medicinetb`
--
ALTER TABLE `medicinetb`
  ADD PRIMARY KEY (`id`);

--
<<<<<<< HEAD
-- Indexes for table `monitoring_reports`
--
ALTER TABLE `monitoring_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pid` (`pid`);

--
=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
-- Indexes for table `nursetb`
--
ALTER TABLE `nursetb`
  ADD PRIMARY KEY (`id`);

--
<<<<<<< HEAD
-- Indexes for table `operations`
--
ALTER TABLE `operations`
  ADD PRIMARY KEY (`id`);

--
=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
-- Indexes for table `patient_chargstb`
--
ALTER TABLE `patient_chargstb`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pid` (`pid`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `patient_insurancetb`
--
ALTER TABLE `patient_insurancetb`
  ADD PRIMARY KEY (`patient_insurance_id`),
  ADD UNIQUE KEY `unique_patient_insurance` (`patient_id`,`insurance_id`),
  ADD KEY `insurance_id` (`insurance_id`);

--
-- Indexes for table `patient_roundstb`
--
ALTER TABLE `patient_roundstb`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pid` (`pid`);

--
<<<<<<< HEAD
-- Indexes for table `patient_transferstb`
--
ALTER TABLE `patient_transferstb`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pid` (`pid`),
  ADD KEY `from_doctor_id` (`from_doctor_id`),
  ADD KEY `to_doctor_id` (`to_doctor_id`);

--
=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
-- Indexes for table `patreg`
--
ALTER TABLE `patreg`
  ADD PRIMARY KEY (`pid`);

--
-- Indexes for table `paymentstb`
--
ALTER TABLE `paymentstb`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pid` (`pid`);

--
<<<<<<< HEAD
-- Indexes for table `pharmacisttb`
--
ALTER TABLE `pharmacisttb`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `prestb`
--
ALTER TABLE `prestb`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_prestb_status` (`status`),
  ADD KEY `idx_prestb_dispensed_date` (`dispensed_date`);
=======
-- Indexes for table `prestb`
--
ALTER TABLE `prestb`
  ADD PRIMARY KEY (`id`);
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516

--
-- Indexes for table `servicestb`
--
ALTER TABLE `servicestb`
  ADD PRIMARY KEY (`id`);

--
<<<<<<< HEAD
-- Indexes for table `surgerytb`
--
ALTER TABLE `surgerytb`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pid` (`pid`),
  ADD KEY `operating_doctor_id` (`operating_doctor_id`);

--
=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admissiontb`
--
ALTER TABLE `admissiontb`
<<<<<<< HEAD
  MODIFY `pid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `adverse_reactions`
--
ALTER TABLE `adverse_reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `billtb`
--
ALTER TABLE `billtb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `cashiertb`
--
ALTER TABLE `cashiertb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `compounding_records`
--
ALTER TABLE `compounding_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `counseling_notes`
--
ALTER TABLE `counseling_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
=======
  MODIFY `pid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516

--
-- AUTO_INCREMENT for table `diagnosticstb`
--
ALTER TABLE `diagnosticstb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `doctortb`
--
ALTER TABLE `doctortb`
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516

--
-- AUTO_INCREMENT for table `emergency_access_logs`
--
ALTER TABLE `emergency_access_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `insurance_companiestb`
--
ALTER TABLE `insurance_companiestb`
  MODIFY `insurance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
<<<<<<< HEAD
-- AUTO_INCREMENT for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
-- AUTO_INCREMENT for table `invoicetb`
--
ALTER TABLE `invoicetb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `labtb`
--
ALTER TABLE `labtb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `labtesttb`
--
ALTER TABLE `labtesttb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `medicinetb`
--
ALTER TABLE `medicinetb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
<<<<<<< HEAD
-- AUTO_INCREMENT for table `monitoring_reports`
--
ALTER TABLE `monitoring_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
-- AUTO_INCREMENT for table `nursetb`
--
ALTER TABLE `nursetb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
<<<<<<< HEAD
-- AUTO_INCREMENT for table `operations`
--
ALTER TABLE `operations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
-- AUTO_INCREMENT for table `patient_chargstb`
--
ALTER TABLE `patient_chargstb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `patient_insurancetb`
--
ALTER TABLE `patient_insurancetb`
<<<<<<< HEAD
  MODIFY `patient_insurance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
=======
  MODIFY `patient_insurance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516

--
-- AUTO_INCREMENT for table `patient_roundstb`
--
ALTER TABLE `patient_roundstb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
<<<<<<< HEAD
-- AUTO_INCREMENT for table `patient_transferstb`
--
ALTER TABLE `patient_transferstb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
-- AUTO_INCREMENT for table `patreg`
--
ALTER TABLE `patreg`
  MODIFY `pid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `paymentstb`
--
ALTER TABLE `paymentstb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
<<<<<<< HEAD
-- AUTO_INCREMENT for table `pharmacisttb`
--
ALTER TABLE `pharmacisttb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
-- AUTO_INCREMENT for table `prestb`
--
ALTER TABLE `prestb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `servicestb`
--
ALTER TABLE `servicestb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
<<<<<<< HEAD
-- AUTO_INCREMENT for table `surgerytb`
--
ALTER TABLE `surgerytb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
-- Constraints for dumped tables
--

--
<<<<<<< HEAD
-- Constraints for table `adverse_reactions`
--
ALTER TABLE `adverse_reactions`
  ADD CONSTRAINT `adverse_reactions_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `admissiontb` (`pid`);

--
-- Constraints for table `billtb`
--
ALTER TABLE `billtb`
  ADD CONSTRAINT `fk_cashier_id` FOREIGN KEY (`cashier_id`) REFERENCES `cashiertb` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `compounding_records`
--
ALTER TABLE `compounding_records`
  ADD CONSTRAINT `compounding_records_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `admissiontb` (`pid`);

--
-- Constraints for table `counseling_notes`
--
ALTER TABLE `counseling_notes`
  ADD CONSTRAINT `counseling_notes_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `admissiontb` (`pid`);
=======
-- Constraints for table `billtb`
--
ALTER TABLE `billtb`
  ADD CONSTRAINT `billtb_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `admissiontb` (`pid`);
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516

--
-- Constraints for table `diagnosticstb`
--
ALTER TABLE `diagnosticstb`
  ADD CONSTRAINT `diagnosticstb_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `admissiontb` (`pid`);

--
-- Constraints for table `dischargetb`
--
ALTER TABLE `dischargetb`
  ADD CONSTRAINT `dischargetb_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `admissiontb` (`pid`);

--
<<<<<<< HEAD
-- Constraints for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD CONSTRAINT `inventory_logs_ibfk_1` FOREIGN KEY (`medicine_id`) REFERENCES `medicinetb` (`id`);

--
=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
-- Constraints for table `invoicetb`
--
ALTER TABLE `invoicetb`
  ADD CONSTRAINT `invoicetb_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `admissiontb` (`pid`);

--
-- Constraints for table `labtesttb`
--
ALTER TABLE `labtesttb`
  ADD CONSTRAINT `labtesttb_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `admissiontb` (`pid`);

--
<<<<<<< HEAD
-- Constraints for table `monitoring_reports`
--
ALTER TABLE `monitoring_reports`
  ADD CONSTRAINT `monitoring_reports_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `admissiontb` (`pid`);

--
=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
-- Constraints for table `patient_chargstb`
--
ALTER TABLE `patient_chargstb`
  ADD CONSTRAINT `patient_chargstb_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `admissiontb` (`pid`),
  ADD CONSTRAINT `patient_chargstb_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `servicestb` (`id`);

--
-- Constraints for table `patient_insurancetb`
--
ALTER TABLE `patient_insurancetb`
  ADD CONSTRAINT `patient_insurancetb_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `admissiontb` (`pid`) ON DELETE CASCADE,
  ADD CONSTRAINT `patient_insurancetb_ibfk_2` FOREIGN KEY (`insurance_id`) REFERENCES `insurance_companiestb` (`insurance_id`) ON DELETE CASCADE;

--
-- Constraints for table `patient_roundstb`
--
ALTER TABLE `patient_roundstb`
  ADD CONSTRAINT `fk_patient_rounds_pid` FOREIGN KEY (`pid`) REFERENCES `admissiontb` (`pid`) ON DELETE CASCADE;

--
<<<<<<< HEAD
-- Constraints for table `patient_transferstb`
--
ALTER TABLE `patient_transferstb`
  ADD CONSTRAINT `patient_transferstb_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `admissiontb` (`pid`),
  ADD CONSTRAINT `patient_transferstb_ibfk_2` FOREIGN KEY (`from_doctor_id`) REFERENCES `doctortb` (`id`),
  ADD CONSTRAINT `patient_transferstb_ibfk_3` FOREIGN KEY (`to_doctor_id`) REFERENCES `doctortb` (`id`);

--
=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
-- Constraints for table `paymentstb`
--
ALTER TABLE `paymentstb`
  ADD CONSTRAINT `paymentstb_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `admissiontb` (`pid`);
<<<<<<< HEAD

--
-- Constraints for table `surgerytb`
--
ALTER TABLE `surgerytb`
  ADD CONSTRAINT `surgerytb_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `admissiontb` (`pid`),
  ADD CONSTRAINT `surgerytb_ibfk_2` FOREIGN KEY (`operating_doctor_id`) REFERENCES `doctortb` (`id`);
=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
