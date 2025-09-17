CREATE TABLE IF NOT EXISTS prestb (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor VARCHAR(255) NOT NULL,
    pid INT NOT NULL,
    fname VARCHAR(255) NOT NULL,
    lname VARCHAR(255) NOT NULL,
    allergy VARCHAR(255),
    prescription TEXT,
    price DECIMAL(10,2),
    diagnosis_details TEXT,
    prescribed_medicines TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
