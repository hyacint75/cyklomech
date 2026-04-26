<?php
require __DIR__ . '/app/bootstrap.php';

require APP_ROOT . '/app/db.php';
require APP_ROOT . '/app/controllers/gallery_controller.php';

$pageData = build_gallery_page_data($mysqli, $dbError);
$galleryBikes = $pageData['galleryBikes'];

$pageTitle = 'Fotogalerie | ' . APP_NAME;
$activePage = 'gallery';
require APP_ROOT . '/app/layout/header.php';
require APP_ROOT . '/app/views/gallery/page.php';
require APP_ROOT . '/app/layout/footer.php';
