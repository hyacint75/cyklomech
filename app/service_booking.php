<?php

require_once __DIR__ . '/auth.php';

function service_booking_access_session_key(): string
{
    return 'service_booking_access_granted';
}

function service_booking_last_activity_session_key(): string
{
    return 'service_booking_last_activity';
}

function service_booking_normalize_serial(string $serial): string
{
    $normalized = preg_replace('/[^A-Za-z0-9]+/', '', strtoupper(trim($serial)));

    return $normalized === null ? '' : $normalized;
}

function service_booking_default_password_hash(): string
{
    return '$2y$10$KerhuRejUGmOXyg9iXEvveTLkJWcLF9uxSXjUL8.Je2wZeZEXm75e';
}

function service_booking_status_options(): array
{
    return [
        'nova' => 'Nova',
        'potvrzena' => 'Potvrzena',
        'vyrizena' => 'Vyrizena',
        'zrusena' => 'Zrusena',
    ];
}

function service_booking_type_options(): array
{
    return [
        'Garancni prohlidka',
        'Bezny servis',
        'Serizeni kola',
        'Diagnostika elektrokola',
        'Defekt nebo nepojizdne kolo',
        'Jiny pozadavek',
    ];
}

function service_booking_email_templates(): array
{
    return [
        'potvrdit' => [
            'status' => 'potvrzena',
            'subject' => 'Potvrzeni rezervace servisu - CykloFlos',
        ],
        'odmitnout' => [
            'status' => 'zrusena',
            'subject' => 'Vyjadreni k rezervaci servisu - CykloFlos',
        ],
    ];
}

function service_booking_default_form(): array
{
    return [
        'customer_name' => '',
        'phone' => '',
        'email' => '',
        'preferred_date' => '',
        'preferred_time' => '',
        'service_type' => service_booking_type_options()[0],
        'bike_info' => '',
        'note' => '',
    ];
}

function service_booking_ok_message(string $okKey): string
{
    return match ($okKey) {
        'access_granted' => 'Pristup do rezervacniho systemu byl povolen.',
        'access_expired' => 'Pristup do rezervacniho systemu vyprsel kvuli necinnosti.',
        'reservation_created' => 'Rezervace servisu byla odeslana.',
        'service_serial_created' => 'Výrobní číslo bylo uloženo.',
        'service_serial_deleted' => 'Výrobní číslo bylo smazáno.',
        'service_reservation_updated' => 'Stav rezervace byl aktualizovan.',
        'service_reservation_deleted' => 'Rezervace byla smazana.',
        'service_reservation_confirmed' => 'Rezervace byla potvrzena a e-mail byl odeslan.',
        'service_reservation_rejected' => 'Rezervace byla odmitnuta a e-mail byl odeslan.',
        default => '',
    };
}

function service_booking_is_unlocked(): bool
{
    ensure_session_started();

    $isUnlocked = (bool) ($_SESSION[service_booking_access_session_key()] ?? false);
    if (!$isUnlocked) {
        return false;
    }

    $now = time();
    $lastActivity = (int) ($_SESSION[service_booking_last_activity_session_key()] ?? 0);
    if ($lastActivity > 0 && ($now - $lastActivity) > service_session_inactivity_timeout_seconds()) {
        service_booking_revoke_access();
        return false;
    }

    $_SESSION[service_booking_last_activity_session_key()] = $now;

    return true;
}

function service_booking_grant_access(): void
{
    ensure_session_started();
    $_SESSION[service_booking_access_session_key()] = true;
    $_SESSION[service_booking_last_activity_session_key()] = time();
}

function service_booking_revoke_access(): void
{
    ensure_session_started();
    unset($_SESSION[service_booking_access_session_key()]);
    unset($_SESSION[service_booking_last_activity_session_key()]);
}

function service_bike_serials_redirect_target(): string
{
    return admin_form_redirect_target('service_work.php');
}

function ensure_service_bike_serials_table(mysqli $mysqli): void
{
    try {
        $mysqli->query(
            'CREATE TABLE IF NOT EXISTS service_bike_serials (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                sale_date DATE DEFAULT NULL,
                service_date DATE DEFAULT NULL,
                serial_number VARCHAR(120) NOT NULL,
                bike_description VARCHAR(255) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_service_bike_serials_serial (serial_number),
                INDEX idx_service_bike_serials_sale_date (sale_date),
                INDEX idx_service_bike_serials_service_date (service_date)
            )'
        );

        $columns = [];
        $result = $mysqli->query('SHOW COLUMNS FROM service_bike_serials');
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $columns[] = (string) ($row['Field'] ?? '');
                if (($row['Field'] ?? '') === 'sale_date' && strtoupper((string) ($row['Null'] ?? '')) !== 'YES') {
                    $mysqli->query('ALTER TABLE service_bike_serials MODIFY sale_date DATE DEFAULT NULL');
                }
            }
            $result->free();
        }

        if (!in_array('service_date', $columns, true)) {
            $mysqli->query('ALTER TABLE service_bike_serials ADD COLUMN service_date DATE DEFAULT NULL AFTER sale_date');
            $mysqli->query('ALTER TABLE service_bike_serials ADD INDEX idx_service_bike_serials_service_date (service_date)');
        }

        if (!in_array('bike_description', $columns, true)) {
            $mysqli->query('ALTER TABLE service_bike_serials ADD COLUMN bike_description VARCHAR(255) DEFAULT NULL AFTER serial_number');
        }

        $result = $mysqli->query("SHOW INDEX FROM service_bike_serials WHERE Key_name = 'uniq_service_bike_serials_serial'");
        if ($result instanceof mysqli_result) {
            if ($result->num_rows > 0) {
                $mysqli->query('ALTER TABLE service_bike_serials DROP INDEX uniq_service_bike_serials_serial');
            }
            $result->free();
        }

        $result = $mysqli->query("SHOW INDEX FROM service_bike_serials WHERE Key_name = 'idx_service_bike_serials_serial'");
        if ($result instanceof mysqli_result) {
            $hasSerialIndex = $result->num_rows > 0;
            $result->free();
            if (!$hasSerialIndex) {
                $mysqli->query('ALTER TABLE service_bike_serials ADD INDEX idx_service_bike_serials_serial (serial_number)');
            }
        }
    } catch (Throwable $exception) {
        return;
    }
}

function ensure_service_booking_access_row(mysqli $mysqli): void
{
    $defaultHash = service_booking_default_password_hash();

    try {
        $stmt = $mysqli->prepare('INSERT INTO service_booking_access (id, password_hash) VALUES (1, ?) ON DUPLICATE KEY UPDATE id = id');
        if (!$stmt) {
            return;
        }

        $stmt->bind_param('s', $defaultHash);
        $stmt->execute();
        $stmt->close();
    } catch (Throwable $exception) {
        return;
    }
}

function fetch_service_booking_access_hash(mysqli $mysqli): ?string
{
    ensure_service_booking_access_row($mysqli);

    try {
        $result = $mysqli->query('SELECT password_hash FROM service_booking_access WHERE id = 1 LIMIT 1');
    } catch (Throwable $exception) {
        return service_booking_default_password_hash();
    }

    if (!$result) {
        return service_booking_default_password_hash();
    }

    $row = $result->fetch_assoc();
    $result->free();

    return isset($row['password_hash']) ? (string) $row['password_hash'] : service_booking_default_password_hash();
}

