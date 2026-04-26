<?php

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function split_multi_value_options(string $value): array
{
    $value = trim($value);
    if ($value === '') {
        return [];
    }

    $parts = preg_split('/\s*[,;\/|]\s*/u', $value) ?: [];
    $options = [];

    foreach ($parts as $part) {
        $option = trim($part);
        if ($option === '') {
            continue;
        }

        $options[] = $option;
    }

    return array_values(array_unique($options));
}

function homepage_filters_from_request(): array
{
    $page = (int) ($_GET['page'] ?? 1);

    return [
        'q' => trim((string) ($_GET['q'] ?? '')),
        'category' => trim((string) ($_GET['category'] ?? '')),
        'bike_type' => trim((string) ($_GET['bike_type'] ?? '')),
        'manufacturer' => trim((string) ($_GET['manufacturer'] ?? '')),
        'frame_size' => trim((string) ($_GET['frame_size'] ?? '')),
        'sort' => trim((string) ($_GET['sort'] ?? 'newest')),
        'price_min' => trim((string) ($_GET['price_min'] ?? '')),
        'price_max' => trim((string) ($_GET['price_max'] ?? '')),
        'wheel_size' => trim((string) ($_GET['wheel_size'] ?? '')),
        'page' => max(1, $page),
    ];
}

function fetch_distinct_values(mysqli $mysqli, string $column): array
{
    $allowedColumns = ['category', 'bike_type', 'manufacturer', 'frame_size', 'wheel_size'];
    if (!in_array($column, $allowedColumns, true)) {
        return [];
    }

    $values = [];
    $sql = sprintf(
        "SELECT DISTINCT %s FROM bikes WHERE in_stock > 0 AND %s IS NOT NULL AND %s <> '' ORDER BY %s",
        $column,
        $column,
        $column,
        $column
    );

    if ($result = $mysqli->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $rawValue = (string) $row[$column];
            if ($column === 'frame_size') {
                $values = array_merge($values, split_multi_value_options($rawValue));
                continue;
            }

            $values[] = $rawValue;
        }
        $result->free();
    }

    $values = array_values(array_unique($values));
    natcasesort($values);

    return array_values($values);
}

function fetch_important_notices(mysqli $mysqli): array
{
    $notices = [];
    $sql = "SELECT message FROM site_notice WHERE is_active = 1 AND TRIM(message) <> '' ORDER BY id DESC LIMIT 10";

    if ($result = $mysqli->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $notices[] = (string) $row['message'];
        }
        $result->free();
    }

    return $notices;
}

function important_notices_key(array $importantNotices): string
{
    if ($importantNotices === []) {
        return '';
    }

    return sha1(json_encode($importantNotices, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');
}

function build_bikes_where_clause(mysqli $mysqli, array $filters): string
{
    $where = [];

    if ($filters['q'] !== '') {
        $q = $mysqli->real_escape_string($filters['q']);
        $where[] = "(name LIKE '%{$q}%' OR description LIKE '%{$q}%')";
    }
    if ($filters['category'] !== '') {
        $category = $mysqli->real_escape_string($filters['category']);
        $where[] = "category = '{$category}'";
    }
    if ($filters['bike_type'] !== '') {
        $bikeType = $mysqli->real_escape_string($filters['bike_type']);
        $where[] = "bike_type = '{$bikeType}'";
    }
    if ($filters['manufacturer'] !== '') {
        $manufacturer = $mysqli->real_escape_string($filters['manufacturer']);
        $where[] = "manufacturer = '{$manufacturer}'";
    }
    if ($filters['frame_size'] !== '') {
        $frameSizePattern = preg_quote($filters['frame_size'], '/');
        $frameSizePattern = $mysqli->real_escape_string("(^|[[:space:],;/|]){$frameSizePattern}([[:space:],;/|]|$)");
        $where[] = "frame_size REGEXP '{$frameSizePattern}'";
    }
    $where[] = 'in_stock > 0';

    if ($filters['price_min'] !== '' && is_numeric($filters['price_min'])) {
        $where[] = 'price_czk >= ' . (float) $filters['price_min'];
    }
    if ($filters['price_max'] !== '' && is_numeric($filters['price_max'])) {
        $where[] = 'price_czk <= ' . (float) $filters['price_max'];
    }
    if ($filters['wheel_size'] !== '') {
        $wheelSize = $mysqli->real_escape_string($filters['wheel_size']);
        $where[] = "wheel_size = '{$wheelSize}'";
    }

    return $where !== [] ? ' WHERE ' . implode(' AND ', $where) : '';
}

function build_bikes_order_by_clause(array $filters): string
{
    return match ($filters['sort'] ?? 'newest') {
        'price_asc' => 'price_czk ASC, id DESC',
        'price_desc' => 'price_czk DESC, id DESC',
        'name_asc' => 'name ASC, id DESC',
        default => 'id DESC',
    };
}

function count_filtered_bikes(mysqli $mysqli, array $filters): ?int
{
    $sql = 'SELECT COUNT(*) AS total FROM bikes' . build_bikes_where_clause($mysqli, $filters);

    if ($result = $mysqli->query($sql)) {
        $row = $result->fetch_assoc();
        $result->free();

        return isset($row['total']) ? (int) $row['total'] : 0;
    }

    return null;
}

function build_bikes_query(mysqli $mysqli, array $filters, int $perPage = 18): string
{
    $page = max(1, (int) ($filters['page'] ?? 1));
    $perPage = max(1, $perPage);
    $offset = ($page - 1) * $perPage;

    $sql = 'SELECT id, name, category, bike_type, manufacturer, price_czk, old_price_czk, is_new, description, image_url, frame_size, wheel_size, in_stock FROM bikes';
    $sql .= build_bikes_where_clause($mysqli, $filters);

    return sprintf(
        '%s ORDER BY %s LIMIT %d OFFSET %d',
        $sql,
        build_bikes_order_by_clause($filters),
        $perPage,
        $offset
    );
}

function fetch_filtered_bikes(mysqli $mysqli, array $filters, int $perPage = 18): ?array
{
    $bikes = [];
    $sql = build_bikes_query($mysqli, $filters, $perPage);

    if ($result = $mysqli->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $bikes[] = $row;
        }
        $result->free();

        return $bikes;
    }

    return null;
}
