<?php
require __DIR__ . '/app/bootstrap.php';

require APP_ROOT . '/app/auth.php';
require APP_ROOT . '/app/db.php';
require APP_ROOT . '/app/view_helpers.php';
require APP_ROOT . '/app/controllers/login_controller.php';

$pageData = build_login_page_data($mysqli, $dbError);
$next = $pageData['next'];
$error = $pageData['error'];
$infoMessage = $pageData['infoMessage'];
$username = $pageData['username'];

$pageTitle = 'Přihlášení | ' . APP_NAME;
$activePage = '';
require APP_ROOT . '/app/layout/header.php';
require APP_ROOT . '/app/views/auth/login_form.php';
require APP_ROOT . '/app/layout/footer.php';
