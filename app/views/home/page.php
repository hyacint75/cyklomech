<main class="mx-auto max-w-[1680px] space-y-10 px-6 py-10">
    <section class="relative overflow-hidden rounded-2xl px-6 py-7 text-white md:py-8">
        <div class="absolute inset-0 bg-slate-950/45"></div>
        <div class="absolute inset-0 bg-forest-gradient opacity-95"></div>
        <div class="absolute -right-16 -top-12 h-56 w-56 rounded-full bg-amber-300/20 blur-2xl"></div>
        <div class="relative grid gap-6 lg:grid-cols-[1.15fr_0.85fr] lg:items-start">
            <div>
                <p class="text-limepop text-sm uppercase tracking-widest"><?php echo APP_NAME; ?></p>
                <h1 class="mt-3 font-display text-4xl font-black md:text-5xl">Kde končí silnice, my nekončíme.</h1>
                <p class="mt-4 max-w-2xl text-slate-100">Servis, testovací jízdy a odborné poradenství. Osobní přístup a možnost rezervace přes telefon.</p>
                <div class="mt-8 flex flex-wrap gap-3 text-sm">
                    <span class="rounded-full bg-white/15 px-4 py-2">Tel: +420 723 186 464</span>
                </div>
            </div>
            <aside class="rounded-2xl border border-white/15 bg-white/10 p-5 backdrop-blur-sm md:p-6">
                <p class="text-sm font-bold uppercase tracking-[0.28em] text-white/70">Aktuální informace</p>
                <div class="mt-5 space-y-4 text-white">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/60">Pracovní doba</p>
                        <p class="mt-2 text-2xl font-black">Po-Pá 9:00-12:00 / 14:00-17:00</p>
                        <p class="mt-1 text-sm text-white/80">Sobota a neděle po dohodě nebo zavřeno.</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/60">Telefon</p>
                        <p class="mt-2 text-2xl font-black">723 186 464</p>
                    </div>
                    <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                        <p class="text-sm font-semibold">Web slouží jako katalog dostupných kol.</p>
                        <p class="mt-2 text-sm text-white/80">
                            Pro finální výběr, zkušební jízdu a převzetí kola počítáme s osobní návštěvou prodejny.
                        </p>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h2 class="font-display text-2xl font-bold">Aktuální nabídka kol</h2>

        <?php
        $hasActiveFilters = $searchQuery !== ''
            || $selectedCategory !== ''
            || $selectedBikeType !== ''
            || $selectedManufacturer !== ''
            || $selectedFrameSize !== ''
            || $priceMin !== ''
            || $priceMax !== ''
            || $selectedWheelSize !== ''
            || $selectedSort !== 'newest';
        $buildCategoryUrl = static function (string $categoryValue) use ($searchQuery, $selectedBikeType, $selectedManufacturer, $selectedFrameSize, $priceMin, $priceMax, $selectedWheelSize, $selectedSort): string {
            $params = [
                'q' => $searchQuery,
                'category' => $categoryValue,
                'bike_type' => $selectedBikeType,
                'manufacturer' => $selectedManufacturer,
                'frame_size' => $selectedFrameSize,
                'price_min' => $priceMin,
                'price_max' => $priceMax,
                'wheel_size' => $selectedWheelSize,
                'sort' => $selectedSort,
            ];

            $params = array_filter($params, static fn ($value): bool => $value !== '');

            return 'index.php' . ($params !== [] ? '?' . http_build_query($params) : '');
        };
        $buildPageUrl = static function (int $pageNumber) use ($searchQuery, $selectedCategory, $selectedBikeType, $selectedManufacturer, $selectedFrameSize, $priceMin, $priceMax, $selectedWheelSize, $selectedSort): string {
            $params = [
                'q' => $searchQuery,
                'category' => $selectedCategory,
                'bike_type' => $selectedBikeType,
                'manufacturer' => $selectedManufacturer,
                'frame_size' => $selectedFrameSize,
                'price_min' => $priceMin,
                'price_max' => $priceMax,
                'wheel_size' => $selectedWheelSize,
                'sort' => $selectedSort,
                'page' => $pageNumber,
            ];

            $params = array_filter($params, static fn ($value): bool => $value !== '' && $value !== 1);

            return 'index.php' . ($params !== [] ? '?' . http_build_query($params) : '');
        };
        $visiblePageNumbers = [];
        if ($totalPages <= 5) {
            $visiblePageNumbers = range(1, $totalPages);
        } else {
            $visiblePageNumbers = array_values(array_unique(array_filter([
                1,
                $currentPage - 1,
                $currentPage,
                $currentPage + 1,
                $totalPages,
            ], static fn (int $pageNumber): bool => $pageNumber >= 1 && $pageNumber <= $totalPages)));
            sort($visiblePageNumbers);
        }
        ?>
        <details class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4" <?php echo $hasActiveFilters ? 'open' : ''; ?>>
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm font-semibold text-slate-800">
                <span>Filtr kol</span>
                <span class="rounded-full bg-white px-3 py-1 text-xs text-slate-500 ring-1 ring-slate-200"><?php echo $hasActiveFilters ? 'Rozbaleno' : 'Rozbalit'; ?></span>
            </summary>

            <form method="get" class="mt-4 grid gap-3 md:grid-cols-4">
                <div class="md:col-span-2">
                    <label for="q" class="mb-1 block text-sm font-medium">Hledat</label>
                    <input id="q" name="q" type="text" value="<?php echo e($searchQuery); ?>" placeholder="Název nebo popis kola" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label for="category" class="mb-1 block text-sm font-medium">Kategorie</label>
                    <select id="category" name="category" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                        <option value="">Všechny</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo e($category); ?>" <?php echo $selectedCategory === $category ? 'selected' : ''; ?>><?php echo e($category); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="manufacturer" class="mb-1 block text-sm font-medium">Výrobce</label>
                    <select id="manufacturer" name="manufacturer" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                        <option value="">Všichni</option>
                        <?php foreach ($manufacturers as $manufacturerOption): ?>
                            <option value="<?php echo e($manufacturerOption); ?>" <?php echo $selectedManufacturer === $manufacturerOption ? 'selected' : ''; ?>><?php echo e($manufacturerOption); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="bike_type" class="mb-1 block text-sm font-medium">Druh kola</label>
                    <select id="bike_type" name="bike_type" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                        <option value="">Všechny</option>
                        <?php foreach ($bikeTypes as $bikeTypeOption): ?>
                            <option value="<?php echo e($bikeTypeOption); ?>" <?php echo $selectedBikeType === $bikeTypeOption ? 'selected' : ''; ?>><?php echo e(mb_convert_case($bikeTypeOption, MB_CASE_TITLE, 'UTF-8')); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="frame_size" class="mb-1 block text-sm font-medium">Velikost rámu</label>
                    <select id="frame_size" name="frame_size" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                        <option value="">Všechny</option>
                        <?php foreach ($frameSizes as $frameSizeOption): ?>
                            <option value="<?php echo e($frameSizeOption); ?>" <?php echo $selectedFrameSize === $frameSizeOption ? 'selected' : ''; ?>><?php echo e($frameSizeOption); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="price_min" class="mb-1 block text-sm font-medium">Cena od (Kč)</label>
                    <input id="price_min" name="price_min" type="number" min="0" step="1" value="<?php echo e($priceMin); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label for="price_max" class="mb-1 block text-sm font-medium">Cena do (Kč)</label>
                    <input id="price_max" name="price_max" type="number" min="0" step="1" value="<?php echo e($priceMax); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label for="wheel_size" class="mb-1 block text-sm font-medium">Velikost kol</label>
                    <select id="wheel_size" name="wheel_size" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                        <option value="">Všechny</option>
                        <?php foreach ($wheelSizes as $wheelSizeOption): ?>
                            <option value="<?php echo e($wheelSizeOption); ?>" <?php echo $selectedWheelSize === $wheelSizeOption ? 'selected' : ''; ?>><?php echo e($wheelSizeOption); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="sort" class="mb-1 block text-sm font-medium">Řazení</label>
                    <select id="sort" name="sort" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                        <option value="newest" <?php echo $selectedSort === 'newest' ? 'selected' : ''; ?>>Nejnovější</option>
                        <option value="price_asc" <?php echo $selectedSort === 'price_asc' ? 'selected' : ''; ?>>Nejlevnější</option>
                        <option value="price_desc" <?php echo $selectedSort === 'price_desc' ? 'selected' : ''; ?>>Nejdražší</option>
                        <option value="name_asc" <?php echo $selectedSort === 'name_asc' ? 'selected' : ''; ?>>Název A-Z</option>
                    </select>
                </div>
                <div class="flex flex-wrap gap-2 md:col-span-4">
                    <button type="submit" class="btn-gradient rounded-lg px-4 py-2 text-sm font-semibold">Filtrovat</button>
                    <a href="index.php" class="btn-gradient rounded-lg px-4 py-2 text-sm font-semibold">Reset</a>
                </div>
            </form>
        </details>

        <?php if ($dbError !== null): ?>
            <div class="mt-4 rounded-xl border border-amber-300 bg-amber-50 p-4 text-amber-900">
                <p class="font-semibold">Databázové upozornění</p>
                <p class="mt-1 text-sm"><?php echo e($dbError); ?></p>
            </div>
        <?php endif; ?>

        <?php if (count($bikes) > 0): ?>
            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm text-slate-600">
                <p>Zobrazeno <?php echo count($bikes); ?> z <?php echo (int) $totalItems; ?> kol</p>
                <?php if ($totalPages > 1): ?>
                    <p>Strana <?php echo (int) $currentPage; ?> z <?php echo (int) $totalPages; ?></p>
                <?php endif; ?>
            </div>
            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <?php foreach ($bikes as $bike): ?>
                    <?php
                    $price = (float) ($bike['price_czk'] ?? 0);
                    $oldPrice = (float) ($bike['old_price_czk'] ?? 0);
                    $hasDiscount = $oldPrice > $price && $price > 0;
                    $discountPercent = $hasDiscount ? (int) round((($oldPrice - $price) / $oldPrice) * 100) : 0;
                    ?>
                    <article class="rounded-xl border border-slate-200 p-5 transition-colors hover:border-amber-700">
                        <?php if (!empty($bike['image_url'])): ?>
                            <button
                                type="button"
                                class="mb-4 block w-full overflow-hidden rounded-lg"
                                data-lightbox-image="<?php echo e((string) $bike['image_url']); ?>"
                                data-lightbox-title="<?php echo e((string) $bike['name']); ?>"
                            >
                                <img src="<?php echo e((string) $bike['image_url']); ?>" alt="<?php echo e((string) $bike['name']); ?>" class="h-44 w-full rounded-lg object-cover transition hover:scale-[1.02]">
                            </button>
                        <?php else: ?>
                            <div class="mb-4 flex h-44 w-full items-center justify-center rounded-lg bg-slate-100 text-sm text-slate-400">Bez obrázku</div>
                        <?php endif; ?>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-semibold"><?php echo e((string) $bike['name']); ?></h3>
                                <?php if (trim((string) ($bike['manufacturer'] ?? '')) !== ''): ?>
                                    <p class="mt-1 text-sm text-slate-500">Výrobce: <?php echo e((string) $bike['manufacturer']); ?></p>
                                <?php endif; ?>
                                <?php if (trim((string) ($bike['bike_type'] ?? '')) !== ''): ?>
                                    <p class="mt-1 text-sm text-slate-500">Druh: <?php echo e(mb_convert_case((string) $bike['bike_type'], MB_CASE_TITLE, 'UTF-8')); ?></p>
                                <?php endif; ?>
                                <?php if ((int) ($bike['is_new'] ?? 0) === 1): ?>
                                    <span class="mt-2 inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-800">Novinka</span>
                                <?php endif; ?>
                            </div>
                            <a href="<?php echo e($buildCategoryUrl((string) $bike['category'])); ?>" class="rounded-full bg-slate-100 px-3 py-1 text-xs uppercase tracking-wide transition hover:bg-amber-50 hover:text-amber-900"><?php echo e((string) $bike['category']); ?></a>
                        </div>
                        <p class="mt-2 text-sm text-slate-600"><?php echo e((string) $bike['description']); ?></p>
                        <div class="mt-4 flex items-center justify-between gap-3">
                            <div>
                                <?php if ($hasDiscount): ?>
                                    <p class="text-sm font-medium text-slate-400 line-through"><?php echo number_format($oldPrice, 0, ',', ' '); ?> Kč</p>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-xl font-bold text-rose-600"><?php echo number_format($price, 0, ',', ' '); ?> Kč</p>
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">-<?php echo $discountPercent; ?> %</span>
                                    </div>
                                <?php else: ?>
                                    <p class="text-xl font-bold text-forest"><?php echo number_format($price, 0, ',', ' '); ?> Kč</p>
                                <?php endif; ?>
                            </div>
                            <span class="text-sm <?php echo ((int) $bike['in_stock'] > 0) ? 'text-amber-700' : 'text-red-600'; ?>">
                                <?php echo ((int) $bike['in_stock'] > 0) ? 'Skladem' : 'Na objednání'; ?>
                            </span>
                        </div>
                        <div class="mt-4">
                            <a href="pages/bike.php?id=<?php echo (int) $bike['id']; ?>" class="btn-gradient inline-flex rounded-lg px-4 py-2 text-sm font-semibold">Detail kola</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php if ($totalPages > 1): ?>
                <nav class="mt-8 flex flex-wrap items-center justify-center gap-2" aria-label="Stránkování kol">
                    <?php if ($currentPage > 1): ?>
                        <a href="<?php echo e($buildPageUrl($currentPage - 1)); ?>" class="btn-gradient rounded-lg px-4 py-2 text-sm font-medium">Předchozí</a>
                    <?php endif; ?>
                    <?php $previousVisiblePage = null; ?>
                    <?php foreach ($visiblePageNumbers as $pageNumber): ?>
                        <?php if ($previousVisiblePage !== null && ($pageNumber - $previousVisiblePage) > 1): ?>
                            <span class="px-2 py-2 text-sm text-slate-500">...</span>
                        <?php endif; ?>
                        <?php if ($pageNumber === $currentPage): ?>
                            <span class="rounded-lg border border-[#7a5b0f] bg-white px-4 py-2 text-sm font-semibold text-[#7a5b0f] shadow-sm"><?php echo $pageNumber; ?></span>
                        <?php else: ?>
                            <a href="<?php echo e($buildPageUrl($pageNumber)); ?>" class="btn-gradient rounded-lg px-4 py-2 text-sm font-medium"><?php echo $pageNumber; ?></a>
                        <?php endif; ?>
                        <?php $previousVisiblePage = $pageNumber; ?>
                    <?php endforeach; ?>
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="<?php echo e($buildPageUrl($currentPage + 1)); ?>" class="btn-gradient rounded-lg px-4 py-2 text-sm font-medium">Další</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-600">
                Zatím nejsou žádná kola k vypsání. Naplň tabulku <code>bikes</code> daty.
            </div>
        <?php endif; ?>
    </section>
