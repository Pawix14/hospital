-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 17, 2025 at 06:06 PM
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
  `role` varchar(20) NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `adminusertb`
--

INSERT INTO `adminusertb` (`username`, `password`, `email`, `role`) VALUES
('admin', 'admin123', 'admin@hospital.com', 'admin');

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
  `reason` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admissiontb`
--

INSERT INTO `admissiontb` (`pid`, `fname`, `lname`, `gender`, `email`, `contact`, `password`, `admission_date`, `status`, `age`, `address`, `blood_group`, `medical_history`, `allergies`, `assigned_doctor`, `room_number`, `created_by`, `reason`) VALUES
(1, 'Paolo', 'madridano', 'Male', 'g@gmail.com', '0944644675', '$2y$10$pm.AeJSO/M2eclygLuIYiucFHUC3Ic9sK92HsVqyoHprCGKDz/RsK', '2025-09-17', 'Discharged', 20, 'kaqrew', '', NULL, NULL, 'dr_smith', '402', 'nurse', 'back pain'),
(2, 'justin', 'nabunturan', 'Male', 'justin@gmail.com', '0995525454', '$2y$10$2Z2gHjb5rtmwAx7x7BfpEuU05.7hiI8nfCuzl6SMolQ9n9Zk3yere', '2025-09-17', 'Ready for Discharge', 32, 'cebu', '', NULL, NULL, 'pawix_12', '201', 'nurse', 'Sakit ang buto'),
(3, 'lebron', 'james', 'Male', 'james@gmail.com', '513100312', '$2y$10$1vW0v9Kp8B9sNcQUYC3yLOPlQbZdaQUGvEy6LDCyzZZIy89N1yqRi', '2025-09-17', 'Admitted', 32, 'popasodj', '', NULL, NULL, 'pawix_12', '201', 'nurse', 'qweqp'),
(4, 'james', 'lebron', 'Male', 'lebron@gmail.com', '0909901965', '$2y$10$Iz3TeaMWkjE6EqZQxJ97Z.7mupDJHtL5UYO548w8SvwS6YlFS7Jn.', '2025-09-17', 'Admitted', 40, 'aosemasd', '', NULL, NULL, 'pawix_12', '402', 'nurse', 'pqwjewqjr');

-- --------------------------------------------------------

--
-- Table structure for table `billtb`
--

CREATE TABLE `billtb` (
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
  `invoice_generated` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `billtb`
--

INSERT INTO `billtb` (`pid`, `consultation_fees`, `lab_fees`, `medicine_fees`, `total`, `status`, `service_charges`, `room_charges`, `other_charges`, `discount`, `payment_date`, `payment_method`, `invoice_generated`) VALUES
(1, 200.00, 30.00, 0.00, 590.00, 'Paid', 160.00, 200.00, 0.00, 0.00, NULL, NULL, 0),
(2, 800.00, 0.00, 0.00, 950.00, 'Paid', 0.00, 150.00, 0.00, 0.00, NULL, NULL, 0),
(3, 800.00, 0.00, 0.00, 950.00, 'Unpaid', 0.00, 150.00, 0.00, 0.00, NULL, NULL, 0),
(4, 800.00, 0.00, 0.00, 1000.00, 'Unpaid', 0.00, 200.00, 0.00, 0.00, NULL, NULL, 0);

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
(1, 1, 'dr_smith', 'Ubo', 'Undang nag ginhawa', '120', 'Bad', 'Nabali ang tiil', 'wala', 'wala', '2025-09-17', '19:43:52'),
(2, 4, 'pawix_12', 'wala', 'wala', 'init', 'luya', 'aans', 'wala', 'wala', '2025-09-17', '23:25:13'),
(3, 4, 'pawix_12', 'wala', 'wala', 'init', 'luya', 'aans', 'wala', 'wala', '2025-09-17', '23:26:07'),
(4, 4, 'pawix_12', 'wala', 'wala', 'init', 'luya', 'aans', 'wala', 'wala', '2025-09-17', '23:26:40');

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
(1, '2025-09-17', 1, NULL, NULL, NULL, NULL, NULL, NULL);

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
  `created_by` varchar(50) DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctortb`
--

