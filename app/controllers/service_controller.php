<?php

function service_redirect_with_status(string $status): void
{
    header('Location: servis.php?ok=' . rawurlencode($status));
    exit;
}

function build_service_page_data(mysqli $mysqli, ?string &$dbError): array
{
    $form = service_booking_default_form();
    $accessErrors = [];
    $reservationErrors = [];
    $okMessage = service_booking_ok_message((string) ($_GET['ok'] ?? ''));

    if ($dbError !== null) {
        return [
            'pageTitle' => 'Rezervace servisu | ' . APP_NAME,
            'activePage' => 'service',
            'isUnlocked' => false,
            'accessErrors' => $accessErrors,
            'reservationErrors' => $reservationErrors,
            'okMessage' => $okMessage,
            'form' => $form,
            'serviceTypes' => service_booking_type_options(),
        ];
    }

    ensure_service_booking_access_row($mysqli);
    ensure_service_bike_serials_table($mysqli);

    if ($dbError === null && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'service_access_login') {
            $accessKey = (string) ($_POST['access_key'] ?? $_POST['serial_number'] ?? '');
            if (!service_booking_access_key_exists($mysqli, $accessKey)) {
                $accessErrors[] = 'Zadané výrobní číslo, kód zákaznické karty nebo jméno nebylo nalezeno.';
            } else {
                service_booking_grant_access();
                service_redirect_with_status('access_granted');
            }
        }

        if ($action === 'service_access_logout') {
            service_booking_revoke_access();
            header('Location: servis.php');
            exit;
        }

        if ($action === 'create_service_reservation' && service_booking_is_unlocked()) {
            $form = [
                'customer_name' => trim((string) ($_POST['customer_name'] ?? '')),
                'phone' => trim((string) ($_POST['phone'] ?? '')),
                'email' => trim((string) ($_POST['email'] ?? '')),
                'preferred_date' => trim((string) ($_POST['preferred_date'] ?? '')),
                'preferred_time' => trim((string) ($_POST['preferred_time'] ?? '')),
                'service_type' => trim((string) ($_POST['service_type'] ?? '')),
                'bike_info' => trim((string) ($_POST['bike_info'] ?? '')),
                'note' => trim((string) ($_POST['note'] ?? '')),
            ];

            validate_service_booking_form($form, $reservationErrors);
            if ($reservationErrors === []) {
                $reservationId = insert_service_reservation($mysqli, $form);
                if ($reservationId === null) {
                    $reservationErrors[] = 'Rezervaci se nepodařilo uložit.';
                } else {
                    service_redirect_with_status('reservation_created');
                }
            }
        }
    }

    return [
        'pageTitle' => 'Rezervace servisu | ' . APP_NAME,
        'activePage' => 'service',
        'isUnlocked' => service_booking_is_unlocked(),
        'accessErrors' => $accessErrors,
        'reservationErrors' => $reservationErrors,
        'okMessage' => $okMessage,
        'form' => $form,
        'serviceTypes' => service_booking_type_options(),
    ];
}