function verify_service_booking_password(mysqli $mysqli, string $password): bool
{
    $hash = fetch_service_booking_access_hash($mysqli);
    if ($hash === null || $password === '') {
        return false;
    }

    return password_verify($password, $hash);
}

function service_booking_serial_exists(mysqli $mysqli, string $serialNumber): bool
{
    $normalizedSerial = service_booking_normalize_serial($serialNumber);
    if ($normalizedSerial === '') {
        return false;
    }

    ensure_service_bike_serials_table($mysqli);

    try {
        $stmt = $mysqli->prepare('SELECT id FROM service_bike_serials WHERE serial_number = ? LIMIT 1');
    } catch (Throwable $exception) {
        return false;
    }

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('s', $normalizedSerial);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result ? $result->fetch_assoc() !== null : false;
    $stmt->close();

    return $exists;
}

function service_booking_customer_card_matches(mysqli $mysqli, string $accessKey): bool
{
    $accessKey = trim($accessKey);
    if ($accessKey === '') {
        return false;
    }

    $normalizedAccessKey = service_booking_normalize_serial($accessKey);

    try {
        $result = $mysqli->query('SELECT card_code, customer_name, serial_number FROM customer_cards');
    } catch (Throwable $exception) {
        return false;
    }

    if (!$result) {
        return false;
    }

    while ($row = $result->fetch_assoc()) {
        $cardCode = trim((string) ($row['card_code'] ?? ''));
        $customerName = trim((string) ($row['customer_name'] ?? ''));
        $serialNumber = trim((string) ($row['serial_number'] ?? ''));

        if ($customerName !== '' && mb_strtolower($customerName, 'UTF-8') === mb_strtolower($accessKey, 'UTF-8')) {
            $result->free();
            return true;
        }

        if ($cardCode !== '' && service_booking_normalize_serial($cardCode) === $normalizedAccessKey) {
            $result->free();
            return true;
        }

        if ($serialNumber !== '' && service_booking_normalize_serial($serialNumber) === $normalizedAccessKey) {
            $result->free();
            return true;
        }
    }

    $result->free();

    return false;
}

function service_booking_access_key_exists(mysqli $mysqli, string $accessKey): bool
{
    return service_booking_serial_exists($mysqli, $accessKey)
        || service_booking_customer_card_matches($mysqli, $accessKey);
}

function fetch_service_bike_serials(mysqli $mysqli): array
{
    ensure_service_bike_serials_table($mysqli);

    $rows = [];

    try {
        $result = $mysqli->query('SELECT id, sale_date, service_date, serial_number, bike_description, created_at FROM service_bike_serials ORDER BY COALESCE(service_date, sale_date) DESC, id DESC');
    } catch (Throwable $exception) {
        return [];
    }

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $result->free();
    }

    return $rows;
}

function create_service_bike_serial(mysqli $mysqli, string $saleDate, string $serviceDate, string $serialNumber, string $bikeDescription): bool
{
    $normalizedSerial = service_booking_normalize_serial($serialNumber);
    if ($normalizedSerial === '') {
        return false;
    }

    ensure_service_bike_serials_table($mysqli);

    try {
        $stmt = $mysqli->prepare("INSERT INTO service_bike_serials (sale_date, service_date, serial_number, bike_description) VALUES (NULLIF(?, ''), NULLIF(?, ''), ?, NULLIF(?, ''))");
    } catch (Throwable $exception) {
        return false;
    }

    if (!$stmt) {
        return false;
    }

    $cleanDescription = trim($bikeDescription);
    $stmt->bind_param('ssss', $saleDate, $serviceDate, $normalizedSerial, $cleanDescription);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function delete_service_bike_serial(mysqli $mysqli, int $id): bool
{
    if ($id <= 0) {
        return false;
    }

    ensure_service_bike_serials_table($mysqli);

    try {
        $stmt = $mysqli->prepare('DELETE FROM service_bike_serials WHERE id = ?');
    } catch (Throwable $exception) {
        return false;
    }

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function ensure_service_sheet_tables(mysqli $mysqli): void
{
    try {
        $mysqli->query(
            'CREATE TABLE IF NOT EXISTS service_sheets (
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
            )'
        );

        $mysqli->query(
            'CREATE TABLE IF NOT EXISTS service_sheet_material_items (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                sheet_id INT UNSIGNED NOT NULL,
                item_name VARCHAR(160) NOT NULL,
                quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
                price DECIMAL(10,2) NOT NULL DEFAULT 0,
                sort_order INT UNSIGNED NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_service_sheet_material_items_sheet_id (sheet_id)
            )'
        );

        $mysqli->query(
            'CREATE TABLE IF NOT EXISTS service_sheet_labor_items (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                sheet_id INT UNSIGNED NOT NULL,
                operation_name VARCHAR(160) NOT NULL,
                price DECIMAL(10,2) NOT NULL DEFAULT 0,
                sort_order INT UNSIGNED NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_service_sheet_labor_items_sheet_id (sheet_id)
            )'
        );

        $result = $mysqli->query("SHOW INDEX FROM service_sheets WHERE Key_name = 'uniq_service_sheets_serial_id'");
        if ($result instanceof mysqli_result) {
            if ($result->num_rows > 0) {
                $mysqli->query('ALTER TABLE service_sheets DROP INDEX uniq_service_sheets_serial_id');
            }
            $result->free();
        }

        $result = $mysqli->query("SHOW INDEX FROM service_sheets WHERE Key_name = 'idx_service_sheets_serial_id'");
        if ($result instanceof mysqli_result) {
            $hasSerialIndex = $result->num_rows > 0;
            $result->free();
            if (!$hasSerialIndex) {
                $mysqli->query('ALTER TABLE service_sheets ADD INDEX idx_service_sheets_serial_id (serial_id)');
            }
        }
    } catch (Throwable $exception) {
        return;
    }
}

function fetch_service_bike_serial_by_id(mysqli $mysqli, int $id): ?array
{
    if ($id <= 0) {
        return null;
    }

    ensure_service_bike_serials_table($mysqli);

    try {
        $stmt = $mysqli->prepare('SELECT id, sale_date, service_date, serial_number, bike_description, created_at FROM service_bike_serials WHERE id = ? LIMIT 1');
    } catch (Throwable $exception) {
        return null;
    }

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $row ?: null;
}

function generate_service_sheet_number(mysqli $mysqli, string $repairDate): string
{
    ensure_service_sheet_tables($mysqli);

    $year = preg_match('/^\d{4}-\d{2}-\d{2}$/', $repairDate) === 1 ? substr($repairDate, 0, 4) : date('Y');
    $prefix = 'SL-' . $year . '-';
    $nextNumber = 1;

    try {
        $stmt = $mysqli->prepare('SELECT sheet_number FROM service_sheets WHERE sheet_number LIKE ? ORDER BY id DESC LIMIT 1');
    } catch (Throwable $exception) {
        return $prefix . '0001';
    }

    if ($stmt) {
        $likePrefix = $prefix . '%';
        $stmt->bind_param('s', $likePrefix);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if ($row !== null) {
            $lastSheetNumber = (string) ($row['sheet_number'] ?? '');
            if (preg_match('/(\d+)$/', $lastSheetNumber, $matches) === 1) {
                $nextNumber = ((int) $matches[1]) + 1;
            }
        }
    }

    return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
}