INSERT INTO `doctortb` (`id`, `username`, `password`, `fname`, `lname`, `email`, `contact`, `specialization`, `qualification`, `experience_years`, `consultation_fee`, `status`, `created_date`, `created_by`) VALUES
(1, 'dr_smith', 'doctor123', 'John', 'Smith', 'john.smith@hospital.com', '1234567890', 'Cardiology', 'MD Cardiology', 10, 200.00, 'Active', '2025-09-17', 'admin'),
(2, 'dr_johnson', 'doctor123', 'Sarah', 'Johnson', 'sarah.johnson@hospital.com', '1234567891', 'Pediatrics', 'MD Pediatrics', 8, 150.00, 'Active', '2025-09-17', 'admin'),
(3, 'dr_williams', 'doctor123', 'Michael', 'Williams', 'michael.williams@hospital.com', '1234567892', 'General Medicine', 'MBBS', 5, 100.00, 'Active', '2025-09-17', 'admin'),
(7, 'pawix_12', 'paolo123', 'Gabriel', 'Madridano', 'pmadridano2@gmial.com', '09940213443', 'Cardiology', 'Ok', 13, 800.00, 'Active', '2025-09-17', 'admin');

-- --------------------------------------------------------

--
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
(1, 2, 'INV-2-20250917-5253', '2025-09-17', '20:00:14', 'Patient Request', 950.00, 'Generated'),
(2, 2, 'INV-2-20250917-6895', '2025-09-17', '20:03:04', 'Patient Request', 950.00, 'Generated'),
(3, 2, 'INV-2-20250917-6780', '2025-09-17', '20:18:07', 'Patient Request', 950.00, 'Approved'),
(5, 2, 'INV-2-20250917-9660', '2025-09-17', '20:25:46', 'Patient Request', 950.00, 'Approved');

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
  `created_by` varchar(50) DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `labtb`
--

INSERT INTO `labtb` (`id`, `username`, `password`, `email`, `fname`, `lname`, `contact`, `department`, `status`, `created_date`, `created_by`) VALUES
(1, 'lab1', 'lab123', 'lab1@hospital.com', 'Robert', 'Miller', '1234567896', 'Pathology', 'Active', '2025-09-17', 'admin'),
(2, 'lab2', 'lab123', 'lab2@hospital.com', 'Amanda', 'Garcia', '1234567897', 'Radiology', 'Active', '2025-09-17', 'admin');

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
(1, 1, 'Urine Analysis', 'dr_smith', 'Completed', '2025-09-18', '2025-09-17', 30.00, '2025-09-17', '19:43:10', 'wala', 'Done', 'Normal');

-- --------------------------------------------------------

--
-- Table structure for table `medicinetb`
--

