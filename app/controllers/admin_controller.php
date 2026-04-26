<?php

function build_admin_page_data(mysqli $mysqli, ?string $dbError): array
{
    $data = [
        'form' => admin_default_bike_form(),
        'errors' => [],
        'okMessage' => admin_ok_message((string) ($_GET['ok'] ?? '')),
        'noticeBulkText' => '',
        'noticeBulkActive' => 1,
        'notices' => [],
        'adminBikes' => [],
        'serviceReservations' => [],
        'serviceBikeSerials' => [],
        'serviceSheets' => [],
        'isEditMode' => false,
    ];

    if ($dbError === null && $_SERVER['REQUEST_METHOD'] === 'POST') {
        handle_admin_post($mysqli, $data['form'], $data['errors'], $data['noticeBulkText'], $data['noticeBulkActive']);
    }

    if ($dbError === null) {
        $data['notices'] = fetch_admin_notices($mysqli);
    }

    $editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
    if ($dbError === null && $editId > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        $bikeForEdit = fetch_bike_for_edit($mysqli, $editId);
        if ($bikeForEdit !== null) {
            $data['form'] = admin_form_from_bike_row($bikeForEdit);
        }
    }

    if ($dbError === null) {
        $data['adminBikes'] = fetch_admin_bikes($mysqli);
        $data['serviceReservations'] = fetch_service_reservations($mysqli);
        $data['serviceBikeSerials'] = fetch_service_bike_serials($mysqli);
        $data['serviceSheets'] = fetch_service_sheets($mysqli);
    }

    $data['isEditMode'] = $data['form']['id'] !== null;
    return $data;
}