function create_service_sheet_for_serial(mysqli $mysqli, array $serviceBikeSerial): ?int
{
    $serialId = (int) ($serviceBikeSerial['id'] ?? 0);
    $repairDate = trim((string) ($serviceBikeSerial['service_date'] ?? ''));
    $serialNumber = trim((string) ($serviceBikeSerial['serial_number'] ?? ''));

    if ($serialId <= 0 || $repairDate === '' || $serialNumber === '') {
        return null;
    }

    ensure_service_sheet_tables($mysqli);
    $sheetNumber = generate_service_sheet_number($mysqli, $repairDate);
    $defaultRequest = trim((string) ($serviceBikeSerial['bike_description'] ?? ''));

    try {
        $stmt = $mysqli->prepare("INSERT INTO service_sheets (serial_id, sheet_number, repair_date, serial_number, request_text) VALUES (?, ?, ?, ?, NULLIF(?, ''))");
    } catch (Throwable $exception) {
        return null;
    }

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('issss', $serialId, $sheetNumber, $repairDate, $serialNumber, $defaultRequest);
    $ok = $stmt->execute();
    $newId = $ok ? (int) $mysqli->insert_id : null;
    $stmt->close();

    return $newId;
}

function create_service_sheet_for_serial_id(mysqli $mysqli, int $serialId): ?int
{
    if ($serialId <= 0) {
        return null;
    }

    ensure_service_sheet_tables($mysqli);

    $serviceBikeSerial = fetch_service_bike_serial_by_id($mysqli, $serialId);
    if ($serviceBikeSerial === null) {
        return null;
    }

    return create_service_sheet_for_serial($mysqli, $serviceBikeSerial);
}

function fetch_service_sheet_by_id(mysqli $mysqli, int $id): ?array
{
    if ($id <= 0) {
        return null;
    }

    ensure_service_sheet_tables($mysqli);

    try {
        $stmt = $mysqli->prepare('SELECT id, serial_id, sheet_number, repair_date, serial_number, request_text, material_total, labor_total, total_price, created_at, updated_at FROM service_sheets WHERE id = ? LIMIT 1');
    } catch (Throwable $exception) {
        return null;
    }

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $row ?: null;
}

function fetch_service_sheets(mysqli $mysqli): array
{
    ensure_service_sheet_tables($mysqli);
    $rows = [];

    try {
        $result = $mysqli->query('SELECT id, serial_id, sheet_number, repair_date, serial_number, request_text, material_total, labor_total, total_price, created_at, updated_at FROM service_sheets ORDER BY repair_date DESC, id DESC');
    } catch (Throwable $exception) {
        return [];
    }

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $result->free();
    }

    return $rows;
}

function fetch_service_sheets_grouped_by_serial_id(mysqli $mysqli): array
{
    $grouped = [];

    foreach (fetch_service_sheets($mysqli) as $serviceSheet) {
        $serialId = (int) ($serviceSheet['serial_id'] ?? 0);
        if ($serialId <= 0) {
            continue;
        }

        if (!isset($grouped[$serialId])) {
            $grouped[$serialId] = [];
        }

        $grouped[$serialId][] = $serviceSheet;
    }

    return $grouped;
}

function fetch_service_sheet_material_items(mysqli $mysqli, int $sheetId): array
{
    if ($sheetId <= 0) {
        return [];
    }

    ensure_service_sheet_tables($mysqli);
    $rows = [];

    try {
        $stmt = $mysqli->prepare('SELECT id, item_name, quantity, price, sort_order FROM service_sheet_material_items WHERE sheet_id = ? ORDER BY sort_order ASC, id ASC');
    } catch (Throwable $exception) {
        return [];
    }

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param('i', $sheetId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }

    $stmt->close();
    return $rows;
}

function fetch_service_sheet_labor_items(mysqli $mysqli, int $sheetId): array
{
    if ($sheetId <= 0) {
        return [];
    }

    ensure_service_sheet_tables($mysqli);
    $rows = [];

    try {
        $stmt = $mysqli->prepare('SELECT id, operation_name, price, sort_order FROM service_sheet_labor_items WHERE sheet_id = ? ORDER BY sort_order ASC, id ASC');
    } catch (Throwable $exception) {
        return [];
    }

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param('i', $sheetId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }

    $stmt->close();
    return $rows;
}

function normalize_service_sheet_material_items(array $names, array $quantities, array $prices): array
{
    $items = [];
    $rowCount = max(count($names), count($quantities), count($prices));

    for ($index = 0; $index < $rowCount; $index++) {
        $name = trim((string) ($names[$index] ?? ''));
        $quantityRaw = trim((string) ($quantities[$index] ?? ''));
        $priceRaw = trim((string) ($prices[$index] ?? ''));

        if ($name === '' && $quantityRaw === '' && $priceRaw === '') {
            continue;
        }

        $quantity = (float) str_replace(',', '.', $quantityRaw);
        $price = (float) str_replace(',', '.', $priceRaw);

        $items[] = [
            'item_name' => $name,
            'quantity' => $quantity,
            'price' => $price,
        ];
    }

    return $items;
}

function normalize_service_sheet_labor_items(array $names, array $prices): array
{
    $items = [];
    $rowCount = max(count($names), count($prices));

    for ($index = 0; $index < $rowCount; $index++) {
        $name = trim((string) ($names[$index] ?? ''));
        $priceRaw = trim((string) ($prices[$index] ?? ''));

        if ($name === '' && $priceRaw === '') {
            continue;
        }

        $price = (float) str_replace(',', '.', $priceRaw);

        $items[] = [
            'operation_name' => $name,
            'price' => $price,
        ];
    }

    return $items;
}

function validate_service_sheet_input(string $repairDate, string $serialNumber, array $materialItems, array $laborItems, array &$errors): void
{
    if ($repairDate === '' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $repairDate) !== 1) {
        $errors[] = 'Datum opravy musí mít platný formát.';
    }

    if (trim($serialNumber) === '') {
        $errors[] = 'Výrobní číslo kola je povinné.';
    }

    foreach ($materialItems as $item) {
        if (trim((string) ($item['item_name'] ?? '')) === '') {
            $errors[] = 'Každá položka materiálu musí mít název dílu.';
            break;
        }

        if ((float) ($item['quantity'] ?? 0) <= 0) {
            $errors[] = 'Každá položka materiálu musí mít počet kusů větší než 0.';
            break;
        }

        if ((float) ($item['price'] ?? -1) < 0) {
            $errors[] = 'Cena materiálu nemůže být záporná.';
            break;
        }
    }

    foreach ($laborItems as $item) {
        if (trim((string) ($item['operation_name'] ?? '')) === '') {
            $errors[] = 'Každá pracovní operace musí mít název.';
            break;
        }

        if ((float) ($item['price'] ?? -1) < 0) {
            $errors[] = 'Cena práce nemůže být záporná.';
            break;
        }
    }
}

function service_sheet_redirect_target(int $sheetId): string
{
    return admin_form_redirect_target('service_sheet.php?sheet_id=' . $sheetId);
}

