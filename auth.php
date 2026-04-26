<?php
function ensure_session_started(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
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
}

function logout_user(): void
{
    ensure_session_started();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
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
    header('Location: ' . $target);
    exit;
}
