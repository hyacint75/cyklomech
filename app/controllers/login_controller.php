<?php

function ensure_login_users_table(mysqli $mysqli): bool
{
    try {
        if (!$mysqli->query(
            'CREATE TABLE IF NOT EXISTS users (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(80) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )'
        )) {
            return false;
        }

        $defaultAdminHash = '$2y$10$.ajE8Ahowkn0PFl6nL2iIeujCtlBE5/dJv1fgW3rpM3Hsc5LzHKAC';
        $stmt = $mysqli->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?) ON DUPLICATE KEY UPDATE username = username');
        if (!$stmt) {
            return false;
        }

        $defaultAdminUsername = 'admin';
        $stmt->bind_param('ss', $defaultAdminUsername, $defaultAdminHash);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    } catch (Throwable $exception) {
        return false;
    }
}

function build_login_page_data(mysqli $mysqli, ?string $dbError): array
{
    if (is_logged_in()) {
        header('Location: admin.php');
        exit;
    }

    $reason = (string) ($_GET['reason'] ?? '');
    $infoMessage = '';
    if ($reason === 'inactive') {
        $infoMessage = 'Byli jste odhlášeni po době nečinnosti.';
    }

    $data = [
        'next' => normalize_next_path((string) ($_GET['next'] ?? $_POST['next'] ?? 'admin.php')),
        'error' => '',
        'infoMessage' => $infoMessage,
        'username' => trim((string) ($_POST['username'] ?? '')),
        'dbError' => $dbError,
    ];

    if ($dbError !== null || $_SERVER['REQUEST_METHOD'] !== 'POST') {
        if ($dbError === null) {
            ensure_login_users_table($mysqli);
        }
        return $data;
    }

    if (!ensure_login_users_table($mysqli)) {
        $data['error'] = 'Nepodařilo se připravit uživatelskou tabulku pro přihlášení.';
        return $data;
    }

    $password = (string) ($_POST['password'] ?? '');
    if ($data['username'] === '' || $password === '') {
        $data['error'] = 'Vyplň uživatelské jméno i heslo.';
        return $data;
    }

    $stmt = $mysqli->prepare('SELECT id, username, password_hash FROM users WHERE username = ? LIMIT 1');
    if (!$stmt) {
        $data['error'] = 'Nepodařilo se ověřit přihlášení.';
        return $data;
    }

    $stmt->bind_param('s', $data['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if ($user && password_verify($password, (string) $user['password_hash'])) {
        login_user((int) $user['id'], (string) $user['username']);
        header('Location: ' . $data['next']);
        exit;
    }

    $data['error'] = 'Neplatné přihlašovací údaje.';
    return $data;
}
