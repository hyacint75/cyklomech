CREATE TABLE IF NOT EXISTS customer_cards (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    card_code VARCHAR(32) NOT NULL UNIQUE,
    customer_name VARCHAR(120) NOT NULL,
    email VARCHAR(120) DEFAULT NULL,
    phone VARCHAR(40) DEFAULT NULL,
    bike_name VARCHAR(160) DEFAULT NULL,
    serial_number VARCHAR(120) DEFAULT NULL,
    note TEXT DEFAULT NULL,
    created_by VARCHAR(80) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customer_cards_created_at (created_at),
    INDEX idx_customer_cards_customer_name (customer_name)
);

ALTER TABLE customer_cards
    ADD COLUMN IF NOT EXISTS bike_name VARCHAR(160) DEFAULT NULL AFTER phone,
    ADD COLUMN IF NOT EXISTS serial_number VARCHAR(120) DEFAULT NULL AFTER bike_name;
