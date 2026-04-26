<?php

function fetch_audit_logs(mysqli $mysqli): ?array
{
    $logs = [];
    $sql = 'SELECT id, username, action, entity_type, entity_id, details_json, created_at FROM audit_log ORDER BY id DESC LIMIT 200';
    if ($resultLogs = $mysqli->query($sql)) {
        while ($row = $resultLogs->fetch_assoc()) {
            $logs[] = $row;
        }
        $resultLogs->free();

        return $logs;
    }

    return null;
}

function delete_audit_log_entry(mysqli $mysqli, int $id): bool
{
    if ($id <= 0) {
        return false;
    }

    $stmt = $mysqli->prepare('DELETE FROM audit_log WHERE id = ? LIMIT 1');
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function delete_audit_log_entries(mysqli $mysqli, array $ids): bool
{
    $ids = array_values(array_filter(array_map(
        static fn ($id): int => (int) $id,
        $ids
    ), static fn (int $id): bool => $id > 0));

    if ($ids === []) {
        return false;
    }

    $placeholders = implode(', ', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $stmt = $mysqli->prepare("DELETE FROM audit_log WHERE id IN ({$placeholders})");
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param($types, ...$ids);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}
