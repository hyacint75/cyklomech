CREATE TABLE IF NOT EXISTS service_booking_access (
    id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    password_hash VARCHAR(255) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO service_booking_access (id, password_hash)
VALUES (1, '$2y$10$KerhuRejUGmOXyg9iXEvveTLkJWcLF9uxSXjUL8.Je2wZeZEXm75e')
ON DUPLICATE KEY UPDATE id = VALUES(id);

CREATE TABLE IF NOT EXISTS service_reservations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(120) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    email VARCHAR(120) DEFAULT NULL,
    preferred_date DATE NOT NULL,
    preferred_time VARCHAR(40) NOT NULL,
    service_type VARCHAR(80) NOT NULL,
    bike_info VARCHAR(160) NOT NULL,
    note TEXT DEFAULT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'nova',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_service_reservations_date (preferred_date),
    INDEX idx_service_reservations_status (status)
);

CREATE TABLE IF NOT EXISTS service_bike_serials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sale_date DATE DEFAULT NULL,
    service_date DATE DEFAULT NULL,
    serial_number VARCHAR(120) NOT NULL,
    bike_description VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_service_bike_serials_serial (serial_number),
    INDEX idx_service_bike_serials_sale_date (sale_date),
    INDEX idx_service_bike_serials_service_date (service_date)
);

CREATE TABLE IF NOT EXISTS service_sheets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    serial_id INT UNSIGNED NOT NULL,
    sheet_number VARCHAR(32) NOT NULL,
    repair_date DATE NOT NULL,
    serial_number VARCHAR(120) NOT NULL,
    request_text TEXT DEFAULT NULL,
    material_total DECIMAL(10,2) NOT NULL DEFAULT 0,
    labor_total DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_price DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_service_sheets_sheet_number (sheet_number),
    INDEX idx_service_sheets_serial_id (serial_id),
    INDEX idx_service_sheets_repair_date (repair_date)
);

CREATE TABLE IF NOT EXISTS service_sheet_material_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sheet_id INT UNSIGNED NOT NULL,
    item_name VARCHAR(160) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_service_sheet_material_items_sheet_id (sheet_id)
);

CREATE TABLE IF NOT EXISTS service_sheet_labor_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sheet_id INT UNSIGNED NOT NULL,
    operation_name VARCHAR(160) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_service_sheet_labor_items_sheet_id (sheet_id)
);
