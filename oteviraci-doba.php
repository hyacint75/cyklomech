<?php
require __DIR__ . '/app/bootstrap.php';

require APP_ROOT . '/app/controllers/hours_controller.php';

$pageData = build_hours_page_data();
$pageTitle = $pageData['pageTitle'];
$activePage = $pageData['activePage'];

require APP_ROOT . '/app/layout/header.php';
require APP_ROOT . '/app/views/hours/page.php';
require APP_ROOT . '/app/layout/footer.php';
