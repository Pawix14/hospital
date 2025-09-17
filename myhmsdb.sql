-- Add tables for walk-in hospital billing system

CREATE TABLE `patreg` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `email` varchar(50) NOT NULL,
  `contact` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `nursetb` (
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `labtb` (
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `admissiontb` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `email` varchar(50) NOT NULL,
  `contact` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `admission_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Admitted',
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `labtesttb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `test_name` varchar(100) NOT NULL,
  `suggested_by_doctor` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `scheduled_date` date DEFAULT NULL,
  `completed_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`pid`) REFERENCES admissiontb(`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `medicinetb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `medicine_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `added_by_nurse` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `billtb` (
  `pid` int(11) NOT NULL,
  `consultation_fees` decimal(10,2) NOT NULL DEFAULT 0,
  `lab_fees` decimal(10,2) NOT NULL DEFAULT 0,
  `medicine_fees` decimal(10,2) NOT NULL DEFAULT 0,
  `total` decimal(10,2) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'Unpaid',
  PRIMARY KEY (`pid`),
  FOREIGN KEY (`pid`) REFERENCES admissiontb(`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `dischargetb` (
  `pid` int(11) NOT NULL,
  `discharge_date` date DEFAULT NULL,
  `approved_by_admin` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`pid`),
  FOREIGN KEY (`pid`) REFERENCES admissiontb(`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Create admin users table
CREATE TABLE `adminusertb` (
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'admin',
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Insert default admin user
INSERT INTO `adminusertb` (username, password, email, role) VALUES
('admin', 'admin123', 'admin@hospital.com', 'admin');

-- Update prestb to link to admission pid instead of appointment ID
ALTER TABLE `prestb`
  ADD COLUMN `admission_pid` int(11) DEFAULT NULL,
  ADD FOREIGN KEY (`admission_pid`) REFERENCES admissiontb(`pid`);
