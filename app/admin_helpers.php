<?php

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function is_local_bike_image(string $path): bool
{
    return str_starts_with($path, 'uploads/bikes/');
}

function local_bike_image_absolute_path(string $path): string
{
    return dirname(__DIR__) . '/' . str_replace('/', DIRECTORY_SEPARATOR, $path);
}

function admin_default_bike_form(): array
{
    return [
        'id' => null,
        'name' => '',
        'category' => '',
        'bike_type' => 'uni',
        'manufacturer' => '',
        'price_czk' => '',
        'old_price_czk' => '',
        'is_new' => 0,
        'description' => '',
        'image_url' => '',
        'color' => '',
        'frame_size' => '',
        'wheel_size' => '',
        'weight_kg' => '',
        'tires' => '',
        'lighting' => '',
        'brakes' => '',
        'bottom_bracket' => '',
        'front_hub' => '',
        'rear_hub' => '',
        'front_rotor' => '',
        'rear_rotor' => '',
        'front_derailleur' => '',
        'rear_derailleur' => '',
        'wheels' => '',
        'battery' => '',
        'motor' => '',
        'display_name' => '',
        'saddle' => '',
        'cassette' => '',
        'frame_spec' => '',
        'fork_spec' => '',
        'chain_spec' => '',
        'note' => '',
        'in_stock' => 1,
    ];
}

function admin_ok_message(string $okKey): string
{
    $okMap = [
        'created' => 'Kolo bylo úspěšně přidáno.',
        'updated' => 'Kolo bylo úspěšně upraveno.',
        'deleted' => 'Kolo bylo úspěšně smazáno.',
        'duplicated' => 'Kolo bylo úspěšně zkopírováno.',
        'notice_added' => 'Důležité informace byly uloženy.',
        'notice_toggled' => 'Stav informace byl změněn.',
        'notice_deleted' => 'Informace byla smazána.',
    ];

    $okMap['imported'] = 'CSV seznam kol byl úspěšně importován.';

    $okMap['service_serial_created'] = 'Výrobní číslo kola bylo uloženo.';
    $okMap['service_serial_deleted'] = 'Výrobní číslo kola bylo smazáno.';
    $okMap['service_reservation_updated'] = 'Stav rezervace servisu byl uložen.';
    $okMap['service_reservation_deleted'] = 'Rezervace servisu byla smazána.';

    $okMap['service_reservation_confirmed'] = 'Rezervace byla potvrzena a e-mail byl odeslán.';
    $okMap['service_reservation_rejected'] = 'Rezervace byla odmítnuta a e-mail byl odeslán.';

    $okMap['service_sheet_saved'] = 'Servisní list byl uložen.';
    $okMap['service_sheet_emailed'] = 'PDF servisního listu bylo odesláno e-mailem.';

    return $okMap[$okKey] ?? '';
}

function admin_frame_size_presets(): array
{
    return ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
}

function admin_wheel_size_presets(): array
{
    return ['12"', '16"', '20"', '24"', '26"', '27,5"', '28"', '29"'];
}

function admin_bike_type_options(): array
{
    return ['pánské', 'dámské', 'dětské', 'uni'];
}

function admin_bike_csv_headers(): array
{
    return [
        'ID',
        'Název',
        'Kategorie',
        'Druh kola',
        'Výrobce',
        'Nová cena',
        'Původní cena',
        'Novinka',
        'Popis',
        'Barva',
        'Velikost rámu',
        'Velikost kol',
        'Hmotnost',
        'Pneu',
        'Osvětlení',
        'Brzdy',
        'Střed',
        'Střed kola přední',
        'Střed kola zadní',
        'Disk rotor přední',
        'Disk rotor zadní',
        'Řazení vpředu',
        'Řazení vzadu',
        'Kola',
        'Baterie',
        'Motor',
        'Display',
        'Sedlo',
        'Kazeta',
        'Rám',
        'Vidlice',
        'Řetěz',
        'Poznámka',
        'Skladem',
        'Obrázek',
    ];
}

function admin_csv_truthy_to_int(string $value): int
{
    $normalized = mb_strtolower(trim($value), 'UTF-8');
    return in_array($normalized, ['1', 'ano', 'yes', 'true'], true) ? 1 : 0;
}

function normalize_multi_option_value(string $value): string
{
    $parts = preg_split('/\s*[,;\/|]\s*/u', trim($value)) ?: [];
    $normalized = [];

    foreach ($parts as $part) {
        $option = trim($part);
        if ($option === '') {
            continue;
        }

        $normalized[] = $option;
    }

    return implode(', ', array_values(array_unique($normalized)));
}

function fetch_admin_notices(mysqli $mysqli): array
{
    $notices = [];
    $sql = "SELECT id, message, is_active, updated_by, updated_at FROM site_notice WHERE TRIM(message) <> '' ORDER BY id DESC LIMIT 100";

    if ($result = $mysqli->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $notices[] = $row;
        }
        $result->free();
    }

    return $notices;
}

function fetch_admin_bikes(mysqli $mysqli): array
{
    $bikes = [];
    $sql = 'SELECT id, name, category, bike_type, frame_size, note, price_czk, old_price_czk, is_new, image_url, in_stock FROM bikes ORDER BY id DESC';

    if ($result = $mysqli->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $bikes[] = $row;
        }
        $result->free();
    }

    return $bikes;
}

