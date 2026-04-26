<?php
require __DIR__ . '/app/bootstrap.php';

require APP_ROOT . '/app/db.php';
require APP_ROOT . '/app/view_helpers.php';
require APP_ROOT . '/app/service_booking.php';
require APP_ROOT . '/app/controllers/service_controller.php';

$pageData = build_service_page_data($mysqli, $dbError);
$pageTitle = $pageData['pageTitle'];
$activePage = $pageData['activePage'];
$isUnlocked = $pageData['isUnlocked'];
$accessErrors = $pageData['accessErrors'];
$reservationErrors = $pageData['reservationErrors'];
$okMessage = $pageData['okMessage'];
$form = $pageData['form'];
$serviceTypes = $pageData['serviceTypes'];

require APP_ROOT . '/app/layout/header.php';
require APP_ROOT . '/app/views/service/page.php';
require APP_ROOT . '/app/layout/footer.php';
