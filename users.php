<?php
require __DIR__ . '/app/bootstrap.php';

require APP_ROOT . '/app/auth.php';
require_login('users.php');
require APP_ROOT . '/app/db.php';
require APP_ROOT . '/app/audit.php';
require APP_ROOT . '/app/view_helpers.php';
require APP_ROOT . '/app/users_helpers.php';
require APP_ROOT . '/app/controllers/users_controller.php';

$pageData = build_users_page_data($mysqli, $dbError);
$errors = $pageData['errors'];
$okMessage = $pageData['okMessage'];
$form = $pageData['form'];
$users = $pageData['users'];

$pageTitle = 'Uživatelé | ' . APP_NAME;
$activePage = 'users';
require APP_ROOT . '/app/layout/header.php';
?>

<main class="mx-auto max-w-[1680px] px-6 py-8 grid gap-6 lg:grid-cols-5">
    <?php require APP_ROOT . '/app/views/users/form_section.php'; ?>
    <?php require APP_ROOT . '/app/views/users/table_section.php'; ?>
</main>

<?php require APP_ROOT . '/app/layout/footer.php'; ?>
