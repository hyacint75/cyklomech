<main class="mx-auto max-w-[1680px] px-6 py-8">
    <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h1 class="text-xl font-bold">Audit log změn</h1>
        <p class="mt-2 text-sm text-slate-600">Posledních 200 záznamů o změnách v administraci.</p>

        <?php if ($okMessage !== ''): ?>
            <div class="mt-4 rounded-xl border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-900">
                <?php echo e($okMessage); ?>
            </div>
        <?php endif; ?>

        <?php if (count($errors) > 0): ?>
            <div class="mt-4 rounded-xl border border-red-300 bg-red-50 p-3 text-sm text-red-900">
                <ul class="list-inside list-disc space-y-1">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($dbError !== null): ?>
            <div class="mt-4 rounded-xl border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900">
                <?php echo e($dbError); ?>
            </div>
        <?php elseif (count($logs) === 0): ?>
            <p class="mt-4 text-sm text-slate-600">Zatím nejsou žádné auditní záznamy.</p>
        <?php else: ?>
            <form id="bulk-audit-log-form" method="post" class="mt-4 space-y-3" onsubmit="return confirmBulkDelete();">
                <input type="hidden" name="action" value="delete_selected_audit_logs">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" id="select-all-audit-logs" class="rounded border-slate-300">
                        Vybrat vše
                    </label>
                    <button type="submit" class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">Smazat vybrané</button>
                </div>
            </form>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-slate-500">
                        <tr class="border-b border-slate-200">
                            <th class="px-2 py-2">Výběr</th>
                            <th class="px-2 py-2">Čas</th>
                            <th class="px-2 py-2">Uživatel</th>
                            <th class="px-2 py-2">Akce</th>
                            <th class="px-2 py-2">Entita</th>
                            <th class="px-2 py-2">ID</th>
                            <th class="px-2 py-2">Detail</th>
                            <th class="px-2 py-2">Akce</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr class="border-b border-slate-100 align-top">
                                <td class="px-2 py-2">
                                    <input form="bulk-audit-log-form" type="checkbox" name="selected_logs[]" value="<?php echo (int) $log['id']; ?>" class="audit-log-checkbox rounded border-slate-300">
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap"><?php echo e((string) $log['created_at']); ?></td>
                                <td class="px-2 py-2"><?php echo e((string) $log['username']); ?></td>
                                <td class="px-2 py-2"><?php echo e((string) $log['action']); ?></td>
                                <td class="px-2 py-2"><?php echo e((string) $log['entity_type']); ?></td>
                                <td class="px-2 py-2"><?php echo e((string) ($log['entity_id'] ?? '')); ?></td>
                                <td class="px-2 py-2"><code class="text-xs text-slate-600"><?php echo e((string) ($log['details_json'] ?? '')); ?></code></td>
                                <td class="px-2 py-2">
                                    <form method="post" onsubmit="return confirm('Opravdu chcete smazat tento audit log?');">
                                        <input type="hidden" name="action" value="delete_audit_log">
                                        <input type="hidden" name="id" value="<?php echo (int) $log['id']; ?>">
                                        <button type="submit" class="rounded-md border border-red-300 px-3 py-1 text-red-700 hover:bg-red-50">Smazat</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <script>
                (function () {
                    var selectAll = document.getElementById('select-all-audit-logs');
                    var checkboxes = Array.prototype.slice.call(document.querySelectorAll('.audit-log-checkbox'));

                    if (!selectAll || checkboxes.length === 0) {
                        return;
                    }

                    selectAll.addEventListener('change', function () {
                        checkboxes.forEach(function (checkbox) {
                            checkbox.checked = selectAll.checked;
                        });
                    });

                    checkboxes.forEach(function (checkbox) {
                        checkbox.addEventListener('change', function () {
                            selectAll.checked = checkboxes.every(function (item) {
                                return item.checked;
                            });
                        });
                    });
                })();

                function confirmBulkDelete() {
                    var checked = document.querySelectorAll('.audit-log-checkbox:checked');
                    if (checked.length === 0) {
                        alert('Nejprve vyber audit logy ke smazání.');
                        return false;
                    }

                    return confirm('Opravdu chcete smazat vybrané audit logy?');
                }
            </script>
        <?php endif; ?>
    </section>
</main>
