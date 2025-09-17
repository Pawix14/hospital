
CREATE TABLE IF NOT EXISTS doctortb (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(50) NOT NULL,
    fname VARCHAR(50) NOT NULL,
    lname VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    contact VARCHAR(15) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    qualification VARCHAR(200) NOT NULL,
    experience_years INT DEFAULT 0,
    consultation_fee DECIMAL(10,2) DEFAULT 100.00,
    status VARCHAR(20) DEFAULT 'Active',
    created_date DATE DEFAULT (CURRENT_DATE),
    created_by VARCHAR(50) DEFAULT 'admin'
);

INSERT INTO doctortb (username, password, fname, lname, email, contact, specialization, qualification, experience_years, consultation_fee) VALUES
('dr_smith', 'doctor123', 'John', 'Smith', 'john.smith@hospital.com', '1234567890', 'Cardiology', 'MD Cardiology', 10, 200.00),
('dr_johnson', 'doctor123', 'Sarah', 'Johnson', 'sarah.johnson@hospital.com', '1234567891', 'Pediatrics', 'MD Pediatrics', 8, 150.00),
('dr_williams', 'doctor123', 'Michael', 'Williams', 'michael.williams@hospital.com', '1234567892', 'General Medicine', 'MBBS', 5, 100.00);

ALTER TABLE nursetb 
ADD COLUMN IF NOT EXISTS id INT AUTO_INCREMENT PRIMARY KEY FIRST,
ADD COLUMN IF NOT EXISTS fname VARCHAR(50) DEFAULT '',
ADD COLUMN IF NOT EXISTS lname VARCHAR(50) DEFAULT '',
ADD COLUMN IF NOT EXISTS contact VARCHAR(15) DEFAULT '',
ADD COLUMN IF NOT EXISTS department VARCHAR(100) DEFAULT 'General',
ADD COLUMN IF NOT EXISTS shift VARCHAR(20) DEFAULT 'Day',
ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'Active',
ADD COLUMN IF NOT EXISTS created_date DATE DEFAULT (CURRENT_DATE),
ADD COLUMN IF NOT EXISTS created_by VARCHAR(50) DEFAULT 'admin';


INSERT INTO nursetb (username, password, email, fname, lname, contact, department, shift) VALUES
('nurse1', 'nurse123', 'nurse1@hospital.com', 'Mary', 'Wilson', '1234567893', 'General Ward', 'Day'),
('nurse2', 'nurse123', 'nurse2@hospital.com', 'Lisa', 'Brown', '1234567894', 'ICU', 'Night'),
('nurse3', 'nurse123', 'nurse3@hospital.com', 'Jennifer', 'Davis', '1234567895', 'Emergency', 'Day');


ALTER TABLE labtb 
ADD COLUMN IF NOT EXISTS id INT AUTO_INCREMENT PRIMARY KEY FIRST,
ADD COLUMN IF NOT EXISTS fname VARCHAR(50) DEFAULT '',
ADD COLUMN IF NOT EXISTS lname VARCHAR(50) DEFAULT '',
ADD COLUMN IF NOT EXISTS contact VARCHAR(15) DEFAULT '',
ADD COLUMN IF NOT EXISTS department VARCHAR(100) DEFAULT 'Laboratory',
ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'Active',
ADD COLUMN IF NOT EXISTS created_date DATE DEFAULT (CURRENT_DATE),
ADD COLUMN IF NOT EXISTS created_by VARCHAR(50) DEFAULT 'admin';


INSERT INTO labtb (username, password, email, fname, lname, contact, department) VALUES
('lab1', 'lab123', 'lab1@hospital.com', 'Robert', 'Miller', '1234567896', 'Pathology'),
('lab2', 'lab123', 'lab2@hospital.com', 'Amanda', 'Garcia', '1234567897', 'Radiology');


ALTER TABLE admissiontb 
ADD COLUMN IF NOT EXISTS age INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS address TEXT,
ADD COLUMN IF NOT EXISTS emergency_contact VARCHAR(15) DEFAULT '',
ADD COLUMN IF NOT EXISTS emergency_contact_name VARCHAR(100) DEFAULT '',
ADD COLUMN IF NOT EXISTS blood_group VARCHAR(10) DEFAULT '',
ADD COLUMN IF NOT EXISTS medical_history TEXT,
ADD COLUMN IF NOT EXISTS allergies TEXT,
ADD COLUMN IF NOT EXISTS assigned_doctor VARCHAR(50) DEFAULT '',
ADD COLUMN IF NOT EXISTS room_number VARCHAR(20) DEFAULT '',
ADD COLUMN IF NOT EXISTS created_by VARCHAR(50) DEFAULT 'nurse';