function fetch_admin_bikes_for_export(mysqli $mysqli): array
{
    $bikes = [];
    $sql = 'SELECT id, name, category, bike_type, manufacturer, price_czk, old_price_czk, is_new, description, color, frame_size, wheel_size, weight_kg, tires, lighting, brakes, bottom_bracket, front_hub, rear_hub, front_rotor, rear_rotor, front_derailleur, rear_derailleur, wheels, battery, motor, display_name, saddle, cassette, frame_spec, fork_spec, chain_spec, note, in_stock, image_url FROM bikes ORDER BY id DESC';

    if ($result = $mysqli->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $bikes[] = $row;
        }
        $result->free();
    }

    return $bikes;
}

function admin_export_bikes_csv(mysqli $mysqli): void
{
    $rows = fetch_admin_bikes_for_export($mysqli);

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="cykloflos-kola.csv"');

    $output = fopen('php://output', 'wb');
    if ($output === false) {
        exit;
    }

    fwrite($output, "\xEF\xBB\xBF");
    fputcsv($output, admin_bike_csv_headers(), ';');

    foreach ($rows as $row) {
        fputcsv($output, [
            $row['id'],
            $row['name'],
            $row['category'],
            (string) ($row['bike_type'] ?? 'uni'),
            $row['manufacturer'],
            $row['price_czk'],
            $row['old_price_czk'],
            ((int) ($row['is_new'] ?? 0) === 1) ? 'ano' : 'ne',
            $row['description'],
            $row['color'],
            $row['frame_size'],
            $row['wheel_size'],
            $row['weight_kg'],
            $row['tires'],
            $row['lighting'],
            $row['brakes'],
            $row['bottom_bracket'],
            $row['front_hub'],
            $row['rear_hub'],
            $row['front_rotor'],
            $row['rear_rotor'],
            $row['front_derailleur'],
            $row['rear_derailleur'],
            $row['wheels'],
            $row['battery'],
            $row['motor'],
            $row['display_name'],
            $row['saddle'],
            $row['cassette'],
            $row['frame_spec'],
            $row['fork_spec'],
            $row['chain_spec'],
            $row['note'],
            ((int) $row['in_stock'] === 1) ? 'ano' : 'ne',
            $row['image_url'],
        ], ';');
    }

    fclose($output);
    exit;
}

function admin_form_from_bike_row(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'name' => (string) $row['name'],
        'category' => (string) $row['category'],
        'bike_type' => (string) ($row['bike_type'] ?? 'uni'),
        'manufacturer' => (string) ($row['manufacturer'] ?? ''),
        'price_czk' => (string) $row['price_czk'],
        'old_price_czk' => (string) ($row['old_price_czk'] ?? ''),
        'is_new' => (int) ($row['is_new'] ?? 0),
        'description' => (string) $row['description'],
        'image_url' => (string) ($row['image_url'] ?? ''),
        'color' => (string) ($row['color'] ?? ''),
        'frame_size' => (string) ($row['frame_size'] ?? ''),
        'wheel_size' => (string) ($row['wheel_size'] ?? ''),
        'weight_kg' => (string) ($row['weight_kg'] ?? ''),
        'tires' => (string) ($row['tires'] ?? ''),
        'lighting' => (string) ($row['lighting'] ?? ''),
        'brakes' => (string) ($row['brakes'] ?? ''),
        'bottom_bracket' => (string) ($row['bottom_bracket'] ?? ''),
        'front_hub' => (string) ($row['front_hub'] ?? ''),
        'rear_hub' => (string) ($row['rear_hub'] ?? ''),
        'front_rotor' => (string) ($row['front_rotor'] ?? ''),
        'rear_rotor' => (string) ($row['rear_rotor'] ?? ''),
        'front_derailleur' => (string) ($row['front_derailleur'] ?? ''),
        'rear_derailleur' => (string) ($row['rear_derailleur'] ?? ''),
        'wheels' => (string) ($row['wheels'] ?? ''),
        'battery' => (string) ($row['battery'] ?? ''),
        'motor' => (string) ($row['motor'] ?? ''),
        'display_name' => (string) ($row['display_name'] ?? ''),
        'saddle' => (string) ($row['saddle'] ?? ''),
        'cassette' => (string) ($row['cassette'] ?? ''),
        'frame_spec' => (string) ($row['frame_spec'] ?? ''),
        'fork_spec' => (string) ($row['fork_spec'] ?? ''),
        'chain_spec' => (string) ($row['chain_spec'] ?? ''),
        'note' => (string) ($row['note'] ?? ''),
        'in_stock' => (int) $row['in_stock'],
    ];
}

function fetch_bike_for_edit(mysqli $mysqli, int $editId): ?array
{
    if ($editId <= 0) {
        return null;
    }

    $stmt = $mysqli->prepare('SELECT id, name, category, bike_type, manufacturer, price_czk, old_price_czk, is_new, description, image_url, color, frame_size, wheel_size, weight_kg, tires, lighting, brakes, bottom_bracket, front_hub, rear_hub, front_rotor, rear_rotor, front_derailleur, rear_derailleur, wheels, battery, motor, display_name, saddle, cassette, frame_spec, fork_spec, chain_spec, note, in_stock FROM bikes WHERE id = ? LIMIT 1');
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $row ?: null;
}
