<?php
$host = getenv('DB_HOST') ?: 'db.dw184.webglobe.com';
$port = (int) (getenv('DB_PORT') ?: 3306);
$user = getenv('DB_USER') ?: 'pida';
$pass = getenv('DB_PASS') ?: '!0kWBcBc';
$dbName = getenv('DB_NAME') ?: 'pida';

$mysqli = @new mysqli($host, $user, $pass, $dbName, $port);

if ($mysqli->connect_errno) {
    $dbError = sprintf(
        'Nepodařilo se připojit k databázi %s (%s).',
        $dbName,
        $mysqli->connect_error
    );
    $bikes = [];
    return;
}

$mysqli->set_charset('utf8mb4');
$dbError = null;

function fetch_bikes(mysqli $mysqli, int $limit = 8): array
{
    $bikes = [];
    $limit = max(1, $limit);
    $sql = sprintf(
        'SELECT id, name, category, bike_type, price_czk, old_price_czk, is_new, description, image_url, in_stock FROM bikes WHERE in_stock > 0 ORDER BY id DESC LIMIT %d',
        $limit
    );

    if ($result = $mysqli->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $bikes[] = $row;
        }
        $result->free();
    }

    return $bikes;
}

$bikes = [];
