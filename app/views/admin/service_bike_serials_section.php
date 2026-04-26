<?php
$serviceSheetsBySerialId = [];
foreach ($serviceSheets as $serviceSheet) {
    $sheetSerialId = (int) ($serviceSheet['serial_id'] ?? 0);
    if ($sheetSerialId <= 0) {
        continue;
    }

    if (!isset($serviceSheetsBySerialId[$sheetSerialId])) {
        $serviceSheetsBySerialId[$sheetSerialId] = [];
    }

    $serviceSheetsBySerialId[$sheetSerialId][] = $serviceSheet;
}
?>

<section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <?php if ($okMessage !== ''): ?>
        <div class="mb-4 rounded-xl border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-900"><?php echo e($okMessage); ?></div>
    <?php endif; ?>

    <?php if (count($errors) > 0): ?>
        <div class="mb-4 rounded-xl border border-red-300 bg-red-50 p-3 text-sm text-red-900">
            <ul class="list-inside list-disc space-y-1">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold">Databáze servisovaných kol</h2>
            <p class="mt-2 text-sm text-slate-600">Společná databáze výrobních čísel pro přístup do servisu a evidenci servisovaných kol.</p>
        </div>
        <a href="servis.php" target="_blank" rel="noopener noreferrer" class="btn-gradient rounded-lg px-4 py-2 text-sm font-semibold">Otevřít zákaznickou stránku</a>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(280px,0.58fr),minmax(0,1.42fr)]">
        <div class="rounded-xl bg-slate-50 p-5 ring-1 ring-slate-200 lg:max-w-md">
            <h3 class="text-lg font-semibold">Přidat servisované kolo</h3>
            <p class="mt-2 text-sm text-slate-600">Zákazník se do rezervací přihlásí jen tehdy, když zadané výrobní číslo existuje v této databázi.</p>
            <form method="post" class="mt-4 space-y-4">
                <input type="hidden" name="action" value="create_service_bike_serial">
                <input type="hidden" name="redirect_to" value="service_work.php">
                <div>
                    <label for="sale_date" class="mb-1 block text-sm font-medium">Datum prodeje</label>
                    <input id="sale_date" name="sale_date" type="date" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label for="service_date" class="mb-1 block text-sm font-medium">Datum servisu</label>
                    <input id="service_date" name="service_date" type="date" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label for="serial_number" class="mb-1 block text-sm font-medium">Výrobní číslo kola</label>
                    <input id="serial_number" name="serial_number" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label for="bike_description" class="mb-1 block text-sm font-medium">Popis kola</label>
                    <input id="bike_description" name="bike_description" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Například model, barva nebo poznámka k servisu">
                </div>
                <button type="submit" class="btn-gradient rounded-lg px-4 py-2 text-sm font-semibold">Přidat servisované kolo</button>
            </form>
        </div>

        <div class="rounded-xl bg-slate-50 p-5 ring-1 ring-slate-200">
            <h3 class="text-lg font-semibold">Seznam servisovaných kol</h3>
            <div class="mt-3">
                <table class="w-full table-fixed text-sm">
                    <thead class="text-left text-slate-500">
                        <tr class="border-b border-slate-200">
                            <th class="px-2 py-2">Datum prodeje</th>
                            <th class="px-2 py-2">Datum servisu</th>
                            <th class="px-2 py-2">Výrobní číslo kola</th>
                            <th class="w-[24%] px-2 py-2">Popis kola</th>
                            <th class="w-[20%] px-2 py-2">Servisní listy</th>
                            <th class="w-[20%] px-2 py-2">Akce</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($serviceBikeSerials) === 0): ?>
                            <tr>
                                <td colspan="6" class="px-2 py-4 text-slate-500">Zatím zde nejsou žádná servisovaná kola.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($serviceBikeSerials as $serviceBikeSerial): ?>
                                <?php $linkedSheets = $serviceSheetsBySerialId[(int) $serviceBikeSerial['id']] ?? []; ?>
                                <tr class="border-b border-slate-100 align-top">
                                    <td class="px-2 py-2"><?php echo e((string) $serviceBikeSerial['sale_date']); ?></td>
                                    <td class="px-2 py-2"><?php echo e((string) ($serviceBikeSerial['service_date'] ?? '')); ?></td>
                                    <td class="break-all px-2 py-2 font-mono"><?php echo e((string) $serviceBikeSerial['serial_number']); ?></td>
                                    <td class="break-words px-2 py-2"><?php echo e((string) ($serviceBikeSerial['bike_description'] ?? '')); ?></td>
                                    <td class="px-2 py-2">
                                        <?php if ($linkedSheets === []): ?>
                                            <span class="text-slate-500">Žádný list</span>
                                        <?php else: ?>
                                            <div class="flex flex-col gap-2">
                                                <?php foreach ($linkedSheets as $linkedSheet): ?>
                                                    <a href="service_sheet.php?sheet_id=<?php echo (int) ($linkedSheet['id'] ?? 0); ?>" class="inline-flex w-fit break-all rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                                                        <?php echo e((string) ($linkedSheet['sheet_number'] ?? '')); ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-2 py-2">
                                        <div class="flex flex-col gap-2">
                                            <?php if (trim((string) ($serviceBikeSerial['service_date'] ?? '')) !== ''): ?>
                                                <a href="service_sheet.php?serial_id=<?php echo (int) $serviceBikeSerial['id']; ?>" class="rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-900 hover:bg-amber-100">Nový servisní list</a>
                                            <?php endif; ?>
                                            <form method="post" onsubmit="return confirm('Opravdu chcete smazat toto výrobní číslo?');">
                                                <input type="hidden" name="action" value="delete_service_bike_serial">
                                                <input type="hidden" name="serial_id" value="<?php echo (int) $serviceBikeSerial['id']; ?>">
                                                <input type="hidden" name="redirect_to" value="service_work.php">
                                                <button type="submit" class="rounded-lg border border-red-300 px-3 py-2 text-red-700 hover:bg-red-50">Smazat</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<section class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold">Seznam servisních listů</h2>
            <p class="mt-2 text-sm text-slate-600">Přehled všech založených servisních listů s rychlým otevřením detailu.</p>
        </div>
    </div>

    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="text-left text-slate-500">
                <tr class="border-b border-slate-200">
                    <th class="px-2 py-2">Číslo listu</th>
                    <th class="px-2 py-2">Datum opravy</th>
                    <th class="px-2 py-2">Výrobní číslo kola</th>
                    <th class="px-2 py-2">Požadavek</th>
                    <th class="px-2 py-2">Celková cena</th>
                    <th class="px-2 py-2">Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($serviceSheets) === 0): ?>
                    <tr>
                        <td colspan="6" class="px-2 py-4 text-slate-500">Zatím zde nejsou žádné servisní listy.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($serviceSheets as $serviceSheet): ?>
                        <tr class="border-b border-slate-100 align-top">
                            <td class="px-2 py-2 font-semibold"><?php echo e((string) ($serviceSheet['sheet_number'] ?? '')); ?></td>
                            <td class="px-2 py-2"><?php echo e((string) ($serviceSheet['repair_date'] ?? '')); ?></td>
                            <td class="px-2 py-2 font-mono"><?php echo e((string) ($serviceSheet['serial_number'] ?? '')); ?></td>
                            <td class="px-2 py-2"><?php echo e((string) ($serviceSheet['request_text'] ?? '')); ?></td>
                            <td class="px-2 py-2 font-semibold"><?php echo e(number_format((float) ($serviceSheet['total_price'] ?? 0), 2, ',', ' ')); ?> Kč</td>
                            <td class="px-2 py-2">
                                <a href="service_sheet.php?sheet_id=<?php echo (int) ($serviceSheet['id'] ?? 0); ?>" class="rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-900 hover:bg-amber-100">Otevřít</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
