<?php
require __DIR__ . '/app/bootstrap.php';

require APP_ROOT . '/app/db.php';
require APP_ROOT . '/app/homepage.php';
require APP_ROOT . '/app/controllers/home_controller.php';

$pageData = build_home_page_data($mysqli, $dbError);
$filters = $pageData['filters'];
$searchQuery = $filters['q'];
$selectedCategory = $filters['category'];
$selectedBikeType = $filters['bike_type'];
$selectedManufacturer = $filters['manufacturer'];
$selectedFrameSize = $filters['frame_size'];
$selectedSort = $filters['sort'];
$selectedStock = '';
$priceMin = $filters['price_min'];
$priceMax = $filters['price_max'];
$selectedWheelSize = $filters['wheel_size'];
$currentPage = $pageData['pagination']['page'];
$totalPages = $pageData['pagination']['totalPages'];
$totalItems = $pageData['pagination']['totalItems'];
$bikes = $pageData['bikes'];
$categories = $pageData['categories'];
$bikeTypes = $pageData['bikeTypes'];
$manufacturers = $pageData['manufacturers'];
$frameSizes = $pageData['frameSizes'];
$wheelSizes = $pageData['wheelSizes'];
$importantNotices = $pageData['importantNotices'];
$importantNoticesKey = $pageData['importantNoticesKey'];
$forceShowNotice = $pageData['forceShowNotice'];

$pageTitle = APP_NAME . ' | Cyklo prodej';
$activePage = 'home';
require APP_ROOT . '/app/layout/header.php';
require APP_ROOT . '/app/views/home/page.php';
require APP_ROOT . '/app/layout/footer.php';
