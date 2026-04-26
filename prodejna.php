<?php
require __DIR__ . '/app/bootstrap.php';

require APP_ROOT . '/app/db.php';
require APP_ROOT . '/app/homepage.php';
require APP_ROOT . '/app/controllers/home_controller.php';

$showroomFilters = [
    'q' => '',
    'category' => '',
    'bike_type' => '',
    'manufacturer' => '',
    'frame_size' => '',
    'sort' => 'newest',
    'price_min' => '',
    'price_max' => '',
    'wheel_size' => '',
    'page' => 1,
];

$bikes = [];
$manufacturers = [];
$categories = [];

if ($dbError === null) {
    $showroomTotalItems = count_filtered_bikes($mysqli, $showroomFilters);

    if ($showroomTotalItems === null) {
        $dbError = 'Nepodařilo se načíst seznam kol pro katalog prodejny.';
    } else {
        $bikes = fetch_filtered_bikes($mysqli, $showroomFilters, max(1, $showroomTotalItems));
        if ($bikes === null) {
            $dbError = 'Nepodařilo se načíst seznam kol pro katalog prodejny.';
            $bikes = [];
        }
    }

    $manufacturers = fetch_distinct_values($mysqli, 'manufacturer');
    $categories = fetch_distinct_values($mysqli, 'category');
}

$pageTitle = APP_NAME . ' | Katalog prodejny';
$activePage = 'showroom';

require APP_ROOT . '/app/layout/header.php';
require APP_ROOT . '/app/views/showroom/page.php';
require APP_ROOT . '/app/layout/footer.php';
