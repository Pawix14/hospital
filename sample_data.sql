
INSERT INTO nursetb (username, password, email) VALUES
('nurse1', 'password123', 'nurse1@hospital.com'),
('nurse2', 'password123', 'nurse2@hospital.com');

INSERT INTO labtb (username, password, email) VALUES
('lab1', 'password123', 'lab1@hospital.com'),
('lab2', 'password123', 'lab2@hospital.com');

INSERT INTO admissiontb (fname, lname, gender, email, contact, admission_date, status) VALUES
('John', 'Doe', 'Male', 'john.doe@email.com', '1234567890', '2023-10-01', 'Admitted'),
('Jane', 'Smith', 'Female', 'jane.smith@email.com', '0987654321', '2023-10-02', 'Admitted'),
('Bob', 'Johnson', 'Male', 'bob.johnson@email.com', '1122334455', '2023-10-03', 'Admitted');

INSERT INTO medicinetb (medicine_name, quantity, added_by_nurse, price) VALUES
('Paracetamol', 100, 'nurse1', 0.50),
('Ibuprofen', 50, 'nurse1', 0.75),
('Amoxicillin', 75, 'nurse2', 1.20),
('Aspirin', 200, 'nurse2', 0.40),
('Insulin', 30, 'nurse1', 15.00),
('Vitamin D', 150, 'nurse2', 0.30),
('Antihistamine', 80, 'nurse1', 0.60),
('Cough Syrup', 60, 'nurse2', 3.00),
('Bandages', 500, 'nurse1', 0.10),
('Antiseptic Cream', 40, 'nurse2', 2.50);

INSERT INTO labtesttb (pid, test_name, suggested_by_doctor, status, scheduled_date, price) VALUES
(1, 'Blood Test', 'Dr. Smith', 'Pending', NULL, 50.00),
(1, 'X-Ray Chest', 'Dr. Smith', 'Accepted', '2023-10-05', 150.00),
(2, 'Urine Analysis', 'Dr. Johnson', 'Completed', '2023-10-04', 30.00),
(2, 'ECG', 'Dr. Johnson', 'Pending', NULL, 100.00),
(3, 'MRI Brain', 'Dr. Brown', 'Accepted', '2023-10-06', 500.00),
(3, 'Blood Sugar Test', 'Dr. Brown', 'Pending', NULL, 25.00);


DELETE FROM billtb;

INSERT INTO billtb (pid, consultation_fees, lab_fees, medicine_fees, total, status) VALUES
(1, 500.00, 200.00, 150.00, 850.00, 'Unpaid'),
(2, 600.00, 150.00, 100.00, 850.00, 'Paid'),
(3, 450.00, 300.00, 200.00, 950.00, 'Unpaid');


INSERT INTO dischargetb (pid, discharge_date, approved_by_admin) VALUES
(2, '2023-10-05', TRUE);

