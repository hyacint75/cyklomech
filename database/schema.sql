CREATE DATABASE IF NOT EXISTS cyklo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cyklo;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, password_hash)
VALUES ('admin', '$2y$10$.ajE8Ahowkn0PFl6nL2iIeujCtlBE5/dJv1fgW3rpM3Hsc5LzHKAC')
ON DUPLICATE KEY UPDATE username = VALUES(username);

CREATE TABLE IF NOT EXISTS audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL,
    action VARCHAR(40) NOT NULL,
    entity_type VARCHAR(40) NOT NULL,
    entity_id INT UNSIGNED DEFAULT NULL,
    details_json JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_created_at (created_at),
    INDEX idx_audit_entity (entity_type, entity_id)
);

CREATE TABLE IF NOT EXISTS site_notice (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    message TEXT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    updated_by VARCHAR(80) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO site_notice (id, message, is_active, updated_by)
VALUES (1, '', 0, NULL)
ON DUPLICATE KEY UPDATE id = VALUES(id);

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

CREATE TABLE IF NOT EXISTS bikes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    category VARCHAR(60) NOT NULL,
    bike_type VARCHAR(20) NOT NULL DEFAULT 'uni',
    manufacturer VARCHAR(120) DEFAULT NULL,
    price_czk DECIMAL(10,2) NOT NULL,
    old_price_czk DECIMAL(10,2) DEFAULT NULL,
    is_new TINYINT(1) NOT NULL DEFAULT 0,
    description TEXT NOT NULL,
    image_url VARCHAR(255) DEFAULT NULL,
    color VARCHAR(60) DEFAULT NULL,
    frame_size VARCHAR(60) DEFAULT NULL,
    wheel_size VARCHAR(60) DEFAULT NULL,
    weight_kg DECIMAL(5,2) DEFAULT NULL,
    tires VARCHAR(120) DEFAULT NULL,
    lighting VARCHAR(120) DEFAULT NULL,
    brakes VARCHAR(120) DEFAULT NULL,
    bottom_bracket VARCHAR(120) DEFAULT NULL,
    front_hub VARCHAR(120) DEFAULT NULL,
    rear_hub VARCHAR(120) DEFAULT NULL,
    front_rotor VARCHAR(120) DEFAULT NULL,
    rear_rotor VARCHAR(120) DEFAULT NULL,
    front_derailleur VARCHAR(120) DEFAULT NULL,
    rear_derailleur VARCHAR(120) DEFAULT NULL,
    wheels VARCHAR(120) DEFAULT NULL,
    battery VARCHAR(120) DEFAULT NULL,
    motor VARCHAR(120) DEFAULT NULL,
    display_name VARCHAR(120) DEFAULT NULL,
    saddle VARCHAR(120) DEFAULT NULL,
    cassette VARCHAR(120) DEFAULT NULL,
    frame_spec VARCHAR(120) DEFAULT NULL,
    fork_spec VARCHAR(120) DEFAULT NULL,
    chain_spec VARCHAR(120) DEFAULT NULL,
    note VARCHAR(255) DEFAULT NULL,
    in_stock TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO bikes (name, category, bike_type, manufacturer, price_czk, old_price_czk, is_new, description, image_url, color, frame_size, wheel_size, weight_kg, tires, lighting, brakes, bottom_bracket, front_hub, rear_hub, front_rotor, rear_rotor, front_derailleur, rear_derailleur, wheels, battery, motor, display_name, saddle, cassette, frame_spec, fork_spec, chain_spec, note, in_stock)
VALUES
    ('Rockrider Trail 540', 'Horské', 'pánské', 'Rockrider', 21990.00, NULL, 0, 'Univerzální hardtail pro lesní trasy i delší vyjížďky.', 'https://images.unsplash.com/photo-1511994298241-608e28f14fde?auto=format&fit=crop&w=1200&q=80', 'Černá/Zelená', 'M', '29"', 13.80, NULL, NULL, 'Hydraulické kotoučové', 'Shimano BB-MT500', NULL, NULL, NULL, NULL, 'Shimano Deore', 'Shimano Deore', 'WTB ST i30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
    ('CityGo Urban 3', 'Městské', 'uni', 'CityGo', 12990.00, NULL, 0, 'Lehké městské kolo s pohodlným posedem a nosičem.', 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?auto=format&fit=crop&w=1200&q=80', 'Šedá', 'L', '28"', 14.60, NULL, NULL, 'V-Brake', 'Neco B910', NULL, NULL, NULL, NULL, 'Shimano Tourney', 'Shimano Tourney', 'Remerx Dragon', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
    ('Sprint Carbon R7', 'Silniční', 'dámské', 'Sprint', 39990.00, NULL, 0, 'Rychlý karbonový rám pro sportovní jízdu.', 'https://images.unsplash.com/photo-1505705694340-019e1e335916?auto=format&fit=crop&w=1200&q=80', 'Bílá/Červená', 'M', '28"', 8.40, NULL, NULL, 'Kotoučové', 'Token Ninja', NULL, NULL, NULL, NULL, 'Shimano 105', 'Shimano 105', 'Mavic Aksium', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0)
ON DUPLICATE KEY UPDATE name = VALUES(name);

SELECT
    id,
    customer_name,
    phone,
    email,
    preferred_date,
    preferred_time,
    service_type,
    bike_info,
    note,
    status,
    created_at
FROM service_reservations
ORDER BY preferred_date ASC, preferred_time ASC, id DESC;

SHOW TABLES LIKE 'service_reservations';
