<section class="lg:col-span-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <h1 class="text-xl font-bold">Nový uživatel</h1>

    <?php if ($okMessage !== ''): ?>
        <div class="mt-4 rounded-xl border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-900"><?php echo e($okMessage); ?></div>
    <?php endif; ?>

    <?php if ($dbError !== null): ?>
        <div class="mt-4 rounded-xl border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900"><?php echo e($dbError); ?></div>
    <?php endif; ?>

    <?php if (count($errors) > 0): ?>
        <div class="mt-4 rounded-xl border border-red-300 bg-red-50 p-3 text-sm text-red-900">
            <ul class="list-disc list-inside space-y-1">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="mt-5 space-y-4">
        <div>
            <label for="username" class="mb-1 block text-sm font-medium">Uživatelské jméno</label>
            <input id="username" name="username" type="text" required class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e($form['username']); ?>">
        </div>
        <div>
            <label for="password" class="mb-1 block text-sm font-medium">Heslo</label>
            <input id="password" name="password" type="password" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>
        <button type="submit" class="btn-gradient rounded-lg px-4 py-2 text-sm font-semibold">Vytvořit uživatele</button>
    </form>
</section>
