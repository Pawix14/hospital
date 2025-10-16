-- Pharmacist Panel Database Updates
-- This file contains the new tables added for enhanced pharmacy management features

-- Counseling Notes Table
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

-- Monitoring Reports Table
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

-- Compounding Records Table
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

-- Adverse Reactions Table
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

-- Inventory Logs Table
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

-- Indexes and Constraints
ALTER TABLE `counseling_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pid` (`pid`);

ALTER TABLE `monitoring_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pid` (`pid`);

ALTER TABLE `compounding_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pid` (`pid`);

ALTER TABLE `adverse_reactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pid` (`pid`);

ALTER TABLE `inventory_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medicine_id` (`medicine_id`);

-- Auto Increment
ALTER TABLE `counseling_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `monitoring_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `compounding_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `adverse_reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `inventory_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- Foreign Key Constraints
ALTER TABLE `counseling_notes`
  ADD CONSTRAINT `counseling_notes_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `admissiontb` (`pid`);

ALTER TABLE `monitoring_reports`
  ADD CONSTRAINT `monitoring_reports_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `admissiontb` (`pid`);

ALTER TABLE `compounding_records`
  ADD CONSTRAINT `compounding_records_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `admissiontb` (`pid`);

ALTER TABLE `adverse_reactions`
  ADD CONSTRAINT `adverse_reactions_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `admissiontb` (`pid`);

ALTER TABLE `inventory_logs`
  ADD CONSTRAINT `inventory_logs_ibfk_1` FOREIGN KEY (`medicine_id`) REFERENCES `medicinetb` (`id`);
