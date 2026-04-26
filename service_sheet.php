<?php
require __DIR__ . '/app/bootstrap.php';

require APP_ROOT . '/app/auth.php';
require_login('service_sheet.php');
require APP_ROOT . '/app/db.php';
require APP_ROOT . '/app/audit.php';
require APP_ROOT . '/app/admin_helpers.php';
require APP_ROOT . '/app/service_booking.php';
require APP_ROOT . '/app/admin_post.php';

$errors = [];
$okMessage = admin_ok_message((string) ($_GET['ok'] ?? ''));
$dummyForm = admin_default_bike_form();
$dummyNoticeBulkText = '';
$dummyNoticeBulkActive = 1;

if ($dbError === null && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_admin_post($mysqli, $dummyForm, $errors, $dummyNoticeBulkText, $dummyNoticeBulkActive);
}

$sheetId = isset($_GET['sheet_id']) ? (int) $_GET['sheet_id'] : (int) ($_POST['sheet_id'] ?? 0);
$serialId = isset($_GET['serial_id']) ? (int) $_GET['serial_id'] : 0;
$serviceSheet = null;
$serviceBikeSerial = null;

if ($dbError === null && $sheetId <= 0 && $serialId > 0) {
    $sheetId = create_service_sheet_for_serial_id($mysqli, $serialId) ?? 0;
    if ($sheetId > 0) {
        header('Location: service_sheet.php?sheet_id=' . $sheetId);
        exit;
    }
}

if ($dbError === null && $sheetId > 0) {
    $serviceSheet = fetch_service_sheet_by_id($mysqli, $sheetId);
    if ($serviceSheet !== null) {
        $serviceBikeSerial = fetch_service_bike_serial_by_id($mysqli, (int) ($serviceSheet['serial_id'] ?? 0));
    }
}

if ($dbError === null && $serviceSheet === null) {
    $errors[] = 'Servisní list nebyl nalezen.';
}

$materialItemsForm = [];
$laborItemsForm = [];
$serviceSheetRequest = '';
$emailRecipient = trim((string) ($_POST['email_recipient'] ?? ''));

if ($serviceSheet !== null) {
    $serviceSheetRequest = trim((string) ($serviceSheet['request_text'] ?? ''));
    $materialItemsForm = fetch_service_sheet_material_items($mysqli, (int) $serviceSheet['id']);
    $laborItemsForm = fetch_service_sheet_labor_items($mysqli, (int) $serviceSheet['id']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['action'] ?? '') === 'save_service_sheet') {
    $serviceSheetRequest = trim((string) ($_POST['request_text'] ?? ''));
    $materialItemsForm = normalize_service_sheet_material_items(
        (array) ($_POST['material_name'] ?? []),
        (array) ($_POST['material_quantity'] ?? []),
        (array) ($_POST['material_price'] ?? [])
    );
    $laborItemsForm = normalize_service_sheet_labor_items(
        (array) ($_POST['labor_name'] ?? []),
        (array) ($_POST['labor_price'] ?? [])
    );
}

if ($materialItemsForm === []) {
    $materialItemsForm[] = ['item_name' => '', 'quantity' => 1, 'price' => 0];
}

if ($laborItemsForm === []) {
    $laborItemsForm[] = ['operation_name' => '', 'price' => 0];
}

$materialTotalForm = 0.0;
foreach ($materialItemsForm as $item) {
    $materialTotalForm += ((float) ($item['quantity'] ?? 0)) * ((float) ($item['price'] ?? 0));
}

$laborTotalForm = 0.0;
foreach ($laborItemsForm as $item) {
    $laborTotalForm += (float) ($item['price'] ?? 0);
}

$totalPriceForm = $materialTotalForm + $laborTotalForm;
$serviceSheetLogoPath = 'uploads/hero/Flos-2.0_1.png';
$adminSection = 'service_work';
$pageTitle = 'Servisní list | ' . APP_NAME;
$activePage = 'service_work';
require APP_ROOT . '/app/layout/header.php';
?>

<main class="mx-auto max-w-[1680px] space-y-6 px-6 py-8">
    <?php require APP_ROOT . '/app/views/admin/section_nav.php'; ?>
    <?php require APP_ROOT . '/app/views/admin/service_sheet_form_section.php'; ?>
</main>

<?php require APP_ROOT . '/app/layout/footer.php'; ?>
