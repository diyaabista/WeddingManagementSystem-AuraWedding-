-- Copy and paste this ONLY into phpMyAdmin SQL tab
-- Do NOT include any PHP code

CREATE TABLE IF NOT EXISTS user_designs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    booking_id INT NOT NULL,
    ceremony_type ENUM('sangeet','mehendi','haldi','vivaha') NOT NULL,
    design_type ENUM('mehendi','decor','venue','other') DEFAULT 'mehendi',
    image_url VARCHAR(255) NOT NULL,
    description TEXT,
    is_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);