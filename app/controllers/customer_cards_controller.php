<?php

function build_customer_cards_page_data(mysqli $mysqli, ?string &$dbError): array
{
    $data = [
        'errors' => [],
        'okMessage' => customer_card_ok_message((string) ($_GET['ok'] ?? '')),
        'form' => customer_card_default_form(),
        'cards' => [],
        'createdCard' => null,
    ];

    if ($dbError !== null) {
        return $data;
    }

    if (!ensure_customer_cards_table($mysqli)) {
        $dbError = 'Nepodařilo se připravit tabulku zákaznických karet.';
        return $data;
    }

    if (isset($_GET['created'])) {
        $data['createdCard'] = fetch_customer_card_by_id($mysqli, (int) $_GET['created']);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data['form'] = [
            'customer_name' => trim((string) ($_POST['customer_name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'bike_name' => trim((string) ($_POST['bike_name'] ?? '')),
            'serial_number' => trim((string) ($_POST['serial_number'] ?? '')),
            'note' => trim((string) ($_POST['note'] ?? '')),
        ];

        if ($data['form']['customer_name'] === '') {
            $data['errors'][] = 'Jméno zákazníka je povinné.';
        }

        if ($data['form']['email'] !== '' && !filter_var($data['form']['email'], FILTER_VALIDATE_EMAIL)) {
            $data['errors'][] = 'E-mail nemá platný formát.';
        }

        if ($data['errors'] === []) {
            $createdCard = create_customer_card($mysqli, $data['form']);
            if ($createdCard === null) {
                $data['errors'][] = 'Zákaznickou kartu se nepodařilo vytvořit.';
            } else {
                write_audit_log($mysqli, 'create', 'customer_card', (int) $createdCard['id'], [
                    'card_code' => (string) $createdCard['card_code'],
                    'customer_name' => (string) $createdCard['customer_name'],
                ]);
                header('Location: customer_cards.php?ok=created&created=' . (int) $createdCard['id']);
                exit;
            }
        }
    }

    $cards = fetch_customer_cards($mysqli);
    if ($cards === null) {
        $dbError = 'Nepodařilo se načíst zákaznické karty.';
    } else {
        $data['cards'] = $cards;
    }

    return $data;
}
