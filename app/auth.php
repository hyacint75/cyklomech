<?php

function app_root_path(): string
{
    return defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__);
}

function preferred_session_path(): string
{
    return app_root_path() . '/storage/sessions';
}

function session_inactivity_timeout_seconds(): int
{
    return 900;
}

function service_session_inactivity_timeout_seconds(): int
{
    return 300;
}

function current_logout_reason(): string
{
    return (string) ($GLOBALS['auth_logout_reason'] ?? '');
}

function set_logout_reason(string $reason): void
{
    $GLOBALS['auth_logout_reason'] = $reason;
}

function ensure_session_storage_ready(): void
{
    $preferredPath = preferred_session_path();

    if (!is_dir($preferredPath)) {
        @mkdir($preferredPath, 0775, true);
    }

    if (is_dir($preferredPath) && is_writable($preferredPath)) {
        session_save_path($preferredPath);
        return;
    }

    $currentPath = (string) session_save_path();
    if ($currentPath !== '' && is_dir($currentPath) && is_writable($currentPath)) {
        return;
    }
}

function clear_active_session(string $reason = ''): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        if ($reason !== '') {
            set_logout_reason($reason);
        }
        return;
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();

    if ($reason !== '') {
        set_logout_reason($reason);
    }
}

function enforce_session_timeout(): void
{
    if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
        return;
    }

    $now = time();
    $lastActivity = (int) ($_SESSION['last_activity'] ?? 0);

    if ($lastActivity > 0 && ($now - $lastActivity) > session_inactivity_timeout_seconds()) {
        clear_active_session('inactive');
        return;
    }

    $_SESSION['last_activity'] = $now;
}

function ensure_session_started(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        ensure_session_storage_ready();
        session_cache_limiter('nocache');
        session_start();
    }

    enforce_session_timeout();
}

function normalize_next_path(string $next, string $default = 'admin.php'): string
{
    $next = trim($next);
    if ($next === '') {
        return $default;
    }

    if (preg_match('#^(?:[a-z][a-z0-9+.-]*:)?//#i', $next)) {
        return $default;
    }

    $next = ltrim($next, "/\\");
    if ($next === '' || str_contains($next, '..') || str_starts_with($next, '?') || str_starts_with($next, '#')) {
        return $default;
    }

    return $next;
}

function is_logged_in(): bool
{
    ensure_session_started();
    return isset($_SESSION['user']) && is_array($_SESSION['user']);
}

function current_username(): string
{
    ensure_session_started();
    return (string) ($_SESSION['user']['username'] ?? '');
}

function login_user(int $id, string $username): void
{
    ensure_session_started();
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => $id,
        'username' => $username,
    ];
    $_SESSION['last_activity'] = time();
}

function logout_user(string $reason = 'manual'): void
{
    ensure_session_started();
    clear_active_session($reason);
}

function require_login(string $next = ''): void
{
    if (is_logged_in()) {
        return;
    }

    if ($next === '') {
        $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? 'admin.php');
        $next = $requestUri;
    }

    $next = normalize_next_path($next);
    $target = 'login.php?next=' . rawurlencode($next);
    $reason = current_logout_reason();
    if ($reason !== '') {
        $target .= '&reason=' . rawurlencode($reason);
    }

    header('Location: ' . $target);
    exit;
}
