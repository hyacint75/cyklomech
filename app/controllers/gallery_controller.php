<?php

function build_gallery_page_data(mysqli $mysqli, ?string &$dbError): array
{
    $data = [
        'galleryBikes' => [],
    ];

    if ($dbError !== null) {
        return $data;
    }

    $sql = "SELECT id, name, category, image_url FROM bikes WHERE in_stock > 0 AND image_url IS NOT NULL AND TRIM(image_url) <> '' ORDER BY id DESC";
    $result = $mysqli->query($sql);

    if (!$result) {
        $dbError = 'Nepodařilo se načíst fotogalerii kol.';
        return $data;
    }

    while ($row = $result->fetch_assoc()) {
        $data['galleryBikes'][] = $row;
    }

    $result->free();
    return $data;
}
