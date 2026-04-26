<?php
require __DIR__ . '/app/bootstrap.php';

require APP_ROOT . '/app/auth.php';
require_login('admin.php');
require APP_ROOT . '/app/db.php';
require APP_ROOT . '/app/audit.php';
require APP_ROOT . '/app/admin_helpers.php';
require APP_ROOT . '/app/service_booking.php';
require APP_ROOT . '/app/admin_post.php';
require APP_ROOT . '/app/controllers/admin_controller.php';

if ($dbError === null && isset($_GET['export']) && (string) $_GET['export'] === 'csv') {
    admin_export_bikes_csv($mysqli);
}

$pageData = build_admin_page_data($mysqli, $dbError);
$form = $pageData['form'];
$errors = $pageData['errors'];
$okMessage = $pageData['okMessage'];
$noticeBulkText = $pageData['noticeBulkText'];
$noticeBulkActive = $pageData['noticeBulkActive'];
$notices = $pageData['notices'];
$adminBikes = $pageData['adminBikes'];
$isEditMode = $pageData['isEditMode'];
$isCreateMode = isset($_GET['new']) && (string) $_GET['new'] === '1';
$showBikeForm = $isCreateMode || $isEditMode;
$adminSection = 'bikes';

$pageTitle = 'Administrace | ' . APP_NAME;
$activePage = 'admin';
require APP_ROOT . '/app/layout/header.php';
?>

<main class="mx-auto max-w-[1680px] px-6 py-8 space-y-6">
    <?php require APP_ROOT . '/app/views/admin/section_nav.php'; ?>
    <?php if ($showBikeForm): ?>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold"><?php echo $isEditMode ? 'Úprava kola' : 'Nové kolo'; ?></h1>
                <p class="mt-1 text-sm text-slate-600"><?php echo $isEditMode ? 'Uprav detaily vybraného kola.' : 'Vyplň formulář pro přidání nového kola.'; ?></p>
            </div>
            <a href="admin.php" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-50">Zpět na seznam kol</a>
        </div>
        <?php require APP_ROOT . '/app/views/admin/bike_form_section.php'; ?>
    <?php else: ?>
        <?php require APP_ROOT . '/app/views/admin/notices_section.php'; ?>
        <div class="flex justify-end">
            <a href="admin.php?new=1" class="rounded-lg bg-forest px-4 py-2 text-sm font-semibold text-white hover:bg-amber-800">Přidat nové kolo</a>
        </div>
        <?php require APP_ROOT . '/app/views/admin/bikes_table_section.php'; ?>
    <?php endif; ?>
</main>

<?php require APP_ROOT . '/app/layout/footer.php'; ?>



