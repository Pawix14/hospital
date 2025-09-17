-- updates sa database

ALTER TABLE admissiontb 
ADD COLUMN IF NOT EXISTS age INT,
ADD COLUMN IF NOT EXISTS address TEXT,
ADD COLUMN IF NOT EXISTS reason TEXT,
ADD COLUMN IF NOT EXISTS assigned_doctor VARCHAR(100),
ADD COLUMN IF NOT EXISTS room_number VARCHAR(20),
ADD COLUMN IF NOT EXISTS admission_time TIME DEFAULT NULL;

CREATE TABLE IF NOT EXISTS diagnosticstb (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pid INT NOT NULL,
    doctor_name VARCHAR(100) NOT NULL,
    symptoms TEXT,
    diagnosis TEXT,
    vital_signs TEXT,
    physical_examination TEXT,
    medical_history TEXT,
    diagnostic_tests_ordered TEXT,
    treatment_plan TEXT,
    created_date DATE NOT NULL,
    created_time TIME NOT NULL,
    FOREIGN KEY (pid) REFERENCES admissiontb(pid)
);

CREATE TABLE IF NOT EXISTS servicestb (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(255) NOT NULL,
    service_category VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    status VARCHAR(20) DEFAULT 'Active'
);


INSERT INTO servicestb (service_name, service_category, price, description) VALUES
('General Consultation', 'Consultation', 100.00, 'Basic doctor consultation'),
('Specialist Consultation', 'Consultation', 200.00, 'Specialist doctor consultation'),
('Emergency Consultation', 'Consultation', 300.00, 'Emergency room consultation'),
('Follow-up Consultation', 'Consultation', 75.00, 'Follow-up visit consultation'),
('Surgical Consultation', 'Consultation', 250.00, 'Pre-surgical consultation'),
('Room Charges - General Ward', 'Accommodation', 50.00, 'Daily charge for general ward'),
('Room Charges - Private Room', 'Accommodation', 150.00, 'Daily charge for private room'),
('Room Charges - ICU', 'Accommodation', 500.00, 'Daily charge for ICU'),
('Nursing Care', 'Care', 80.00, 'Daily nursing care charges'),
('Medical Equipment Usage', 'Equipment', 25.00, 'Basic medical equipment usage');


CREATE TABLE IF NOT EXISTS patient_chargstb (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pid INT NOT NULL,
    service_id INT NOT NULL,
    quantity INT DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    added_by VARCHAR(100) NOT NULL,
    added_date DATE NOT NULL,
    added_time TIME NOT NULL,
    description TEXT,
    FOREIGN KEY (pid) REFERENCES admissiontb(pid),
    FOREIGN KEY (service_id) REFERENCES servicestb(id)
);


ALTER TABLE labtesttb 
ADD COLUMN IF NOT EXISTS price DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS requested_date DATE DEFAULT NULL,
ADD COLUMN IF NOT EXISTS requested_time TIME DEFAULT NULL,
ADD COLUMN IF NOT EXISTS lab_notes TEXT,
ADD COLUMN IF NOT EXISTS results TEXT,
ADD COLUMN IF NOT EXISTS priority VARCHAR(20) DEFAULT 'Normal';


ALTER TABLE billtb 
ADD COLUMN IF NOT EXISTS service_charges DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS room_charges DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS other_charges DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS discount DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS payment_date DATE DEFAULT NULL,
ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS invoice_generated BOOLEAN DEFAULT FALSE;


CREATE TABLE IF NOT EXISTS paymentstb (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pid INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_date DATE NOT NULL,
    payment_time TIME NOT NULL,
    processed_by VARCHAR(100) NOT NULL,
    transaction_id VARCHAR(100),
    notes TEXT,
    FOREIGN KEY (pid) REFERENCES admissiontb(pid)
);


ALTER TABLE dischargetb 
ADD COLUMN IF NOT EXISTS discharge_time TIME DEFAULT NULL,
ADD COLUMN IF NOT EXISTS discharge_summary TEXT,
ADD COLUMN IF NOT EXISTS final_diagnosis TEXT,
ADD COLUMN IF NOT EXISTS discharge_medications TEXT,
ADD COLUMN IF NOT EXISTS follow_up_instructions TEXT,
ADD COLUMN IF NOT EXISTS discharged_by VARCHAR(100) DEFAULT NULL;


CREATE TABLE IF NOT EXISTS invoicetb (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pid INT NOT NULL,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    generated_date DATE NOT NULL,
    generated_time TIME NOT NULL,
    generated_by VARCHAR(100) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(20) DEFAULT 'Generated',
    FOREIGN KEY (pid) REFERENCES admissiontb(pid)
);