function save_service_sheet(mysqli $mysqli, int $sheetId, string $requestText, array $materialItems, array $laborItems): bool
{
    $serviceSheet = fetch_service_sheet_by_id($mysqli, $sheetId);
    if ($serviceSheet === null) {
        return false;
    }

    $repairDate = trim((string) ($serviceSheet['repair_date'] ?? ''));
    $serialNumber = trim((string) ($serviceSheet['serial_number'] ?? ''));
    $errors = [];
    validate_service_sheet_input($repairDate, $serialNumber, $materialItems, $laborItems, $errors);
    if ($errors !== []) {
        return false;
    }

    $materialTotal = 0.0;
    foreach ($materialItems as $item) {
        $materialTotal += ((float) $item['quantity']) * ((float) $item['price']);
    }

    $laborTotal = 0.0;
    foreach ($laborItems as $item) {
        $laborTotal += (float) $item['price'];
    }

    $totalPrice = $materialTotal + $laborTotal;

    $mysqli->begin_transaction();

    try {
        $stmtSheet = $mysqli->prepare("UPDATE service_sheets SET request_text = NULLIF(?, ''), material_total = ?, labor_total = ?, total_price = ? WHERE id = ?");
        if (!$stmtSheet) {
            throw new RuntimeException('missing sheet statement');
        }

        $stmtSheet->bind_param('sdddi', $requestText, $materialTotal, $laborTotal, $totalPrice, $sheetId);
        if (!$stmtSheet->execute()) {
            $stmtSheet->close();
            throw new RuntimeException('sheet update failed');
        }
        $stmtSheet->close();

        $stmtDeleteMaterial = $mysqli->prepare('DELETE FROM service_sheet_material_items WHERE sheet_id = ?');
        $stmtDeleteLabor = $mysqli->prepare('DELETE FROM service_sheet_labor_items WHERE sheet_id = ?');
        if (!$stmtDeleteMaterial || !$stmtDeleteLabor) {
            throw new RuntimeException('missing delete statement');
        }

        $stmtDeleteMaterial->bind_param('i', $sheetId);
        $stmtDeleteMaterial->execute();
        $stmtDeleteMaterial->close();

        $stmtDeleteLabor->bind_param('i', $sheetId);
        $stmtDeleteLabor->execute();
        $stmtDeleteLabor->close();

        if ($materialItems !== []) {
            $stmtMaterial = $mysqli->prepare('INSERT INTO service_sheet_material_items (sheet_id, item_name, quantity, price, sort_order) VALUES (?, ?, ?, ?, ?)');
            if (!$stmtMaterial) {
                throw new RuntimeException('missing material insert statement');
            }

            foreach ($materialItems as $index => $item) {
                $itemName = (string) $item['item_name'];
                $quantity = (float) $item['quantity'];
                $price = (float) $item['price'];
                $sortOrder = $index + 1;
                $stmtMaterial->bind_param('isddi', $sheetId, $itemName, $quantity, $price, $sortOrder);
                if (!$stmtMaterial->execute()) {
                    $stmtMaterial->close();
                    throw new RuntimeException('material insert failed');
                }
            }

            $stmtMaterial->close();
        }

        if ($laborItems !== []) {
            $stmtLabor = $mysqli->prepare('INSERT INTO service_sheet_labor_items (sheet_id, operation_name, price, sort_order) VALUES (?, ?, ?, ?)');
            if (!$stmtLabor) {
                throw new RuntimeException('missing labor insert statement');
            }

            foreach ($laborItems as $index => $item) {
                $operationName = (string) $item['operation_name'];
                $price = (float) $item['price'];
                $sortOrder = $index + 1;
                $stmtLabor->bind_param('isdi', $sheetId, $operationName, $price, $sortOrder);
                if (!$stmtLabor->execute()) {
                    $stmtLabor->close();
                    throw new RuntimeException('labor insert failed');
                }
            }

            $stmtLabor->close();
        }

        $mysqli->commit();
    } catch (Throwable $exception) {
        $mysqli->rollback();
        return false;
    }

    return true;
}

function service_sheet_pdf_escape(string $text): string
{
    $asciiText = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    if ($asciiText === false) {
        $asciiText = $text;
    }

    return str_replace(['\\', '(', ')'], ['\\\\', '\(', '\)'], (string) $asciiText);
}

function service_sheet_logo_path(): string
{
    return dirname(__DIR__) . '/uploads/hero/Flos-2.0_1.png';
}

function parse_png_for_pdf(string $path): ?array
{
    if (!is_file($path)) {
        return null;
    }

    $data = @file_get_contents($path);
    if ($data === false || substr($data, 0, 8) !== "\x89PNG\r\n\x1a\n") {
        return null;
    }

    $offset = 8;
    $width = 0;
    $height = 0;
    $bitDepth = 8;
    $colorType = 6;
    $compression = 0;
    $filter = 0;
    $interlace = 0;
    $idat = '';

    while ($offset + 8 <= strlen($data)) {
        $length = unpack('N', substr($data, $offset, 4))[1];
        $offset += 4;
        $type = substr($data, $offset, 4);
        $offset += 4;
        $chunkData = substr($data, $offset, $length);
        $offset += $length + 4;

        if ($type === 'IHDR') {
            $header = unpack('Nwidth/Nheight/Cbit/Ccolor/Ccompression/Cfilter/Cinterlace', $chunkData);
            $width = (int) $header['width'];
            $height = (int) $header['height'];
            $bitDepth = (int) $header['bit'];
            $colorType = (int) $header['color'];
            $compression = (int) $header['compression'];
            $filter = (int) $header['filter'];
            $interlace = (int) $header['interlace'];
        } elseif ($type === 'IDAT') {
            $idat .= $chunkData;
        } elseif ($type === 'IEND') {
            break;
        }
    }

    if ($width <= 0 || $height <= 0 || $bitDepth !== 8 || $compression !== 0 || $filter !== 0 || $interlace !== 0) {
        return null;
    }

    if (!in_array($colorType, [2, 6], true)) {
        return null;
    }

    $decoded = zlib_decode($idat);
    if ($decoded === false) {
        return null;
    }

    $channels = $colorType === 6 ? 4 : 3;
    $bytesPerPixel = $channels;
    $stride = $width * $channels;
    $expectedLength = ($stride + 1) * $height;
    if (strlen($decoded) < $expectedLength) {
        return null;
    }

    $rgbData = '';
    $alphaData = '';
    $previousRow = str_repeat("\0", $stride);
    $cursor = 0;

    for ($rowIndex = 0; $rowIndex < $height; $rowIndex++) {
        $filterType = ord($decoded[$cursor]);
        $cursor++;
        $scanline = substr($decoded, $cursor, $stride);
        $cursor += $stride;
        $recon = '';

        for ($index = 0; $index < $stride; $index++) {
            $raw = ord($scanline[$index]);
            $left = $index >= $bytesPerPixel ? ord($recon[$index - $bytesPerPixel]) : 0;
            $up = ord($previousRow[$index]);
            $upperLeft = $index >= $bytesPerPixel ? ord($previousRow[$index - $bytesPerPixel]) : 0;

            if ($filterType === 0) {
                $value = $raw;
            } elseif ($filterType === 1) {
                $value = ($raw + $left) & 0xFF;
            } elseif ($filterType === 2) {
                $value = ($raw + $up) & 0xFF;
            } elseif ($filterType === 3) {
                $value = ($raw + intdiv($left + $up, 2)) & 0xFF;
            } elseif ($filterType === 4) {
                $p = $left + $up - $upperLeft;
                $pa = abs($p - $left);
                $pb = abs($p - $up);
                $pc = abs($p - $upperLeft);
                $predictor = ($pa <= $pb && $pa <= $pc) ? $left : (($pb <= $pc) ? $up : $upperLeft);
                $value = ($raw + $predictor) & 0xFF;
            } else {
                return null;
            }

            $recon .= chr($value);
        }

        $previousRow = $recon;

        if ($colorType === 6) {
            for ($pixel = 0; $pixel < $width; $pixel++) {
                $base = $pixel * 4;
                $rgbData .= substr($recon, $base, 3);
                $alphaData .= $recon[$base + 3];
            }
        } else {
            $rgbData .= $recon;
        }
    }

    return [
        'width' => $width,
        'height' => $height,
        'rgb' => gzcompress($rgbData),
        'alpha' => $alphaData !== '' ? gzcompress($alphaData) : null,
    ];
}

