CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category ENUM('rent', 'water', 'electricity', 'internet', 'stationery', 'fuel', 'transport', 'cleaning', 'maintenance', 'misc') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    paid_by_id INT NOT NULL,
    expense_date DATE NOT NULL,
    description TEXT,
    receipt_file VARCHAR(255) NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    approved_by_id INT NULL,
    approved_at DATETIME NULL,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (paid_by_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (approved_by_id) REFERENCES users(id) ON DELETE SET NULL
);
