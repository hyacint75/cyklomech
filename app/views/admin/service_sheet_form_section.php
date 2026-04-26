<?php
$sheetNumber = (string) ($serviceSheet['sheet_number'] ?? '');
$repairDate = (string) ($serviceSheet['repair_date'] ?? '');
$serialNumber = (string) ($serviceSheet['serial_number'] ?? '');
$bikeDescription = trim((string) ($serviceBikeSerial['bike_description'] ?? ''));
?>

<style>
    .service-sheet-brand {
        background: linear-gradient(135deg, rgba(122, 91, 15, 0.96), rgba(212, 175, 55, 0.92));
    }

    .service-sheet-logo {
        max-width: 10rem;
        max-height: 4.75rem;
        object-fit: contain;
        filter: drop-shadow(0 8px 18px rgba(15, 23, 42, 0.18));
    }

    @media print {
        .no-print {
            display: none !important;
        }

        body {
            background: #fff !important;
        }

        .service-sheet-card {
            box-shadow: none !important;
            border: 1px solid #d1d5db !important;
        }
    }
</style>

<section class="service-sheet-card rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
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

    <div class="service-sheet-brand flex flex-wrap items-start justify-between gap-4 rounded-2xl px-6 py-5 text-white">
        <div class="flex flex-wrap items-center gap-4">
            <div class="rounded-2xl bg-white/12 p-3 backdrop-blur-sm">
                <img src="<?php echo e($serviceSheetLogoPath); ?>" alt="CykloFlos" class="service-sheet-logo">
            </div>
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-white/80">Servisní list</p>
                <h2 class="mt-2 text-3xl font-black"><?php echo e($sheetNumber); ?></h2>
                <?php if ($bikeDescription !== ''): ?>
                    <p class="mt-2 max-w-2xl text-sm text-white/85"><?php echo e($bikeDescription); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="no-print flex flex-wrap gap-3">
            <button type="button" onclick="window.print();" class="btn-gradient rounded-lg px-4 py-2 text-sm font-semibold">Tisk / Uložit do PDF</button>
            <a href="service_work.php" class="rounded-lg border border-white/30 bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/20">Zpět na servisní práce</a>
        </div>
    </div>

    <div class="no-print mt-6 rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-200">
        <div class="flex flex-wrap items-end gap-3">
            <div class="min-w-[16rem] flex-1">
                <label for="email_recipient" class="mb-1 block text-sm font-medium">E-mail pro odeslání PDF</label>
                <input form="service-sheet-email-form" id="email_recipient" name="email_recipient" type="email" value="<?php echo e($emailRecipient); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="např. zakaznik@email.cz">
            </div>
            <form method="post" id="service-sheet-email-form" class="flex items-end">
                <input type="hidden" name="action" value="send_service_sheet_email">
                <input type="hidden" name="sheet_id" value="<?php echo (int) ($serviceSheet['id'] ?? 0); ?>">
                <input type="hidden" name="redirect_to" value="<?php echo e('service_sheet.php?sheet_id=' . (int) ($serviceSheet['id'] ?? 0)); ?>">
                <button type="submit" class="btn-gradient rounded-lg px-4 py-2 text-sm font-semibold">Odeslat PDF e-mailem</button>
            </form>
        </div>
    </div>

    <form method="post" class="mt-6 space-y-6" id="service-sheet-form">
        <input type="hidden" name="action" value="save_service_sheet">
        <input type="hidden" name="sheet_id" value="<?php echo (int) ($serviceSheet['id'] ?? 0); ?>">
        <input type="hidden" name="redirect_to" value="<?php echo e('service_sheet.php?sheet_id=' . (int) ($serviceSheet['id'] ?? 0)); ?>">

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Datum opravy</p>
                <p class="mt-2 text-lg font-semibold text-slate-900"><?php echo e($repairDate); ?></p>
            </div>
            <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Výrobní číslo kola</p>
                <p class="mt-2 break-all font-mono text-lg font-semibold text-slate-900"><?php echo e($serialNumber); ?></p>
            </div>
            <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200">
                <label for="request_text" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Požadavek</label>
                <textarea id="request_text" name="request_text" rows="3" class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?php echo e($serviceSheetRequest); ?></textarea>
            </div>
        </div>

        <section class="rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-200">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold">Materiál</h3>
                    <p class="mt-1 text-sm text-slate-600">Přidej použité díly a jejich cenu.</p>
                </div>
                <button type="button" class="btn-gradient rounded-lg px-4 py-2 text-sm font-semibold" data-add-material>Nová položka</button>
            </div>

            <div class="mt-4 space-y-3" data-material-list>
                <?php foreach ($materialItemsForm as $item): ?>
                    <div class="grid gap-3 rounded-xl bg-white p-4 ring-1 ring-slate-200 md:grid-cols-[minmax(0,1.6fr),120px,140px,auto]">
                        <div>
                            <label class="mb-1 block text-sm font-medium">Název dílu</label>
                            <input name="material_name[]" type="text" value="<?php echo e((string) ($item['item_name'] ?? '')); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Ks</label>
                            <input name="material_quantity[]" type="number" min="0" step="0.01" value="<?php echo e((string) ($item['quantity'] ?? '1')); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2" data-material-quantity>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Cena</label>
                            <input name="material_price[]" type="number" min="0" step="0.01" value="<?php echo e((string) ($item['price'] ?? '0')); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2" data-material-price>
                        </div>
                        <div class="flex items-end">
                            <button type="button" class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 hover:bg-slate-100" data-remove-row>Odebrat</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-200">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold">Práce</h3>
                    <p class="mt-1 text-sm text-slate-600">Přidej provedené servisní operace.</p>
                </div>
                <button type="button" class="btn-gradient rounded-lg px-4 py-2 text-sm font-semibold" data-add-labor>Nová operace</button>
            </div>

            <div class="mt-4 space-y-3" data-labor-list>
                <?php foreach ($laborItemsForm as $item): ?>
                    <div class="grid gap-3 rounded-xl bg-white p-4 ring-1 ring-slate-200 md:grid-cols-[minmax(0,1.6fr),140px,auto]">
                        <div>
                            <label class="mb-1 block text-sm font-medium">Operace</label>
                            <input name="labor_name[]" type="text" value="<?php echo e((string) ($item['operation_name'] ?? '')); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Cena</label>
                            <input name="labor_price[]" type="number" min="0" step="0.01" value="<?php echo e((string) ($item['price'] ?? '0')); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2" data-labor-price>
                        </div>
                        <div class="flex items-end">
                            <button type="button" class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 hover:bg-slate-100" data-remove-row>Odebrat</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200">
                <p class="text-sm font-medium text-slate-500">Materiál celkem</p>
                <p class="mt-2 text-2xl font-bold text-slate-900" data-material-total><?php echo e(number_format($materialTotalForm, 2, ',', ' ')); ?> Kč</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200">
                <p class="text-sm font-medium text-slate-500">Práce celkem</p>
                <p class="mt-2 text-2xl font-bold text-slate-900" data-labor-total><?php echo e(number_format($laborTotalForm, 2, ',', ' ')); ?> Kč</p>
            </div>
            <div class="rounded-xl bg-amber-50 p-4 ring-1 ring-amber-200">
                <p class="text-sm font-medium text-amber-800">Celková cena</p>
                <p class="mt-2 text-3xl font-black text-amber-900" data-grand-total><?php echo e(number_format($totalPriceForm, 2, ',', ' ')); ?> Kč</p>
            </div>
        </div>

        <div class="no-print flex flex-wrap justify-end gap-3">
            <button type="submit" class="btn-gradient rounded-lg px-5 py-3 text-sm font-semibold">Uložit servisní list</button>
        </div>
    </form>