function build_service_sheet_pdf_blocks(array $serviceSheet, array $materialItems, array $laborItems): array
{
    $materialTotal = number_format((float) ($serviceSheet['material_total'] ?? 0), 2, ',', ' ') . ' Kc';
    $laborTotal = number_format((float) ($serviceSheet['labor_total'] ?? 0), 2, ',', ' ') . ' Kc';
    $grandTotal = number_format((float) ($serviceSheet['total_price'] ?? 0), 2, ',', ' ') . ' Kc';

    $materialLines = [];
    if ($materialItems === []) {
        $materialLines[] = '-';
    } else {
        foreach ($materialItems as $item) {
            $materialLines[] = sprintf(
                '%s | %s ks | %s Kc',
                trim((string) ($item['item_name'] ?? '')),
                number_format((float) ($item['quantity'] ?? 0), 2, ',', ' '),
                number_format((float) ($item['price'] ?? 0), 2, ',', ' ')
            );
        }
    }

    $laborLines = [];
    if ($laborItems === []) {
        $laborLines[] = '-';
    } else {
        foreach ($laborItems as $item) {
            $laborLines[] = sprintf(
                '%s | %s Kc',
                trim((string) ($item['operation_name'] ?? '')),
                number_format((float) ($item['price'] ?? 0), 2, ',', ' ')
            );
        }
    }

    return [
        [
            'title' => 'Zakazka',
            'lines' => [
                'Servisni list: ' . (string) ($serviceSheet['sheet_number'] ?? ''),
                'Datum opravy: ' . (string) ($serviceSheet['repair_date'] ?? ''),
                'Vyrobni cislo kola: ' . (string) ($serviceSheet['serial_number'] ?? ''),
                'Pozadavek: ' . trim((string) ($serviceSheet['request_text'] ?? '')),
            ],
        ],
        [
            'title' => 'Material',
            'lines' => $materialLines,
        ],
        [
            'title' => 'Prace',
            'lines' => $laborLines,
        ],
        [
            'title' => 'Rekapitulace',
            'lines' => [
                'Material celkem: ' . $materialTotal,
                'Prace celkem: ' . $laborTotal,
                'Celkova cena: ' . $grandTotal,
            ],
        ],
    ];
}

function generate_service_sheet_pdf(array $serviceSheet, array $materialItems, array $laborItems): string
{
    $blocks = build_service_sheet_pdf_blocks($serviceSheet, $materialItems, $laborItems);
    $logo = parse_png_for_pdf(service_sheet_logo_path());
    $objects = [];

    $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
    $objects[2] = '<< /Type /Pages /Count 1 /Kids [3 0 R] >>';

    $resourceParts = ['/Font << /F1 5 0 R /F2 6 0 R >>'];
    if ($logo !== null) {
        $resourceParts[] = '/XObject << /Im1 7 0 R' . ($logo['alpha'] !== null ? ' /Im1Mask 8 0 R' : '') . ' >>';
    }
    $objects[3] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << ' . implode(' ', $resourceParts) . ' >> /Contents 4 0 R >>';

    $contentLines = [];
    $contentLines[] = '0.97 0.96 0.92 rg';
    $contentLines[] = '0 762 595 80 re f';
    $contentLines[] = '0.48 0.36 0.06 rg';
    $contentLines[] = '0 742 595 20 re f';

    if ($logo !== null) {
        $targetWidth = 110;
        $targetHeight = ($logo['height'] / $logo['width']) * $targetWidth;
        $contentLines[] = 'q';
        $contentLines[] = sprintf('%.2f 0 0 %.2f 40 %.2f cm', $targetWidth, $targetHeight, 785 - $targetHeight);
        $contentLines[] = '/Im1 Do';
        $contentLines[] = 'Q';
    }

    $contentLines[] = 'BT';
    $contentLines[] = '/F2 20 Tf';
    $contentLines[] = '180 797 Td';
    $contentLines[] = '(' . service_sheet_pdf_escape('Servisni list') . ') Tj';
    $contentLines[] = '0 -24 Td';
    $contentLines[] = '/F1 11 Tf';
    $contentLines[] = '(' . service_sheet_pdf_escape('CykloFlos | ' . (string) ($serviceSheet['sheet_number'] ?? '')) . ') Tj';
    $contentLines[] = 'ET';

    $currentY = 700.0;
    foreach ($blocks as $blockIndex => $block) {
        $lineCount = max(1, count($block['lines']));
        $blockHeight = 34 + ($lineCount * 15);
        $currentY -= $blockHeight;

        $contentLines[] = '0.99 0.99 0.99 rg';
        $contentLines[] = sprintf('38 %.2f 519 %.2f re f', $currentY, $blockHeight);
        $contentLines[] = '0.85 0.83 0.78 RG';
        $contentLines[] = sprintf('38 %.2f 519 %.2f re S', $currentY, $blockHeight);
        $contentLines[] = '0.48 0.36 0.06 rg';
        $contentLines[] = sprintf('38 %.2f 519 22 re f', $currentY + $blockHeight - 22);

        $contentLines[] = 'BT';
        $contentLines[] = '/F2 12 Tf';
        $contentLines[] = sprintf('50 %.2f Td', $currentY + $blockHeight - 15);
        $contentLines[] = '(' . service_sheet_pdf_escape((string) $block['title']) . ') Tj';
        $contentLines[] = 'ET';

        $lineY = $currentY + $blockHeight - 38;
        foreach ($block['lines'] as $lineIndex => $line) {
            $font = ($block['title'] === 'Rekapitulace' && $lineIndex === 2) ? '/F2 12 Tf' : '/F1 10 Tf';
            $contentLines[] = 'BT';
            $contentLines[] = $font;
            $contentLines[] = sprintf('50 %.2f Td', $lineY);
            $contentLines[] = '(' . service_sheet_pdf_escape((string) $line) . ') Tj';
            $contentLines[] = 'ET';
            $lineY -= 15;
        }

        $currentY -= 16;
    }

    $footerText = 'CykloFlos | vygenerovano ' . date('d.m.Y H:i');
    $contentLines[] = 'BT';
    $contentLines[] = '/F1 9 Tf';
    $contentLines[] = '40 24 Td';
    $contentLines[] = '(' . service_sheet_pdf_escape($footerText) . ') Tj';
    $contentLines[] = 'ET';

    $stream = implode("\n", $contentLines) . "\n";
    $objects[4] = '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . "endstream";
    $objects[5] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
    $objects[6] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';

    if ($logo !== null) {
        $logoObject = '<< /Type /XObject /Subtype /Image /Width ' . $logo['width'] . ' /Height ' . $logo['height'] . ' /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /FlateDecode /Length ' . strlen($logo['rgb']);
        if ($logo['alpha'] !== null) {
            $logoObject .= ' /SMask 8 0 R';
        }
        $logoObject .= " >>\nstream\n" . $logo['rgb'] . "\nendstream";
        $objects[7] = $logoObject;

        if ($logo['alpha'] !== null) {
            $objects[8] = '<< /Type /XObject /Subtype /Image /Width ' . $logo['width'] . ' /Height ' . $logo['height'] . ' /ColorSpace /DeviceGray /BitsPerComponent 8 /Filter /FlateDecode /Length ' . strlen($logo['alpha']) . " >>\nstream\n" . $logo['alpha'] . "\nendstream";
        }
    }

    ksort($objects);

    $pdf = "%PDF-1.4\n";
    $offsets = [0];

    foreach ($objects as $objectId => $objectBody) {
        $offsets[$objectId] = strlen($pdf);
        $pdf .= $objectId . " 0 obj\n" . $objectBody . "\nendobj\n";
    }

    $xrefOffset = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";

    for ($index = 1; $index <= count($objects); $index++) {
        $pdf .= sprintf('%010d 00000 n ' . "\n", $offsets[$index] ?? 0);
    }

    $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
    $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";

    return $pdf;
}

