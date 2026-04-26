<?php

function build_home_page_data(mysqli $mysqli, ?string &$dbError): array
{
    $filters = homepage_filters_from_request();
    $perPage = 20;
    $data = [
        'filters' => $filters,
        'bikes' => [],
        'pagination' => [
            'page' => 1,
            'perPage' => $perPage,
            'totalItems' => 0,
            'totalPages' => 1,
        ],
        'categories' => [],
        'bikeTypes' => [],
        'manufacturers' => [],
        'frameSizes' => [],
        'wheelSizes' => [],
        'importantNotices' => [],
        'importantNoticesKey' => '',
        'forceShowNotice' => isset($_GET['show_notice']) && (string) $_GET['show_notice'] === '1',
    ];

    if ($dbError !== null) {
        return $data;
    }

    $data['bikes'] = fetch_bikes($mysqli, $perPage);
    $data['categories'] = fetch_distinct_values($mysqli, 'category');
    $data['bikeTypes'] = fetch_distinct_values($mysqli, 'bike_type');
    $data['manufacturers'] = fetch_distinct_values($mysqli, 'manufacturer');
    $data['frameSizes'] = fetch_distinct_values($mysqli, 'frame_size');
    $data['wheelSizes'] = fetch_distinct_values($mysqli, 'wheel_size');
    $data['importantNotices'] = fetch_important_notices($mysqli);
    $data['importantNoticesKey'] = important_notices_key($data['importantNotices']);

    $totalItems = count_filtered_bikes($mysqli, $filters);
    if ($totalItems === null) {
        $dbError = 'Nepodařilo se načíst počet kol pro stránkování.';

        return $data;
    }

    $totalPages = max(1, (int) ceil($totalItems / $perPage));
    $currentPage = min(max(1, (int) ($filters['page'] ?? 1)), $totalPages);
    $filters['page'] = $currentPage;
    $data['filters'] = $filters;
    $data['pagination'] = [
        'page' => $currentPage,
        'perPage' => $perPage,
        'totalItems' => $totalItems,
        'totalPages' => $totalPages,
    ];

    $filteredBikes = fetch_filtered_bikes($mysqli, $filters, $perPage);
    if ($filteredBikes !== null) {
        $data['bikes'] = $filteredBikes;
    } else {
        $dbError = 'Nepodařilo se načíst filtrovaný seznam kol.';
    }

    return $data;
}
