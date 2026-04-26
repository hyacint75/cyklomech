<section class="lg:col-span-3 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <h2 class="text-xl font-bold">Seznam kol</h2>
    <?php if (count($adminBikes) === 0): ?>
        <p class="mt-4 text-sm text-slate-600">Zatím zde nejsou žádná kola.</p>
    <?php else: ?>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-center text-slate-500">
                    <tr class="border-b border-slate-200">
                        <th class="px-2 py-2">Název</th>
                        <th class="px-2 py-2">Obrázek</th>
                        <th class="px-2 py-2">Kategorie</th>
                        <th class="px-2 py-2">Druh</th>
                        <th class="px-2 py-2">Velikost rámu</th>
                        <th class="px-2 py-2">Poznámka</th>
                        <th class="px-2 py-2">Cena</th>
                        <th class="px-2 py-2">Stav</th>
                        <th class="px-2 py-2">Akce</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($adminBikes as $bike): ?>
                        <tr class="border-b border-slate-100">
                            <td class="px-2 py-2 text-center font-medium align-middle"><?php echo e((string) $bike['name']); ?></td>
                            <td class="px-2 py-2 text-center align-middle"><?php if (!empty($bike['image_url'])): ?><img src="<?php echo e((string) $bike['image_url']); ?>" alt="Náhled kola" class="h-12 w-16 rounded object-cover"><?php else: ?><span class="text-xs text-slate-400">Bez obrázku</span><?php endif; ?></td>
                            <td class="px-2 py-2 text-center align-middle"><?php echo e((string) $bike['category']); ?></td>
                            <td class="px-2 py-2 text-center align-middle"><?php echo e(mb_convert_case((string) ($bike['bike_type'] ?? 'uni'), MB_CASE_TITLE, 'UTF-8')); ?></td>
                            <td class="px-2 py-2 text-center align-middle"><?php echo e((string) ($bike['frame_size'] ?? '')); ?></td>
                            <td class="px-2 py-2 text-center align-middle"><?php echo e((string) ($bike['note'] ?? '')); ?></td>
                            <td class="px-2 py-2 text-center align-middle"><?php echo number_format((float) $bike['price_czk'], 0, ',', ' '); ?> Kč</td>
                            <td class="px-2 py-2 text-center align-middle">
                                <?php if ((int) ($bike['in_stock'] ?? 0) > 0): ?>
                                    <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">Skladem</span>
                                <?php else: ?>
                                    <span class="rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-700">Není skladem</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-2 py-2 text-center align-middle">
                                <div class="flex flex-wrap justify-center gap-2">
                                    <a href="admin.php?edit=<?php echo (int) $bike['id']; ?>" title="Upravit" aria-label="Upravit" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-300 hover:bg-slate-50"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-700" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 010 2.828l-8.5 8.5a1 1 0 01-.39.242l-3 1a1 1 0 01-1.264-1.264l1-3a1 1 0 01.242-.39l8.5-8.5a2 2 0 012.828 0z" /></svg></a>
                                    <form method="post"><input type="hidden" name="action" value="duplicate"><input type="hidden" name="id" value="<?php echo (int) $bike['id']; ?>"><button type="submit" title="Kopírovat" aria-label="Kopírovat" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-amber-300 text-amber-800 hover:bg-amber-50"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M6 2a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2V4a2 2 0 00-2-2H6z" /><path d="M4 6a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-1h-2v1H4V8h1V6H4z" /></svg></button></form>
                                    <form method="post" onsubmit="return confirm('Opravdu chcete smazat toto kolo?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo (int) $bike['id']; ?>"><button type="submit" title="Smazat" aria-label="Smazat" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-red-300 text-red-700 hover:bg-red-50"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 2a1 1 0 00-1 1v1H5a1 1 0 100 2h10a1 1 0 100-2h-2V3a1 1 0 00-1-1H8zM6 7a1 1 0 011 1v7a1 1 0 102 0V8a1 1 0 112 0v7a1 1 0 102 0V8a1 1 0 112 0v7a3 3 0 11-6 0V8a1 1 0 10-2 0v7a3 3 0 11-6 0V8a1 1 0 011-1z" clip-rule="evenodd" /></svg></button></form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