function send_smtp_mail_with_attachment(string $toEmail, string $subject, string $message, string $attachmentName, string $attachmentContent, string $attachmentMimeType = 'application/pdf'): bool
{
    $config = service_booking_smtp_config();

    if ($config['password'] === '******') {
        return false;
    }

    $transport = ($config['encryption'] === 'ssl' ? 'ssl://' : '') . $config['host'] . ':' . $config['port'];
    $connection = @stream_socket_client($transport, $errorNumber, $errorMessage, (int) $config['timeout']);
    if (!is_resource($connection)) {
        return false;
    }

    stream_set_timeout($connection, (int) $config['timeout']);

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $encodedFromName = '=?UTF-8?B?' . base64_encode($config['from_name']) . '?=';
    $boundary = '=_cykloflos_' . md5($subject . $toEmail . $attachmentName);
    $headers = [
        'Date: ' . date(DATE_RFC2822),
        'From: ' . $encodedFromName . ' <' . $config['from_email'] . '>',
        'To: <' . $toEmail . '>',
        'Reply-To: ' . $config['reply_to'],
        'Subject: ' . $encodedSubject,
        'MIME-Version: 1.0',
        'Content-Type: multipart/mixed; boundary="' . $boundary . '"',
    ];

    $bodyParts = [];
    $bodyParts[] = '--' . $boundary;
    $bodyParts[] = 'Content-Type: text/plain; charset=UTF-8';
    $bodyParts[] = 'Content-Transfer-Encoding: 8bit';
    $bodyParts[] = '';
    $bodyParts[] = str_replace(["\r\n", "\r", "\n"], "\r\n", $message);
    $bodyParts[] = '--' . $boundary;
    $bodyParts[] = 'Content-Type: ' . $attachmentMimeType . '; name="' . $attachmentName . '"';
    $bodyParts[] = 'Content-Transfer-Encoding: base64';
    $bodyParts[] = 'Content-Disposition: attachment; filename="' . $attachmentName . '"';
    $bodyParts[] = '';
    $bodyParts[] = chunk_split(base64_encode($attachmentContent));
    $bodyParts[] = '--' . $boundary . '--';

    $body = implode("\r\n", $headers) . "\r\n\r\n" . implode("\r\n", $bodyParts) . "\r\n";

    $ok = smtp_expect($connection, [220])
        && smtp_command($connection, 'EHLO cyklomech.cz', [250])
        && smtp_command($connection, 'AUTH LOGIN', [334])
        && smtp_command($connection, base64_encode($config['username']), [334])
        && smtp_command($connection, base64_encode($config['password']), [235])
        && smtp_command($connection, 'MAIL FROM:<' . $config['from_email'] . '>', [250])
        && smtp_command($connection, 'RCPT TO:<' . $toEmail . '>', [250, 251])
        && smtp_command($connection, 'DATA', [354]);

    if ($ok) {
        fwrite($connection, $body . "\r\n.\r\n");
        $ok = smtp_expect($connection, [250]);
    }

    @smtp_command($connection, 'QUIT', [221]);
    fclose($connection);

    return $ok;
}

function build_service_sheet_email_message(array $serviceSheet): string
{
    return "Dobrý den,\n\nv příloze zasíláme servisní list " . (string) ($serviceSheet['sheet_number'] ?? '') . ".\n\nCykloFlos";
}

function send_service_sheet_email(array $serviceSheet, array $materialItems, array $laborItems, string $toEmail): bool
{
    if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $sheetNumber = trim((string) ($serviceSheet['sheet_number'] ?? ''));
    if ($sheetNumber === '') {
        return false;
    }

    $pdfContent = generate_service_sheet_pdf($serviceSheet, $materialItems, $laborItems);
    $subject = 'Servisní list ' . $sheetNumber . ' - CykloFlos';
    $message = build_service_sheet_email_message($serviceSheet);
    $attachmentName = 'servisni-list-' . strtolower(str_replace([' ', '/'], '-', $sheetNumber)) . '.pdf';

    return send_smtp_mail_with_attachment($toEmail, $subject, $message, $attachmentName, $pdfContent);
}

function update_service_booking_password(mysqli $mysqli, string $password): bool
{
    $hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $mysqli->prepare('UPDATE service_booking_access SET password_hash = ? WHERE id = 1');
    } catch (Throwable $exception) {
        return false;
    }

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('s', $hash);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function validate_service_booking_form(array $form, array &$errors): void
{
    if (trim((string) $form['customer_name']) === '') {
        $errors[] = 'Jmeno zakaznika je povinne.';
    }
    if (trim((string) $form['phone']) === '') {
        $errors[] = 'Telefon je povinny.';
    }
    if (trim((string) $form['preferred_date']) === '') {
        $errors[] = 'Preferovany termin je povinny.';
    } elseif (strtotime((string) $form['preferred_date']) < strtotime(date('Y-m-d'))) {
        $errors[] = 'Termin rezervace nesmi byt v minulosti.';
    }
    if (trim((string) $form['preferred_time']) === '') {
        $errors[] = 'Preferovany cas je povinny.';
    }
    if (trim((string) $form['service_type']) === '') {
        $errors[] = 'Typ servisu je povinny.';
    }
    if (trim((string) $form['bike_info']) === '') {
        $errors[] = 'Informace o kole jsou povinne.';
    }

    $email = trim((string) $form['email']);
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'E-mail nema platny format.';
    }
}

function insert_service_reservation(mysqli $mysqli, array $form): ?int
{
    try {
        $stmt = $mysqli->prepare("INSERT INTO service_reservations (customer_name, phone, email, preferred_date, preferred_time, service_type, bike_info, note, status) VALUES (?, ?, NULLIF(?, ''), ?, ?, ?, ?, NULLIF(?, ''), 'nova')");
    } catch (Throwable $exception) {
        return null;
    }

    if (!$stmt) {
        return null;
    }

    $customerName = trim((string) $form['customer_name']);
    $phone = trim((string) $form['phone']);
    $email = trim((string) $form['email']);
    $preferredDate = trim((string) $form['preferred_date']);
    $preferredTime = trim((string) $form['preferred_time']);
    $serviceType = trim((string) $form['service_type']);
    $bikeInfo = trim((string) $form['bike_info']);
    $note = trim((string) $form['note']);

    $stmt->bind_param('ssssssss', $customerName, $phone, $email, $preferredDate, $preferredTime, $serviceType, $bikeInfo, $note);
    $ok = $stmt->execute();
    $newId = $ok ? (int) $mysqli->insert_id : null;
    $stmt->close();

    return $newId;
}

