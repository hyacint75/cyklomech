<?php
require __DIR__ . '/app/bootstrap.php';

require APP_ROOT . '/app/auth.php';
require_login('service_work.php');
require APP_ROOT . '/app/db.php';
require APP_ROOT . '/app/audit.php';
require APP_ROOT . '/app/admin_helpers.php';
require APP_ROOT . '/app/service_booking.php';
require APP_ROOT . '/app/admin_post.php';
require APP_ROOT . '/app/controllers/admin_controller.php';

$pageData = build_admin_page_data($mysqli, $dbError);
$errors = $pageData['errors'];
$okMessage = $pageData['okMessage'];
$serviceBikeSerials = $pageData['serviceBikeSerials'];
$serviceSheets = $pageData['serviceSheets'];
$adminSection = 'service_work';

$pageTitle = 'Servisní práce | ' . APP_NAME;
$activePage = 'service_work';
require APP_ROOT . '/app/layout/header.php';
?>

<main class="mx-auto max-w-[1680px] px-6 py-8 space-y-6">
    <?php require APP_ROOT . '/app/views/admin/section_nav.php'; ?>
    <?php require APP_ROOT . '/app/views/admin/service_bike_serials_section.php'; ?>
</main>

<?php require APP_ROOT . '/app/layout/footer.php'; ?>