</main>

<div id="image-lightbox" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/85 p-4">
    <div class="relative w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl">
        <button id="image-lightbox-close" type="button" class="absolute right-4 top-4 z-10 rounded-full bg-white/90 px-3 py-2 text-sm font-semibold text-slate-700 shadow hover:bg-white">Zavřít</button>
        <img id="image-lightbox-image" src="" alt="" class="max-h-[80vh] w-full bg-slate-100 object-contain">
        <div class="border-t border-slate-100 bg-white p-5">
            <h2 id="image-lightbox-title" class="font-display text-2xl font-black text-slate-900"></h2>
        </div>
    </div>
</div>

<?php if (count($importantNotices) > 0): ?>
    <div id="notice-modal" data-notice-key="<?php echo e($importantNoticesKey); ?>" data-force-show="<?php echo $forceShowNotice ? '1' : '0'; ?>" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
        <div class="w-full max-w-lg rounded-2xl border border-amber-300 bg-white p-6 shadow-2xl">
            <p class="text-xs font-semibold uppercase tracking-widest text-amber-700">Důležitá informace</p>
            <ul class="mt-3 list-inside list-disc space-y-2 text-sm font-medium text-slate-800">
                <?php foreach ($importantNotices as $noticeText): ?>
                    <li><?php echo e($noticeText); ?></li>
                <?php endforeach; ?>
            </ul>
            <div class="mt-5 flex justify-end">
                <button id="notice-close" type="button" class="btn-gradient rounded-lg px-4 py-2 text-sm font-semibold">Zavřít</button>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    (function () {
        var closeButton = document.getElementById('notice-close');
        var modal = document.getElementById('notice-modal');
        if (!closeButton || !modal) return;

        var noticeKey = modal.getAttribute('data-notice-key') || '';
        var forceShow = modal.getAttribute('data-force-show') === '1';
        var storageKey = noticeKey ? 'cyklomech_notice_seen_session_v3_' + noticeKey : '';

        if (!forceShow && storageKey) {
            try {
                if (window.sessionStorage.getItem(storageKey) === '1') {
                    modal.classList.add('hidden');
                    return;
                }
            } catch (error) {
            }
        }

        function rememberDismissedNotice() {
            if (!storageKey) return;
            try {
                window.sessionStorage.setItem(storageKey, '1');
            } catch (error) {
            }
        }

        closeButton.addEventListener('click', function () {
            rememberDismissedNotice();
            modal.classList.add('hidden');
        });

        modal.addEventListener('click', function (event) {
            if (event.target !== modal) return;
            rememberDismissedNotice();
            modal.classList.add('hidden');
        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape' || modal.classList.contains('hidden')) return;
            rememberDismissedNotice();
            modal.classList.add('hidden');
        });
    })();

    (function () {
        var lightbox = document.getElementById('image-lightbox');
        var closeButton = document.getElementById('image-lightbox-close');
        var image = document.getElementById('image-lightbox-image');
        var title = document.getElementById('image-lightbox-title');
        var triggers = document.querySelectorAll('[data-lightbox-image]');

        if (!lightbox || !closeButton || !image || !title || triggers.length === 0) {
            return;
        }

        function closeLightbox() {
            lightbox.classList.add('hidden');
            lightbox.classList.remove('flex');
            image.src = '';
            image.alt = '';
            title.textContent = '';
        }

        triggers.forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                image.src = trigger.getAttribute('data-lightbox-image') || '';
                image.alt = trigger.getAttribute('data-lightbox-title') || '';
                title.textContent = trigger.getAttribute('data-lightbox-title') || '';
                lightbox.classList.remove('hidden');
                lightbox.classList.add('flex');
            });
        });

        closeButton.addEventListener('click', closeLightbox);
        lightbox.addEventListener('click', function (event) {
            if (event.target === lightbox) {
                closeLightbox();
            }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !lightbox.classList.contains('hidden')) {
                closeLightbox();
            }
        });
    })();
</script>
