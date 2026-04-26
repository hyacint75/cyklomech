<main class="mx-auto max-w-md px-6 py-12">
    <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h1 class="text-2xl font-bold font-display">Přihlášení do administrace</h1>
        <p class="mt-2 text-sm text-slate-600">Zadej přístupové údaje správce.</p>

        <?php if ($dbError !== null): ?>
            <div class="mt-4 rounded-xl border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900">
                <?php echo e($dbError); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($infoMessage)): ?>
            <div class="mt-4 rounded-xl border border-sky-300 bg-sky-50 p-3 text-sm text-sky-900">
                <?php echo e($infoMessage); ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="mt-4 rounded-xl border border-red-300 bg-red-50 p-3 text-sm text-red-900">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="mt-5 space-y-4">
            <input type="hidden" name="next" value="<?php echo e($next); ?>">
            <div>
                <label for="username" class="mb-1 block text-sm font-medium">Uživatelské jméno</label>
                <input id="username" name="username" type="text" required class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e($username); ?>">
            </div>
            <div>
                <label for="password" class="mb-1 block text-sm font-medium">Heslo</label>
                <input id="password" name="password" type="password" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
            <button type="submit" class="btn-gradient rounded-lg px-4 py-2 text-sm font-semibold">Přihlásit se</button>
        </form>
    </section>
</main>
