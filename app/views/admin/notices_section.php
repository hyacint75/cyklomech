<section class="lg:col-span-5 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold">Důležité informace na úvodní stránce</h2>
            <p class="mt-2 text-sm text-slate-600">Informace se zobrazují na indexu v popředí jako okno. Můžeš vložit více řádků najednou.</p>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-2">
            <a href="admin.php?export=csv" class="btn-gradient rounded-lg px-4 py-2 text-sm font-semibold">Export kol do CSV</a>
            <form method="post" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="action" value="import_bikes_csv">
                <input type="file" name="bikes_csv" accept=".csv,text/csv" class="max-w-xs rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <button type="submit" class="btn-gradient rounded-lg px-4 py-2 text-sm font-semibold">Import CSV kol</button>
            </form>
        </div>
    </div>

    <?php if ($okMessage !== ''): ?>
        <div class="mt-4 rounded-xl border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-900"><?php echo e($okMessage); ?></div>
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

    <form method="post" class="mt-4 space-y-4">
        <input type="hidden" name="action" value="add_notices">
        <div>
            <label for="notice_bulk_text" class="mb-1 block text-sm font-medium">Texty informací (každý řádek = 1 informace)</label>
            <textarea id="notice_bulk_text" name="notice_bulk_text" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2"><?php echo e($noticeBulkText); ?></textarea>
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="notice_bulk_active" value="1" <?php echo ((int) $noticeBulkActive === 1) ? 'checked' : ''; ?>>
            Nové informace hned aktivovat
        </label>
        <button type="submit" class="btn-gradient rounded-lg px-4 py-2 text-sm font-semibold">Přidat informace</button>
    </form>

    <div class="mt-6 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="text-left text-slate-500">
                <tr class="border-b border-slate-200">
                    <th class="px-2 py-2">Text</th>
                    <th class="px-2 py-2">Stav</th>
                    <th class="px-2 py-2">Aktualizoval</th>
                    <th class="px-2 py-2">Čas</th>
                    <th class="px-2 py-2">Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notices as $notice): ?>
                    <tr class="border-b border-slate-100 align-top">
                        <td class="px-2 py-2"><?php echo e((string) $notice['message']); ?></td>
                        <td class="px-2 py-2"><?php echo ((int) $notice['is_active'] === 1) ? 'Aktivní' : 'Neaktivní'; ?></td>
                        <td class="px-2 py-2"><?php echo e((string) ($notice['updated_by'] ?? '')); ?></td>
                        <td class="px-2 py-2"><?php echo e((string) ($notice['updated_at'] ?? '')); ?></td>
                        <td class="px-2 py-2">
                            <div class="flex flex-wrap gap-2">
                                <form method="post">
                                    <input type="hidden" name="action" value="toggle_notice">
                                    <input type="hidden" name="notice_id" value="<?php echo (int) $notice['id']; ?>">
                                    <input type="hidden" name="next_state" value="<?php echo ((int) $notice['is_active'] === 1) ? 0 : 1; ?>">
                                    <button type="submit" class="rounded-md border border-slate-300 px-3 py-1 hover:bg-slate-50"><?php echo ((int) $notice['is_active'] === 1) ? 'Vypnout' : 'Zapnout'; ?></button>
                                </form>
                                <form method="post" onsubmit="return confirm('Opravdu chcete smazat tuto informaci?');">
                                    <input type="hidden" name="action" value="delete_notice">
                                    <input type="hidden" name="notice_id" value="<?php echo (int) $notice['id']; ?>">
                                    <button type="submit" class="rounded-md border border-red-300 px-3 py-1 text-red-700 hover:bg-red-50">Smazat</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
