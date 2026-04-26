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
