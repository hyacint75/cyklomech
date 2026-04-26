<?php
require __DIR__ . '/app/bootstrap.php';

require APP_ROOT . '/app/auth.php';
require_login('auditlog.php');
require APP_ROOT . '/app/db.php';
require APP_ROOT . '/app/view_helpers.php';
require APP_ROOT . '/app/auditlog_helpers.php';
require APP_ROOT . '/app/controllers/auditlog_controller.php';

$pageData = build_auditlog_page_data($mysqli, $dbError);
$logs = $pageData['logs'];
$errors = $pageData['errors'];
$okMessage = $pageData['okMessage'];

$pageTitle = 'Audit log | ' . APP_NAME;
$activePage = 'audit';
require APP_ROOT . '/app/layout/header.php';
require APP_ROOT . '/app/views/audit/table.php';
require APP_ROOT . '/app/layout/footer.php';
