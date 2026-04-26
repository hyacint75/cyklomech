<?php

function build_users_page_data(mysqli $mysqli, ?string &$dbError): array
{
    $data = [
        'errors' => [],
        'okMessage' => users_ok_message((string) ($_GET['ok'] ?? '')),
        'form' => users_default_form(),
        'users' => [],
    ];

    if ($dbError === null && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data['form']['username'] = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($data['form']['username'] === '') {
            $data['errors'][] = 'Uživatelské jméno je povinné.';
        }
        if ($password === '') {
            $data['errors'][] = 'Heslo je povinné.';
        }
        if (strlen($password) < 4) {
            $data['errors'][] = 'Heslo musí mít alespoň 4 znaky.';
        }

        if ($data['errors'] === []) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
            if ($stmt) {
                $stmt->bind_param('ss', $data['form']['username'], $passwordHash);
                if ($stmt->execute()) {
                    $newUserId = (int) $mysqli->insert_id;
                    $stmt->close();
                    write_audit_log($mysqli, 'create', 'user', $newUserId, [
                        'username' => $data['form']['username'],
                    ]);
                    header('Location: users.php?ok=created');
                    exit;
                }
                $stmt->close();
                $data['errors'][] = 'Uživatelské jméno už existuje nebo se nepodařilo uložit.';
            } else {
                $data['errors'][] = 'Nepodařilo se připravit vložení uživatele.';
            }
        }
    }

    if ($dbError === null) {
        $loadedUsers = fetch_users($mysqli);
        if ($loadedUsers !== null) {
            $data['users'] = $loadedUsers;
        } else {
            $dbError = 'Tabulka users nebyla nalezena. Spusť migraci uživatelů.';
        }
    }

    return $data;
}
