
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
    status VARCHAR(20) DEFAULT 'Pending',
    FOREIGN KEY (pid) REFERENCES admissiontb(pid)
);

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
