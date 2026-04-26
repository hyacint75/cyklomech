<?php
require_once __DIR__ . '/../auth.php';

if (!headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
}

if (!isset($pageTitle) || $pageTitle === '') {
    $pageTitle = defined('APP_NAME') ? APP_NAME : 'CykloFlos';
}

$activePage = $activePage ?? '';
$basePath = $basePath ?? '';
$isLogged = is_logged_in();
$username = current_username();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/style.css?v=<?php echo (string) @filemtime(__DIR__ . '/../../assets/style.css'); ?>">
</head>
<body class="bg-slate-100 text-slate-900">
    <div class="header-shell">
        <header class="relative overflow-hidden bg-forest-gradient text-white shadow-md">
            <div
                class="header-hero-pattern absolute inset-0 opacity-20"
                aria-hidden="true"
            ></div>
            <div class="relative mx-auto flex max-w-[1680px] items-center px-6 py-4">
                <a href="<?php echo $basePath; ?>index.php" class="rounded-xl border border-white/15 bg-slate-950/35 px-4 py-2 font-display text-2xl font-black tracking-wide backdrop-blur-sm"><?php echo defined('APP_NAME') ? APP_NAME : 'CykloFlos'; ?></a>
            </div>
        </header>
        <div class="border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur">
            <nav class="mx-auto flex max-w-[1680px] flex-wrap items-center gap-3 px-6 py-3 text-sm">
                <a href="<?php echo $basePath; ?>index.php" class="rounded-lg border border-slate-200 bg-white px-3 py-2 <?php echo $activePage === 'home' ? 'border-amber-200 bg-amber-50 text-amber-900' : 'hover:bg-slate-50'; ?>">Domů</a>
                <a href="<?php echo $basePath; ?>prodejna.php" class="rounded-lg border border-slate-200 bg-white px-3 py-2 <?php echo $activePage === 'showroom' ? 'border-amber-200 bg-amber-50 text-amber-900' : 'hover:bg-slate-50'; ?>">Katalog prodejny</a>
                <?php if (!$isLogged): ?>
                    <a href="<?php echo $basePath; ?>fotogalerie.php" class="rounded-lg border border-slate-200 bg-white px-3 py-2 <?php echo $activePage === 'gallery' ? 'border-amber-200 bg-amber-50 text-amber-900' : 'hover:bg-slate-50'; ?>">Fotogalerie</a>
                <?php endif; ?>
                <?php if (!$isLogged): ?>
                    <a href="<?php echo $basePath; ?>oteviraci-doba.php" class="rounded-lg border border-slate-200 bg-white px-3 py-2 <?php echo $activePage === 'hours' ? 'border-amber-200 bg-amber-50 text-amber-900' : 'hover:bg-slate-50'; ?>">Otevírací doba</a>
                <?php endif; ?>
                <?php if ($isLogged): ?>
                    <a href="<?php echo $basePath; ?>admin.php" class="rounded-lg border border-slate-200 bg-white px-3 py-2 <?php echo $activePage === 'admin' ? 'border-amber-200 bg-amber-50 text-amber-900' : 'hover:bg-slate-50'; ?>">Administrace</a>
                    <a href="<?php echo $basePath; ?>service_admin.php" class="rounded-lg border border-slate-200 bg-white px-3 py-2 <?php echo $activePage === 'service_admin' ? 'border-amber-200 bg-amber-50 text-amber-900' : 'hover:bg-slate-50'; ?>">Admin servisu</a>
                    <a href="<?php echo $basePath; ?>service_work.php" class="rounded-lg border border-slate-200 bg-white px-3 py-2 <?php echo $activePage === 'service_work' ? 'border-amber-200 bg-amber-50 text-amber-900' : 'hover:bg-slate-50'; ?>">Servisní práce</a>
                    <a href="<?php echo $basePath; ?>customer_cards.php" class="rounded-lg border border-slate-200 bg-white px-3 py-2 <?php echo $activePage === 'customer_cards' ? 'border-amber-200 bg-amber-50 text-amber-900' : 'hover:bg-slate-50'; ?>">Zákaznické karty</a>
                    <a href="<?php echo $basePath; ?>users.php" class="rounded-lg border border-slate-200 bg-white px-3 py-2 <?php echo $activePage === 'users' ? 'border-amber-200 bg-amber-50 text-amber-900' : 'hover:bg-slate-50'; ?>">Uživatelé</a>
                    <a href="<?php echo $basePath; ?>auditlog.php" class="rounded-lg border border-slate-200 bg-white px-3 py-2 <?php echo $activePage === 'audit' ? 'border-amber-200 bg-amber-50 text-amber-900' : 'hover:bg-slate-50'; ?>">Audit log</a>
                <?php endif; ?>
                <div class="flex w-full flex-wrap items-center gap-3 pt-1 md:ml-auto md:w-auto md:pt-0">
                    <?php if ($isLogged): ?>
                        <span class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">Uživatel: <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></span>
                        <a href="<?php echo $basePath; ?>logout.php" class="rounded-lg border border-slate-200 bg-white px-3 py-2 hover:bg-slate-50">Odhlásit</a>
                    <?php else: ?>
                        <a href="<?php echo $basePath; ?>login.php" class="rounded-lg border border-slate-200 bg-white px-3 py-2 hover:bg-slate-50">Přihlášení</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </div>
    <div class="header-spacer" aria-hidden="true"></div>
    <?php if ($isLogged): ?>
        <script>
            (function () {
                var logoutUrl = <?php echo json_encode($basePath . 'logout.php', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
                var inactivityMs = <?php echo (int) (session_inactivity_timeout_seconds() * 1000); ?>;
                var inactivityTimer = null;

                function scheduleInactivityLogout() {
                    window.clearTimeout(inactivityTimer);
                    inactivityTimer = window.setTimeout(function () {
                        window.location.href = logoutUrl + '?reason=inactive';
                    }, inactivityMs);
                }

                ['click', 'keydown', 'mousemove', 'scroll', 'touchstart', 'focus'].forEach(function (eventName) {
                    document.addEventListener(eventName, scheduleInactivityLogout, { passive: true });
                });

                scheduleInactivityLogout();
            })();
        </script>
    <?php endif; ?>
