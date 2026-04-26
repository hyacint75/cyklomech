<style>
    @media print {
        body {
            margin: 0;
        }

        .header-shell,
        .header-spacer,
        main > section:first-child,
        .customer-card-list-panel {
            display: none !important;
        }

        main {
            display: block !important;
            margin: 0 !important;
            max-width: none !important;
            padding: 0 !important;
        }

        main > section {
            display: block !important;
        }

        .customer-card-print-panel {
            display: block !important;
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
        }

        .customer-card-print-panel > * {
            display: none !important;
        }

        #customer-card-preview {
            display: block !important;
            margin: 0;
            width: 180mm;
            max-height: 260mm;
            box-shadow: none !important;
            page-break-after: avoid;
            page-break-before: avoid;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        @page {
            margin: 10mm;
            size: A4 portrait;
        }
    }
</style>

<main class="mx-auto grid max-w-[1680px] gap-6 px-6 py-8 lg:grid-cols-5">
    <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 lg:col-span-2">
        <h1 class="text-xl font-bold">Nová zákaznická karta</h1>
        <p class="mt-2 text-sm text-slate-600">Vyplňte údaje zákazníka. Unikátní kód se vygeneruje automaticky.</p>

        <?php if ($okMessage !== ''): ?>
            <div class="mt-4 rounded-xl border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-900"><?php echo e($okMessage); ?></div>
        <?php endif; ?>

        <?php if ($dbError !== null): ?>
            <div class="mt-4 rounded-xl border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900"><?php echo e($dbError); ?></div>
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

        <form method="post" class="mt-5 space-y-4">
            <div>
                <label for="customer_name" class="mb-1 block text-sm font-medium">Jméno zákazníka</label>
                <input id="customer_name" name="customer_name" type="text" required class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e($form['customer_name']); ?>">
            </div>
            <div>
                <label for="email" class="mb-1 block text-sm font-medium">E-mail</label>
                <input id="email" name="email" type="email" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e($form['email']); ?>">
            </div>
            <div>
                <label for="phone" class="mb-1 block text-sm font-medium">Telefon</label>
                <input id="phone" name="phone" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e($form['phone']); ?>">
            </div>
            <div>
                <label for="bike_name" class="mb-1 block text-sm font-medium">Název kola</label>
                <input id="bike_name" name="bike_name" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e($form['bike_name']); ?>">
            </div>
            <div>
                <label for="serial_number" class="mb-1 block text-sm font-medium">Výrobní číslo</label>
                <input id="serial_number" name="serial_number" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e($form['serial_number']); ?>">
            </div>
            <div>
                <label for="note" class="mb-1 block text-sm font-medium">Poznámka</label>
                <textarea id="note" name="note" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2"><?php echo e($form['note']); ?></textarea>
            </div>
            <button type="submit" class="btn-gradient rounded-lg px-4 py-2 text-sm font-semibold">Vygenerovat kartu</button>
        </form>
    </section>

    <section class="space-y-6 lg:col-span-3">
        <?php if (is_array($createdCard)): ?>
            <div class="customer-card-print-panel rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-amber-700">Vygenerovaná karta</p>
                        <h2 class="mt-2 text-2xl font-black text-slate-900"><?php echo e((string) $createdCard['customer_name']); ?></h2>
                    </div>
                    <button type="button" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-50" onclick="printCustomerCardPreview()">Tisk</button>
                </div>

                <div class="mt-5 overflow-hidden rounded-2xl bg-forest-gradient p-6 text-white shadow-lg" id="customer-card-preview">
                    <p class="text-sm uppercase tracking-[0.3em] text-white/70">CYKLOMECH</p>
                    <div class="mt-8">
                        <p class="text-sm text-white/70">Zákaznická karta</p>
                        <p class="mt-2 break-all font-mono text-4xl font-black tracking-wider"><?php echo e((string) $createdCard['card_code']); ?></p>
                    </div>
                    <div class="mt-8 flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-sm text-white/70">Držitel</p>
                            <p class="mt-1 text-xl font-bold"><?php echo e((string) $createdCard['customer_name']); ?></p>
                        </div>
                        <?php if (trim((string) ($createdCard['bike_name'] ?? '')) !== '' || trim((string) ($createdCard['serial_number'] ?? '')) !== ''): ?>
                            <div>
                                <p class="text-sm text-white/70">Kolo</p>
                                <?php if (trim((string) ($createdCard['bike_name'] ?? '')) !== ''): ?>
                                    <p class="mt-1 text-xl font-bold"><?php echo e((string) $createdCard['bike_name']); ?></p>
                                <?php endif; ?>
                                <?php if (trim((string) ($createdCard['serial_number'] ?? '')) !== ''): ?>
                                    <p class="mt-1 font-mono text-sm text-white/80"><?php echo e((string) $createdCard['serial_number']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <p class="text-sm text-white/70">Vytvořeno <?php echo e((string) $createdCard['created_at']); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="customer-card-list-panel rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-xl font-bold">Přehled zákaznických karet</h2>

            <?php if ($dbError === null && count($cards) === 0): ?>
                <p class="mt-4 text-sm text-slate-600">Zatím nejsou vytvořené žádné zákaznické karty.</p>
            <?php elseif ($dbError === null): ?>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-slate-500">
                            <tr class="border-b border-slate-200">
                                <th class="px-2 py-2">Kód</th>
                                <th class="px-2 py-2">Zákazník</th>
                                <th class="px-2 py-2">Kolo</th>
                                <th class="px-2 py-2">Vytvořeno</th>
                                <th class="px-2 py-2">Akce</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cards as $card): ?>
                                <tr class="border-b border-slate-100">
                                    <td class="px-2 py-2 font-mono font-semibold"><?php echo e((string) $card['card_code']); ?></td>
                                    <td class="px-2 py-2 font-medium"><?php echo e((string) $card['customer_name']); ?></td>
                                    <td class="px-2 py-2">
                                        <?php if (trim((string) ($card['bike_name'] ?? '')) !== ''): ?>
                                            <p><?php echo e((string) $card['bike_name']); ?></p>
                                        <?php endif; ?>
                                        <?php if (trim((string) ($card['serial_number'] ?? '')) !== ''): ?>
                                            <p class="font-mono text-slate-500"><?php echo e((string) $card['serial_number']); ?></p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-2 py-2"><?php echo e((string) $card['created_at']); ?></td>
                                    <td class="px-2 py-2">
                                        <a href="customer_cards.php?created=<?php echo (int) $card['id']; ?>" class="rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-900 hover:bg-amber-100">Zobrazit</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<script>
    function printCustomerCardPreview() {
        var card = document.getElementById('customer-card-preview');
        if (!card) {
            return;
        }

        var printWindow = window.open('', '_blank', 'width=900,height=700');
        if (!printWindow) {
            window.print();
            return;
        }

        var cardHtml = card.outerHTML
            .replace(/^<div[^>]*id="customer-card-preview"[^>]*>/, '<div class="card">')
            .replaceAll('class="text-sm uppercase tracking-[0.3em] text-white/70"', 'class="brand"')
            .replaceAll('class="text-sm text-white/70"', 'class="label"')
            .replaceAll('class="mt-2 break-all font-mono text-4xl font-black tracking-wider"', 'class="code"')
            .replaceAll('class="mt-8 flex flex-wrap items-end justify-between gap-4"', 'class="row"')
            .replaceAll('class="mt-1 text-xl font-bold"', 'class="title"')
            .replaceAll('class="mt-1 font-mono text-sm text-white/80"', 'class="mono"')
            .replaceAll('class="mt-8"', 'style="margin-top:32px"');

        printWindow.document.open();
        printWindow.document.write(
            '<!doctype html><html lang="cs"><head><meta charset="UTF-8">' +
            '<title>Tisk zákaznické karty</title>' +
            '<style>' +
            '@page{size:A4 portrait;margin:10mm}' +
            'body{margin:0;font-family:Segoe UI,Tahoma,sans-serif;color:#fff}' +
            '.card{box-sizing:border-box;width:180mm;max-height:260mm;overflow:hidden;border-radius:18px;background:linear-gradient(135deg,#7a5b0f 0%,#d4af37 100%);padding:24px;color:#fff}' +
            '.brand{font-size:12px;letter-spacing:.3em;text-transform:uppercase;opacity:.75}' +
            '.label{font-size:14px;opacity:.75;margin:0}' +
            '.code{font-family:Consolas,monospace;font-size:38px;font-weight:900;letter-spacing:.05em;word-break:break-all;margin:8px 0 0}' +
            '.row{display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:16px;margin-top:32px}' +
            '.title{font-size:20px;font-weight:800;margin:4px 0 0}' +
            '.mono{font-family:Consolas,monospace;font-size:14px;opacity:.85;margin:4px 0 0}' +
            '@media print{body{margin:0}.card{break-inside:avoid;page-break-inside:avoid}}' +
            '</style></head><body>' +
            cardHtml +
            '<script>window.onload=function(){window.focus();window.print();window.close();};<\/script>' +
            '</body></html>'
        );
        printWindow.document.close();
    }
</script>