</section>

<template id="material-row-template">
    <div class="grid gap-3 rounded-xl bg-white p-4 ring-1 ring-slate-200 md:grid-cols-[minmax(0,1.6fr),120px,140px,auto]">
        <div>
            <label class="mb-1 block text-sm font-medium">Název dílu</label>
            <input name="material_name[]" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Ks</label>
            <input name="material_quantity[]" type="number" min="0" step="0.01" value="1" class="w-full rounded-lg border border-slate-300 px-3 py-2" data-material-quantity>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Cena</label>
            <input name="material_price[]" type="number" min="0" step="0.01" value="0" class="w-full rounded-lg border border-slate-300 px-3 py-2" data-material-price>
        </div>
        <div class="flex items-end">
            <button type="button" class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 hover:bg-slate-100" data-remove-row>Odebrat</button>
        </div>
    </div>
</template>

<template id="labor-row-template">
    <div class="grid gap-3 rounded-xl bg-white p-4 ring-1 ring-slate-200 md:grid-cols-[minmax(0,1.6fr),140px,auto]">
        <div>
            <label class="mb-1 block text-sm font-medium">Operace</label>
            <input name="labor_name[]" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Cena</label>
            <input name="labor_price[]" type="number" min="0" step="0.01" value="0" class="w-full rounded-lg border border-slate-300 px-3 py-2" data-labor-price>
        </div>
        <div class="flex items-end">
            <button type="button" class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 hover:bg-slate-100" data-remove-row>Odebrat</button>
        </div>
    </div>