CREATE TABLE `medicinetb` (
  `id` int(11) NOT NULL,
  `medicine_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `added_by_nurse` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `medicinetb`
--

INSERT INTO `medicinetb` (`id`, `medicine_name`, `quantity`, `added_by_nurse`, `price`) VALUES
(1, 'Paracetamol', 100, 'nurse1', 0.50),
(2, 'Ibuprofen', 50, 'nurse1', 0.75),
(3, 'Amoxicillin', 75, 'nurse2', 1.20),
(4, 'Aspirin', 200, 'nurse2', 0.40),
(5, 'Insulin', 30, 'nurse1', 15.00),
(6, 'Vitamin D', 150, 'nurse2', 0.30),
(7, 'Antihistamine', 80, 'nurse1', 0.60),
(8, 'Cough Syrup', 60, 'nurse2', 3.00),
(9, 'Bandages', 500, 'nurse1', 0.10),
(10, 'Antiseptic Cream', 40, 'nurse2', 2.50);

-- --------------------------------------------------------

--
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
  `created_by` varchar(50) DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `nursetb`
--

INSERT INTO `nursetb` (`id`, `username`, `password`, `email`, `fname`, `lname`, `contact`, `department`, `shift`, `status`, `created_date`, `created_by`) VALUES
(1, 'nurse1', 'nurse123', 'nurse1@hospital.com', 'Mary', 'Wilson', '1234567893', 'General Ward', 'Day', 'Active', '2025-09-17', 'admin'),
(2, 'nurse2', 'nurse123', 'nurse2@hospital.com', 'Lisa', 'Brown', '1234567894', 'ICU', 'Night', 'Active', '2025-09-17', 'admin'),
(3, 'nurse3', 'nurse123', 'nurse3@hospital.com', 'Jennifer', 'Davis', '1234567895', 'Emergency', 'Day', 'Active', '2025-09-17', 'admin'),
(6, 'testnurse', 'nurse123', 'testnurse@gmail.com', 'Test ', 'Nurse', '0844456', 'ICU', 'Morning', 'Active', '2025-09-17', 'admin');

-- --------------------------------------------------------

--
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
(1, 1, 9, 2, 80.00, 160.00, 'dr_smith', '2025-09-17', '19:44:13', 'patay naka\r\n');

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
(1, 2, 'nurse1', '2025-09-17', '20:39:00', NULL, 'Ok', 'Scheduled', '2025-09-17 12:40:08'),
(2, 2, 'nurse1', '2025-09-17', '20:39:00', NULL, 'Ok', 'Scheduled', '2025-09-17 12:42:22');

-- --------------------------------------------------------

--
-- Table structure for table `patreg`
--

CREATE TABLE `patreg` (
  `pid` int(11) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `email` varchar(50) NOT NULL,
  `contact` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
(1, 1, 400.00, 'Bank Transfer', '2025-09-17', '19:41:48', 'Patient Self-Service', NULL, 'Payment request submitted by patient', 'Pending'),
(2, 1, 590.00, 'Cash', '2025-09-17', '19:45:34', 'Patient Self-Service', NULL, 'Payment request submitted by patient', 'Pending'),
(3, 2, 950.00, 'Credit Card', '2025-09-17', '20:28:57', 'Patient Self-Service', NULL, 'Payment request submitted by patient', 'Pending');

-- --------------------------------------------------------

--
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prestb`
--

INSERT INTO `prestb` (`id`, `doctor`, `pid`, `fname`, `lname`, `symptoms`, `allergy`, `prescription`, `price`, `diagnosis_details`, `prescribed_medicines`, `created_at`) VALUES
(1, 'pawix_12', 2, 'justin', 'nabunturan', NULL, 'Pollen', 'Amoxicillin, Aspirin, Antihistamine, Cough Syrup, Antiseptic Cream', 7.70, 'qwewq', 'Amoxicillin, Aspirin, Antihistamine, Cough Syrup, Antiseptic Cream', '2025-09-17 14:56:23'),
(2, 'pawix_12', 2, 'justin', 'nabunturan', NULL, 'None', 'Amoxicillin', 1.20, 'asdasdas', 'Amoxicillin', '2025-09-17 14:57:54'),
(3, 'pawix_12', 2, 'justin', 'nabunturan', '', 'Latex', 'Antihistamine', 0.60, 'qweqwe', 'Antihistamine', '2025-09-17 15:10:14'),
(4, 'pawix_12', 2, 'justin', 'nabunturan', '', 'Latex', 'Antihistamine', 0.60, 'qweqwe', 'Antihistamine', '2025-09-17 15:10:20'),
(5, 'pawix_12', 2, 'justin', 'nabunturan', '', 'Latex', 'Antihistamine', 0.60, 'qweqwe', 'Antihistamine', '2025-09-17 15:11:47');

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
-- Indexes for table `billtb`
--
ALTER TABLE `billtb`
  ADD PRIMARY KEY (`pid`);

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
-- Indexes for table `nursetb`
--
ALTER TABLE `nursetb`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `patient_chargstb`
--
ALTER TABLE `patient_chargstb`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pid` (`pid`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `patient_roundstb`
--
ALTER TABLE `patient_roundstb`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pid` (`pid`);

--
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
-- Indexes for table `prestb`
--
ALTER TABLE `prestb`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `servicestb`
--
ALTER TABLE `servicestb`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admissiontb`
--
ALTER TABLE `admissiontb`
  MODIFY `pid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `diagnosticstb`
--
ALTER TABLE `diagnosticstb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `doctortb`
--
ALTER TABLE `doctortb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `invoicetb`
--
ALTER TABLE `invoicetb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `labtb`
--
ALTER TABLE `labtb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `labtesttb`
--
ALTER TABLE `labtesttb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `medicinetb`
--
ALTER TABLE `medicinetb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `nursetb`
--
ALTER TABLE `nursetb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `patient_chargstb`
--
ALTER TABLE `patient_chargstb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `patient_roundstb`
--
ALTER TABLE `patient_roundstb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `patreg`
--
ALTER TABLE `patreg`
  MODIFY `pid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `paymentstb`
--
ALTER TABLE `paymentstb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `prestb`
--
ALTER TABLE `prestb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `servicestb`
--
ALTER TABLE `servicestb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `billtb`
--
ALTER TABLE `billtb`
  ADD CONSTRAINT `billtb_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `admissiontb` (`pid`);

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
-- Constraints for table `patient_chargstb`
--
ALTER TABLE `patient_chargstb`
  ADD CONSTRAINT `patient_chargstb_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `admissiontb` (`pid`),
  ADD CONSTRAINT `patient_chargstb_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `servicestb` (`id`);

--
-- Constraints for table `patient_roundstb`
--
ALTER TABLE `patient_roundstb`
  ADD CONSTRAINT `patient_roundstb_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `admissiontb` (`pid`);

--
-- Constraints for table `paymentstb`
--
ALTER TABLE `paymentstb`
  ADD CONSTRAINT `paymentstb_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `admissiontb` (`pid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
