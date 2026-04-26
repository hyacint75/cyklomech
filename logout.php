<?php
require __DIR__ . '/app/bootstrap.php';
require APP_ROOT . '/app/auth.php';

$reason = trim((string) ($_REQUEST['reason'] ?? 'manual'));
if (!in_array($reason, ['manual', 'inactive', 'leave'], true)) {
    $reason = 'manual';
}

$backgroundLogout = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' || ((string) ($_GET['background'] ?? '') === '1');

logout_user($reason);

if ($backgroundLogout) {
    http_response_code(204);
    exit;
}

$target = 'login.php';
if ($reason === 'inactive') {
    $target .= '?reason=inactive';
}

header('Location: ' . $target);
exit;
