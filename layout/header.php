<?php
require_once __DIR__ . '/../auth.php';

if (!isset($pageTitle) || $pageTitle === '') {
    $pageTitle = 'CykloFlos';
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
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/style.css?v=<?php echo (string) @filemtime(__DIR__ . '/../assets/style.css'); ?>">
</head>
<body class="bg-slate-100 text-slate-900">
    <header class="bg-forest-gradient text-white shadow-md">
        <div class="mx-auto flex max-w-[1680px] items-center justify-between px-6 py-4">
            <a href="<?php echo $basePath; ?>index.php" class="font-display text-2xl font-black tracking-wide">CykloFlos</a>
            <nav class="flex items-center gap-3 text-sm">
                <a href="<?php echo $basePath; ?>index.php" class="rounded-lg px-3 py-2 <?php echo $activePage === 'home' ? 'bg-white/20' : 'hover:bg-white/10'; ?>">Domů</a>
                <a href="<?php echo $basePath; ?>oteviraci-doba.php" class="rounded-lg px-3 py-2 <?php echo $activePage === 'hours' ? 'bg-white/20' : 'hover:bg-white/10'; ?>">Otevírací doba</a>
                <?php if ($isLogged): ?>
                    <a href="<?php echo $basePath; ?>admin.php" class="rounded-lg px-3 py-2 <?php echo $activePage === 'admin' ? 'bg-white/20' : 'hover:bg-white/10'; ?>">Administrace</a>
                    <a href="<?php echo $basePath; ?>users.php" class="rounded-lg px-3 py-2 <?php echo $activePage === 'users' ? 'bg-white/20' : 'hover:bg-white/10'; ?>">Uživatelé</a>
                    <a href="<?php echo $basePath; ?>auditlog.php" class="rounded-lg px-3 py-2 <?php echo $activePage === 'audit' ? 'bg-white/20' : 'hover:bg-white/10'; ?>">Audit log</a>
                <?php endif; ?>
                <?php if ($isLogged): ?>
                    <span class="rounded-lg bg-white/10 px-3 py-2">Uživatel: <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="<?php echo $basePath; ?>logout.php" class="rounded-lg px-3 py-2 hover:bg-white/10">Odhlásit</a>
                <?php else: ?>
                    <a href="<?php echo $basePath; ?>login.php" class="rounded-lg px-3 py-2 hover:bg-white/10">Přihlášení</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
