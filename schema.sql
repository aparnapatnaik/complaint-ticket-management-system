CREATE TABLE IF NOT EXISTS complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaintNumber INT,
    userId INT,
    user_id INT,
    category INT,
    subcategory VARCHAR(255),
    complaintType VARCHAR(255),
    state VARCHAR(255),
    noc VARCHAR(255),
    complaintDetails TEXT,
    complaintFile VARCHAR(255),
    regDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(100) DEFAULT NULL,
    lastUpdationDate TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
);
