<?php
require __DIR__ . '/app/bootstrap.php';

require APP_ROOT . '/app/auth.php';
require_login('customer_cards.php');
require APP_ROOT . '/app/db.php';
require APP_ROOT . '/app/audit.php';
require APP_ROOT . '/app/view_helpers.php';
require APP_ROOT . '/app/customer_cards_helpers.php';
require APP_ROOT . '/app/controllers/customer_cards_controller.php';

$pageData = build_customer_cards_page_data($mysqli, $dbError);
$errors = $pageData['errors'];
$okMessage = $pageData['okMessage'];
$form = $pageData['form'];
$cards = $pageData['cards'];
$createdCard = $pageData['createdCard'];

$pageTitle = 'Zákaznické karty | ' . APP_NAME;
$activePage = 'customer_cards';
require APP_ROOT . '/app/layout/header.php';
require APP_ROOT . '/app/views/customer_cards/page.php';
require APP_ROOT . '/app/layout/footer.php';
