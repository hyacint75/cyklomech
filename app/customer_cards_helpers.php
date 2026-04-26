<?php

function customer_card_default_form(): array
{
    return [
        'customer_name' => '',
        'email' => '',
        'phone' => '',
        'bike_name' => '',
        'serial_number' => '',
        'note' => '',
    ];
}

function customer_card_ok_message(string $okKey): string
{
    return match ($okKey) {
        'created' => 'Zákaznická karta byla vytvořena.',
        default => '',
    };
}

function ensure_customer_cards_table(mysqli $mysqli): bool
{
    try {
        if (!$mysqli->query(
            'CREATE TABLE IF NOT EXISTS customer_cards (
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
            )'
        )) {
            return false;
        }

        $columns = [];
        $result = $mysqli->query('SHOW COLUMNS FROM customer_cards');
        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $columns[] = (string) ($row['Field'] ?? '');
            }
            $result->free();
        }

        if (!in_array('bike_name', $columns, true)) {
            $mysqli->query('ALTER TABLE customer_cards ADD COLUMN bike_name VARCHAR(160) DEFAULT NULL AFTER phone');
        }

        if (!in_array('serial_number', $columns, true)) {
            $mysqli->query('ALTER TABLE customer_cards ADD COLUMN serial_number VARCHAR(120) DEFAULT NULL AFTER bike_name');
        }

        return true;
    } catch (Throwable $exception) {
        return false;
    }
}

function generate_customer_card_code(mysqli $mysqli): ?string
{
    for ($attempt = 0; $attempt < 10; $attempt++) {
        try {
            $code = 'ZK-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3)));
        } catch (Throwable $exception) {
            return null;
        }

        $stmt = $mysqli->prepare('SELECT id FROM customer_cards WHERE card_code = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('s', $code);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result instanceof mysqli_result && $result->fetch_assoc() !== null;
        if ($result instanceof mysqli_result) {
            $result->free();
        }
        $stmt->close();

        if (!$exists) {
            return $code;
        }
    }

    return null;
}

function create_customer_card(mysqli $mysqli, array $form): ?array
{
    if (!ensure_customer_cards_table($mysqli)) {
        return null;
    }

    $code = generate_customer_card_code($mysqli);
    if ($code === null) {
        return null;
    }

    $createdBy = current_username();
    $stmt = $mysqli->prepare(
        "INSERT INTO customer_cards (card_code, customer_name, email, phone, bike_name, serial_number, note, created_by)
         VALUES (?, ?, NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''))"
    );
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param(
        'ssssssss',
        $code,
        $form['customer_name'],
        $form['email'],
        $form['phone'],
        $form['bike_name'],
        $form['serial_number'],
        $form['note'],
        $createdBy
    );

    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }

    $id = (int) $mysqli->insert_id;
    $stmt->close();

    return fetch_customer_card_by_id($mysqli, $id);
}

function fetch_customer_card_by_id(mysqli $mysqli, int $id): ?array
{
    if ($id <= 0 || !ensure_customer_cards_table($mysqli)) {
        return null;
    }

    $stmt = $mysqli->prepare('SELECT id, card_code, customer_name, email, phone, bike_name, serial_number, note, created_by, created_at FROM customer_cards WHERE id = ? LIMIT 1');
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $card = $result instanceof mysqli_result ? $result->fetch_assoc() : null;
    if ($result instanceof mysqli_result) {
        $result->free();
    }
    $stmt->close();

    return is_array($card) ? $card : null;
}

function fetch_customer_cards(mysqli $mysqli, int $limit = 50): ?array
{
    if (!ensure_customer_cards_table($mysqli)) {
        return null;
    }

    $limit = max(1, min(200, $limit));
    $sql = sprintf(
        'SELECT id, card_code, customer_name, email, phone, bike_name, serial_number, note, created_by, created_at FROM customer_cards ORDER BY id DESC LIMIT %d',
        $limit
    );
    $result = $mysqli->query($sql);
    if (!$result) {
        return null;
    }

    $cards = [];
    while ($row = $result->fetch_assoc()) {
        $cards[] = $row;
    }
    $result->free();

    return $cards;
}
