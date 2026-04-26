<?php

function users_default_form(): array
{
    return [
        'username' => '',
    ];
}

function users_ok_message(string $okKey): string
{
    return $okKey === 'created' ? 'Uživatel byl úspěšně vytvořen.' : '';
}

function fetch_users(mysqli $mysqli): ?array
{
    $users = [];
    $sql = 'SELECT id, username, created_at FROM users ORDER BY id DESC';
    if ($resultUsers = $mysqli->query($sql)) {
        while ($rowUser = $resultUsers->fetch_assoc()) {
            $users[] = $rowUser;
        }
        $resultUsers->free();

        return $users;
    }

    return null;
}
