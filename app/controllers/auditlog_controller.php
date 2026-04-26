<?php

function build_auditlog_page_data(mysqli $mysqli, ?string &$dbError): array
{
    $data = [
        'logs' => [],
        'errors' => [],
        'okMessage' => '',
    ];

    if ($dbError === null && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'delete_audit_log') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                $data['errors'][] = 'Neplatné ID audit logu.';
            } elseif (!delete_audit_log_entry($mysqli, $id)) {
                $data['errors'][] = 'Audit log se nepodařilo smazat.';
            } else {
                header('Location: auditlog.php?ok=deleted');
                exit;
            }
        }

        if ($action === 'delete_selected_audit_logs') {
            $selectedIds = $_POST['selected_logs'] ?? [];
            if (!is_array($selectedIds) || $selectedIds === []) {
                $data['errors'][] = 'Nevybral jsi žádné audit logy ke smazání.';
            } elseif (!delete_audit_log_entries($mysqli, $selectedIds)) {
                $data['errors'][] = 'Vybrané audit logy se nepodařilo smazat.';
            } else {
                header('Location: auditlog.php?ok=bulk_deleted');
                exit;
            }
        }
    }

    if (isset($_GET['ok']) && (string) $_GET['ok'] === 'deleted') {
        $data['okMessage'] = 'Záznam audit logu byl smazán.';
    }
    if (isset($_GET['ok']) && (string) $_GET['ok'] === 'bulk_deleted') {
        $data['okMessage'] = 'Vybrané audit logy byly smazány.';
    }

    if ($dbError === null) {
        $loadedLogs = fetch_audit_logs($mysqli);
        if ($loadedLogs !== null) {
            $data['logs'] = $loadedLogs;
        } else {
            $dbError = 'Tabulka audit_log nebyla nalezena. Spusť migraci audit logu.';
        }
    }

    return $data;
}
