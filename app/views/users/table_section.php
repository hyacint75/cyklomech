<section class="lg:col-span-3 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <h2 class="text-xl font-bold">Přehled uživatelů</h2>

    <?php if ($dbError === null && count($users) === 0): ?>
        <p class="mt-4 text-sm text-slate-600">Zatím nejsou žádní uživatelé.</p>
    <?php elseif ($dbError === null): ?>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                    <tr class="border-b border-slate-200">
                        <th class="px-2 py-2">ID</th>
                        <th class="px-2 py-2">Uživatelské jméno</th>
                        <th class="px-2 py-2">Vytvořen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr class="border-b border-slate-100">
                            <td class="px-2 py-2"><?php echo (int) $user['id']; ?></td>
                            <td class="px-2 py-2 font-medium"><?php echo e((string) $user['username']); ?></td>
                            <td class="px-2 py-2"><?php echo e((string) $user['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