function fetch_service_reservations(mysqli $mysqli): array
{
    $rows = [];
    $sql = 'SELECT id, customer_name, phone, email, preferred_date, preferred_time, service_type, bike_info, note, status, created_at, updated_at FROM service_reservations ORDER BY preferred_date ASC, preferred_time ASC, id DESC';

    try {
        $result = $mysqli->query($sql);
    } catch (Throwable $exception) {
        return [];
    }

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $result->free();
    }

    return $rows;
}

function fetch_service_reservation_by_id(mysqli $mysqli, int $id): ?array
{
    if ($id <= 0) {
        return null;
    }

    try {
        $stmt = $mysqli->prepare('SELECT id, customer_name, phone, email, preferred_date, preferred_time, service_type, bike_info, note, status, created_at, updated_at FROM service_reservations WHERE id = ? LIMIT 1');
    } catch (Throwable $exception) {
        return null;
    }

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $row ?: null;
}

function update_service_reservation_status(mysqli $mysqli, int $id, string $status): bool
{
    $allowedStatuses = array_keys(service_booking_status_options());
    if ($id <= 0 || !in_array($status, $allowedStatuses, true)) {
        return false;
    }

    try {
        $stmt = $mysqli->prepare('UPDATE service_reservations SET status = ? WHERE id = ?');
    } catch (Throwable $exception) {
        return false;
    }

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('si', $status, $id);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function delete_service_reservation(mysqli $mysqli, int $id): bool
{
    if ($id <= 0) {
        return false;
    }

    try {
        $stmt = $mysqli->prepare('DELETE FROM service_reservations WHERE id = ?');
    } catch (Throwable $exception) {
        return false;
    }

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function build_service_reservation_email_message(array $reservation, string $mode): ?string
{
    $templates = service_booking_email_templates();
    if (!isset($templates[$mode])) {
        return null;
    }

    $customerName = trim((string) ($reservation['customer_name'] ?? 'zakazniku'));
    $preferredDate = trim((string) ($reservation['preferred_date'] ?? ''));
    $preferredTime = trim((string) ($reservation['preferred_time'] ?? ''));
    $serviceType = trim((string) ($reservation['service_type'] ?? 'servis kola'));
    $bikeInfo = trim((string) ($reservation['bike_info'] ?? ''));

    if ($mode === 'potvrdit') {
        return "Dobry den {$customerName},\n\npotvrzujeme prijeti vasi rezervace servisu.\n\nTermin: {$preferredDate}\nCas: {$preferredTime}\nTyp servisu: {$serviceType}\nKolo / zavada: {$bikeInfo}\n\nV pripade zmeny terminu vas budeme kontaktovat.\n\nCykloFlos";
    }

    return "Dobry den {$customerName},\n\nbohuzel nemuzeme potvrdit vasi rezervaci servisu v pozadovanem terminu.\n\nPozadovany termin: {$preferredDate}\nCas: {$preferredTime}\nTyp servisu: {$serviceType}\nKolo / zavada: {$bikeInfo}\n\nProsim, kontaktujte nas kvuli domluve nahradniho terminu.\n\nCykloFlos";
}

function service_booking_smtp_config(): array
{
    return [
        'host' => 'mail.webglobe.cz',
        'port' => 465,
        'encryption' => 'ssl',
        'username' => 'cyklomech@cyklomech.cz',
        'password' => '!0kWBcBc',
        'from_email' => 'cyklomech@cyklomech.cz',
        'from_name' => 'CykloFlos',
        'reply_to' => 'cyklomech@cyklomech.cz',
        'timeout' => 20,
    ];
}

function smtp_expect($connection, array $allowedCodes): bool
{
    $response = '';

    while (($line = fgets($connection, 512)) !== false) {
        $response .= $line;
        if (preg_match('/^\d{3}\s/', $line) === 1) {
            break;
        }
    }

    if ($response === '' || preg_match('/^(\d{3})/m', $response, $matches) !== 1) {
        return false;
    }

    return in_array((int) $matches[1], $allowedCodes, true);
}

function smtp_command($connection, string $command, array $allowedCodes): bool
{
    fwrite($connection, $command . "\r\n");

    return smtp_expect($connection, $allowedCodes);
}

function send_smtp_mail(string $toEmail, string $subject, string $message): bool
{
    $config = service_booking_smtp_config();

    if ($config['password'] === '******') {
        return false;
    }

    $transport = ($config['encryption'] === 'ssl' ? 'ssl://' : '') . $config['host'] . ':' . $config['port'];
    $connection = @stream_socket_client($transport, $errorNumber, $errorMessage, (int) $config['timeout']);
    if (!is_resource($connection)) {
        return false;
    }

    stream_set_timeout($connection, (int) $config['timeout']);

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $encodedFromName = '=?UTF-8?B?' . base64_encode($config['from_name']) . '?=';
    $headers = [
        'Date: ' . date(DATE_RFC2822),
        'From: ' . $encodedFromName . ' <' . $config['from_email'] . '>',
        'To: <' . $toEmail . '>',
        'Reply-To: ' . $config['reply_to'],
        'Subject: ' . $encodedSubject,
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
    ];
    $body = implode("\r\n", $headers) . "\r\n\r\n" . str_replace(["\r\n", "\r", "\n"], "\r\n", $message) . "\r\n";

    $ok = smtp_expect($connection, [220])
        && smtp_command($connection, 'EHLO cyklomech.cz', [250])
        && smtp_command($connection, 'AUTH LOGIN', [334])
        && smtp_command($connection, base64_encode($config['username']), [334])
        && smtp_command($connection, base64_encode($config['password']), [235])
        && smtp_command($connection, 'MAIL FROM:<' . $config['from_email'] . '>', [250])
        && smtp_command($connection, 'RCPT TO:<' . $toEmail . '>', [250, 251])
        && smtp_command($connection, 'DATA', [354]);

    if ($ok) {
        fwrite($connection, $body . "\r\n.\r\n");
        $ok = smtp_expect($connection, [250]);
    }

    @smtp_command($connection, 'QUIT', [221]);
    fclose($connection);

    return $ok;
}

function send_service_reservation_email(array $reservation, string $mode): bool
{
    $templates = service_booking_email_templates();
    if (!isset($templates[$mode])) {
        return false;
    }

    $email = trim((string) ($reservation['email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $subject = $templates[$mode]['subject'];
    $message = build_service_reservation_email_message($reservation, $mode);
    if ($message === null) {
        return false;
    }

    try {
        return send_smtp_mail($email, $subject, $message);
    } catch (Throwable $exception) {
        return false;
    }
}

function admin_handle_update_service_access_password(mysqli $mysqli, array &$errors): void
{
    $password = (string) ($_POST['service_access_password'] ?? '');
    $passwordConfirm = (string) ($_POST['service_access_password_confirm'] ?? '');

    if ($password === '') {
        $errors[] = 'Nove heslo do rezervaci je povinne.';
        return;
    }
    if (strlen($password) < 6) {
        $errors[] = 'Heslo do rezervaci musi mit alespon 6 znaku.';
        return;
    }
    if ($password !== $passwordConfirm) {
        $errors[] = 'Hesla do rezervaci se neshoduji.';
        return;
    }

    ensure_service_booking_access_row($mysqli);
    if (!update_service_booking_password($mysqli, $password)) {
        $errors[] = 'Heslo do rezervaci se nepodarilo ulozit.';
        return;
    }

    write_audit_log($mysqli, 'update', 'service_booking_access', 1, []);
    admin_redirect_with_status('service_access_updated', 'service_admin.php');
}

function admin_handle_create_service_bike_serial(mysqli $mysqli, array &$errors): void
{
    $saleDate = trim((string) ($_POST['sale_date'] ?? ''));
    $serviceDate = trim((string) ($_POST['service_date'] ?? ''));
    $serialNumber = trim((string) ($_POST['serial_number'] ?? ''));
    $bikeDescription = trim((string) ($_POST['bike_description'] ?? ''));
    $normalizedSerial = service_booking_normalize_serial($serialNumber);

    if ($saleDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $saleDate) !== 1) {
        $errors[] = 'Datum prodeje nemá platný formát.';
        return;
    }

    if ($serviceDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $serviceDate) !== 1) {
        $errors[] = 'Datum servisu nemá platný formát.';
        return;
    }

    if ($saleDate !== '' && $serviceDate !== '' && strtotime($serviceDate) < strtotime($saleDate)) {
        $errors[] = 'Datum servisu nesmí být dříve než datum prodeje.';
        return;
    }

    if ($normalizedSerial === '') {
        $errors[] = 'Výrobní číslo kola je povinné.';
        return;
    }

    if (!create_service_bike_serial($mysqli, $saleDate, $serviceDate, $normalizedSerial, $bikeDescription)) {
        $errors[] = 'Výrobní číslo se nepodařilo uložit.';
        return;
    }

    write_audit_log($mysqli, 'create', 'service_bike_serial', 0, [
        'sale_date' => $saleDate,
        'service_date' => $serviceDate,
        'serial_number' => $normalizedSerial,
        'bike_description' => $bikeDescription,
    ]);

    admin_redirect_with_status('service_serial_created', service_bike_serials_redirect_target());
}

function admin_handle_delete_service_bike_serial(mysqli $mysqli, array &$errors): void
{
    $id = (int) ($_POST['serial_id'] ?? 0);

    if (!delete_service_bike_serial($mysqli, $id)) {
        $errors[] = 'Výrobní číslo se nepodařilo smazat.';
        return;
    }

    write_audit_log($mysqli, 'delete', 'service_bike_serial', $id);
    admin_redirect_with_status('service_serial_deleted', service_bike_serials_redirect_target());
}

function admin_handle_save_service_sheet(mysqli $mysqli, array &$errors): void
{
    $sheetId = (int) ($_POST['sheet_id'] ?? 0);
    $serviceSheet = fetch_service_sheet_by_id($mysqli, $sheetId);
    if ($serviceSheet === null) {
        $errors[] = 'Servisní list nebyl nalezen.';
        return;
    }

    $requestText = trim((string) ($_POST['request_text'] ?? ''));
    $materialItems = normalize_service_sheet_material_items(
        (array) ($_POST['material_name'] ?? []),
        (array) ($_POST['material_quantity'] ?? []),
        (array) ($_POST['material_price'] ?? [])
    );
    $laborItems = normalize_service_sheet_labor_items(
        (array) ($_POST['labor_name'] ?? []),
        (array) ($_POST['labor_price'] ?? [])
    );

    validate_service_sheet_input(
        (string) ($serviceSheet['repair_date'] ?? ''),
        (string) ($serviceSheet['serial_number'] ?? ''),
        $materialItems,
        $laborItems,
        $errors
    );

    if ($errors !== []) {
        return;
    }

    if (!save_service_sheet($mysqli, $sheetId, $requestText, $materialItems, $laborItems)) {
        $errors[] = 'Servisní list se nepodařilo uložit.';
        return;
    }

    write_audit_log($mysqli, 'update', 'service_sheet', $sheetId, [
        'serial_id' => (int) ($serviceSheet['serial_id'] ?? 0),
        'material_count' => count($materialItems),
        'labor_count' => count($laborItems),
    ]);

    admin_redirect_with_status('service_sheet_saved', service_sheet_redirect_target($sheetId));
}

function admin_handle_send_service_sheet_email(mysqli $mysqli, array &$errors): void
{
    $sheetId = (int) ($_POST['sheet_id'] ?? 0);
    $toEmail = trim((string) ($_POST['email_recipient'] ?? ''));

    $serviceSheet = fetch_service_sheet_by_id($mysqli, $sheetId);
    if ($serviceSheet === null) {
        $errors[] = 'Servisní list nebyl nalezen.';
        return;
    }

    if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Zadej platný e-mail pro odeslání PDF.';
        return;
    }

    $materialItems = fetch_service_sheet_material_items($mysqli, $sheetId);
    $laborItems = fetch_service_sheet_labor_items($mysqli, $sheetId);

    if (!send_service_sheet_email($serviceSheet, $materialItems, $laborItems, $toEmail)) {
        $errors[] = 'PDF servisního listu se nepodařilo odeslat e-mailem.';
        return;
    }

    write_audit_log($mysqli, 'email', 'service_sheet', $sheetId, [
        'recipient' => $toEmail,
    ]);

    admin_redirect_with_status('service_sheet_emailed', service_sheet_redirect_target($sheetId));
}

function admin_handle_update_service_reservation_status(mysqli $mysqli, array &$errors): void
{
    $id = (int) ($_POST['reservation_id'] ?? 0);
    $status = trim((string) ($_POST['reservation_status'] ?? ''));

    if (!update_service_reservation_status($mysqli, $id, $status)) {
        $errors[] = 'Stav rezervace se nepodarilo zmenit.';
        return;
    }

    write_audit_log($mysqli, 'update', 'service_reservation', $id, ['status' => $status]);
    admin_redirect_with_status('service_reservation_updated', 'service_admin.php');
}

function admin_handle_delete_service_reservation(mysqli $mysqli, array &$errors): void
{
    $id = (int) ($_POST['reservation_id'] ?? 0);

    if (!delete_service_reservation($mysqli, $id)) {
        $errors[] = 'Rezervaci se nepodarilo smazat.';
        return;
    }

    write_audit_log($mysqli, 'delete', 'service_reservation', $id);
    admin_redirect_with_status('service_reservation_deleted', 'service_admin.php');
}

function admin_handle_service_reservation_email_action(mysqli $mysqli, array &$errors, string $mode): void
{
    $templates = service_booking_email_templates();
    if (!isset($templates[$mode])) {
        $errors[] = 'Neplatna e-mailova akce rezervace.';
        return;
    }

    $id = (int) ($_POST['reservation_id'] ?? 0);
    $reservation = fetch_service_reservation_by_id($mysqli, $id);
    if ($reservation === null) {
        $errors[] = 'Rezervace nebyla nalezena.';
        return;
    }

    $email = trim((string) ($reservation['email'] ?? ''));
    if ($email === '') {
        $errors[] = 'Rezervace nema vyplneny e-mail, zpravu nelze odeslat.';
        return;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'E-mail u rezervace neni v platnem formatu.';
        return;
    }

    $status = $templates[$mode]['status'];
    if (!update_service_reservation_status($mysqli, $id, $status)) {
        $errors[] = 'Stav rezervace se nepodarilo zmenit.';
        return;
    }

    $reservation['status'] = $status;
    if (!send_service_reservation_email($reservation, $mode)) {
        $errors[] = 'E-mail se nepodarilo odeslat. Doplň heslo SMTP nebo zkontroluj nastaveni posty na hostingu.';
        return;
    }

    write_audit_log($mysqli, 'update', 'service_reservation', $id, [
        'status' => $status,
        'email_action' => $mode,
    ]);

    admin_redirect_with_status($mode === 'potvrdit' ? 'service_reservation_confirmed' : 'service_reservation_rejected', 'service_admin.php');
}
