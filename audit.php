<?php
require_once __DIR__ . '/auth.php';

function write_audit_log(mysqli $mysqli, string $action, string $entityType, ?int $entityId = null, array $details = []): void
{
    $username = current_username();
    $detailsJson = $details === [] ? null : json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $stmt = $mysqli->prepare('INSERT INTO audit_log (username, action, entity_type, entity_id, details_json) VALUES (?, ?, ?, ?, ?)');
    if (!$stmt) {
        return;
    }

    $stmt->bind_param('sssis', $username, $action, $entityType, $entityId, $detailsJson);
    $stmt->execute();
    $stmt->close();
}