</template>

<script>
    (function () {
        var materialList = document.querySelector('[data-material-list]');
        var laborList = document.querySelector('[data-labor-list]');
        var materialTemplate = document.getElementById('material-row-template');
        var laborTemplate = document.getElementById('labor-row-template');
        var addMaterialButton = document.querySelector('[data-add-material]');
        var addLaborButton = document.querySelector('[data-add-labor]');
        var materialTotalNode = document.querySelector('[data-material-total]');
        var laborTotalNode = document.querySelector('[data-labor-total]');
        var grandTotalNode = document.querySelector('[data-grand-total]');

        function parseNumber(value) {
            var normalized = String(value || '').replace(',', '.');
            var number = Number.parseFloat(normalized);
            return Number.isFinite(number) ? number : 0;
        }

        function formatPrice(value) {
            return value.toLocaleString('cs-CZ', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' Kč';
        }

        function updateTotals() {
            var materialTotal = 0;
            document.querySelectorAll('[data-material-list] > div').forEach(function (row) {
                var quantity = parseNumber(row.querySelector('[data-material-quantity]') ? row.querySelector('[data-material-quantity]').value : 0);
                var price = parseNumber(row.querySelector('[data-material-price]') ? row.querySelector('[data-material-price]').value : 0);
                materialTotal += quantity * price;
            });

            var laborTotal = 0;
            document.querySelectorAll('[data-labor-list] > div').forEach(function (row) {
                var price = parseNumber(row.querySelector('[data-labor-price]') ? row.querySelector('[data-labor-price]').value : 0);
                laborTotal += price;
            });

            materialTotalNode.textContent = formatPrice(materialTotal);
            laborTotalNode.textContent = formatPrice(laborTotal);
            grandTotalNode.textContent = formatPrice(materialTotal + laborTotal);
        }

        function removeRow(button) {
            var row = button.closest('div.grid');
            var parent = row ? row.parentElement : null;
            if (!row || !parent) {
                return;
            }

            row.remove();

            if (parent.children.length === 0) {
                if (parent === materialList && materialTemplate) {
                    materialList.appendChild(materialTemplate.content.firstElementChild.cloneNode(true));
                }

                if (parent === laborList && laborTemplate) {
                    laborList.appendChild(laborTemplate.content.firstElementChild.cloneNode(true));
                }
            }

            updateTotals();
        }

        document.addEventListener('click', function (event) {
            var removeButton = event.target.closest('[data-remove-row]');
            if (removeButton) {
                removeRow(removeButton);
                return;
            }

            if (event.target.closest('[data-add-material]') && materialTemplate) {
                materialList.appendChild(materialTemplate.content.firstElementChild.cloneNode(true));
                updateTotals();
                return;
            }

            if (event.target.closest('[data-add-labor]') && laborTemplate) {
                laborList.appendChild(laborTemplate.content.firstElementChild.cloneNode(true));
                updateTotals();
            }
        });

        document.addEventListener('input', function (event) {
            if (event.target.matches('[data-material-quantity], [data-material-price], [data-labor-price]')) {
                updateTotals();
            }
        });

        updateTotals();
    })();
</script>
