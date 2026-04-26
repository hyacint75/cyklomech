<?php
$normalizeSelection = static function ($value): array {
    if (is_array($value)) {
        $items = $value;
    } elseif ($value === null || $value === '') {
        $items = [];
    } else {
        $items = [$value];
    }

    $normalized = [];
    foreach ($items as $item) {
        $item = trim((string) $item);
        if ($item !== '') {
            $normalized[$item] = true;
        }
    }

    return array_keys($normalized);
};

$selectedWheelSizes = $normalizeSelection($_GET['wheel_size'] ?? []);
$selectedBikeTypes = $normalizeSelection($_GET['bike_type'] ?? []);
$selectedCategories = $normalizeSelection($_GET['category'] ?? []);

$wheelSizeOptions = [];
$bikeTypeOptions = [];
$categoryOptions = [];

foreach ($bikes as $bike) {
    $wheelSize = trim((string) ($bike['wheel_size'] ?? ''));
    if ($wheelSize !== '') {
        $wheelSizeOptions[$wheelSize] = true;
    }

    $bikeType = trim((string) ($bike['bike_type'] ?? ''));
    if ($bikeType !== '') {
        $bikeTypeOptions[$bikeType] = true;
    }

    $category = trim((string) ($bike['category'] ?? ''));
    if ($category !== '') {
        $categoryOptions[$category] = true;
    }
}

$wheelSizeOptions = array_keys($wheelSizeOptions);
natsort($wheelSizeOptions);
$wheelSizeOptions = array_values($wheelSizeOptions);

$bikeTypeOptions = array_keys($bikeTypeOptions);
natsort($bikeTypeOptions);
$bikeTypeOptions = array_values($bikeTypeOptions);

$categoryOptions = array_keys($categoryOptions);
natsort($categoryOptions);
$categoryOptions = array_values($categoryOptions);

$filteredBikes = array_values(array_filter(
    $bikes,
    static function (array $bike) use ($selectedWheelSizes, $selectedBikeTypes, $selectedCategories): bool {
        $wheelSize = trim((string) ($bike['wheel_size'] ?? ''));
        $bikeType = trim((string) ($bike['bike_type'] ?? ''));
        $category = trim((string) ($bike['category'] ?? ''));

        if ($selectedWheelSizes !== [] && !in_array($wheelSize, $selectedWheelSizes, true)) {
            return false;
        }

        if ($selectedBikeTypes !== [] && !in_array($bikeType, $selectedBikeTypes, true)) {
            return false;
        }

        if ($selectedCategories !== [] && !in_array($category, $selectedCategories, true)) {
            return false;
        }

        return true;
    }
));

$buildShowroomFilterUrl = static function (array $wheelSizes, array $bikeTypes, array $categories): string {
    $params = [];

    if ($wheelSizes !== []) {
        $params['wheel_size'] = array_values($wheelSizes);
    }

    if ($bikeTypes !== []) {
        $params['bike_type'] = array_values($bikeTypes);
    }

    if ($categories !== []) {
        $params['category'] = array_values($categories);
    }

    $query = http_build_query($params);

    return 'prodejna.php' . ($query !== '' ? '?' . $query : '') . '#nabidka';
};

$toggleValue = static function (array $selectedValues, string $value): array {
    if (in_array($value, $selectedValues, true)) {
        return array_values(array_filter(
            $selectedValues,
            static fn (string $selectedValue): bool => $selectedValue !== $value
        ));
    }

    $selectedValues[] = $value;

    return array_values(array_unique($selectedValues));
};

$countFilteredBikes = static function (
    array $bikes,
    array $wheelSizes,
    array $bikeTypes,
    array $categories
): int {
    return count(array_filter(
        $bikes,
        static function (array $bike) use ($wheelSizes, $bikeTypes, $categories): bool {
            $wheelSize = trim((string) ($bike['wheel_size'] ?? ''));
            $bikeType = trim((string) ($bike['bike_type'] ?? ''));
            $category = trim((string) ($bike['category'] ?? ''));

            if ($wheelSizes !== [] && !in_array($wheelSize, $wheelSizes, true)) {
                return false;
            }

            if ($bikeTypes !== [] && !in_array($bikeType, $bikeTypes, true)) {
                return false;
            }

            if ($categories !== [] && !in_array($category, $categories, true)) {
                return false;
            }

            return true;
        }
    ));
};

$hasActiveFilters = $selectedWheelSizes !== [] || $selectedBikeTypes !== [] || $selectedCategories !== [];

$featuredBikes = $filteredBikes;
$featuredBrands = array_slice($manufacturers, 0, 8);
$featuredCategories = array_slice($categories, 0, 6);
?>

<main class="mx-auto max-w-[1680px] px-6 py-10">
    <section class="showroom-hero overflow-hidden rounded-[2rem] border border-[#ead9a2] bg-white shadow-[0_24px_60px_rgba(122,91,15,0.08)]">
        <div>
            <div class="px-7 py-6 md:px-10 md:py-7">
                <p class="showroom-kicker">Katalog prodejny</p>
                <h1 class="mt-3 w-full font-display text-4xl font-black uppercase tracking-tight text-[#7a5b0f] md:text-5xl">
                    Kola, elektrokola v přímém, přehledném stylu.
                </h1>
                <p class="mt-5 max-w-2xl text-base leading-7 text-slate-700"></p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#nabidka" class="showroom-button">Zobrazit nabídku</a>
                    <!--<a href="servis.php" class="showroom-button showroom-button-secondary">Objednat servis</a>-->
                </div>
            </div>
        </div>
    </section>

    <section id="nabidka" class="mt-8 rounded-[2rem] border border-slate-200 bg-white p-7 shadow-sm">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="showroom-section-title">Nabídka</p>
                <p class="mt-2 text-sm text-slate-600">Přehled drží katalogový charakter, ale pořád navazuje na vaše data z obchodu.</p>
            </div>
            <a href="index.php" class="text-sm font-semibold text-[#7a5b0f] underline decoration-[#d4af37] underline-offset-4">Přejít na původní vyhledávání</a>
        </div>

        <?php if ($dbError !== null): ?>
            <div class="mt-5 rounded-2xl border border-amber-300 bg-amber-50 px-5 py-4 text-sm text-amber-900">
                Databázové upozornění: <?php echo e($dbError); ?>
            </div>
        <?php endif; ?>

        <?php if ($featuredBikes !== []): ?>
            <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
                <div class="grid gap-5">
                    <?php foreach ($featuredBikes as $bike): ?>
                        <?php
                        $price = (float) ($bike['price_czk'] ?? 0);
                        $oldPrice = (float) ($bike['old_price_czk'] ?? 0);
                        $hasDiscount = $oldPrice > $price && $price > 0;
                        $detailUrl = 'pages/bike.php?id=' . (int) $bike['id'];
                        ?>
                        <article class="showroom-card overflow-hidden">
                            <div class="overflow-hidden rounded-[1.35rem] border border-slate-200 bg-slate-50">
                                <?php if (!empty($bike['image_url'])): ?>
                                    <img
                                        src="<?php echo e((string) $bike['image_url']); ?>"
                                        alt="<?php echo e((string) $bike['name']); ?>"
                                        class="h-[500px] w-full object-contain bg-white p-2 md:h-[620px] md:p-3"
                                    >
                                <?php else: ?>
                                    <div class="flex min-h-[220px] items-center justify-center px-6 text-center text-sm text-slate-400">Obrázek bude doplněn.</div>
                                <?php endif; ?>
                            </div>
                            <div class="flex min-w-0 flex-col p-5 md:p-7">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-[#a07a17]">
                                            <?php echo e((string) ($bike['manufacturer'] ?? 'Katalog')); ?>
                                        </p>
                                        <h2 class="mt-2 text-2xl font-black leading-tight text-slate-900"><?php echo e((string) $bike['name']); ?></h2>
                                    </div>
                                    <span class="rounded-full border border-[#ead9a2] bg-[#fffbef] px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-[#7a5b0f]">
                                        <?php echo ((int) ($bike['in_stock'] ?? 0) > 0) ? 'Skladem' : 'Na objednání'; ?>
                                    </span>
                                </div>

                                <p class="mt-4 text-sm leading-6 text-slate-600"><?php echo e((string) $bike['description']); ?></p>

                                <div class="mt-5 grid gap-3 text-sm text-slate-700 sm:grid-cols-2">
                                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                        <span class="block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Kategorie</span>
                                        <span class="mt-2 block font-semibold"><?php echo e((string) ($bike['category'] ?? '-')); ?></span>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                        <span class="block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Velikost kol</span>
                                        <span class="mt-2 block font-semibold"><?php echo e((string) ($bike['wheel_size'] ?? '-')); ?></span>
                                    </div>
                                </div>

                                <div class="mt-5 flex flex-wrap items-end justify-between gap-4 border-t border-slate-200 pt-5">
                                    <div>
                                        <?php if ($hasDiscount): ?>
                                            <p class="text-sm font-medium text-slate-400 line-through"><?php echo number_format($oldPrice, 0, ',', ' '); ?> Kč</p>
                                            <p class="text-3xl font-black text-[#7a5b0f]"><?php echo number_format($price, 0, ',', ' '); ?> Kč</p>
                                        <?php else: ?>
                                            <p class="text-3xl font-black text-[#7a5b0f]"><?php echo number_format($price, 0, ',', ' '); ?> Kč</p>
                                        <?php endif; ?>
                                    </div>
                                    <a href="<?php echo e($detailUrl); ?>" class="showroom-button">Detail kola</a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <aside class="h-fit rounded-[1.75rem] border border-[#ead9a2] bg-[#fffbf1] p-6 shadow-sm xl:sticky xl:top-6">
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="showroom-section-title">Filtry nabídky</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Můžete kombinovat více velikostí kol i druhů kol najednou.</p>
                            </div>
                            <?php if ($selectedWheelSizes !== [] || $selectedBikeTypes !== [] || $selectedCategories !== []): ?>
                                <a href="prodejna.php#nabidka" class="text-sm font-semibold text-[#7a5b0f] underline decoration-[#d4af37] underline-offset-4">Vymazat</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-6">
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-[#a07a17]">Velikost kol</p>
                        <div class="mt-3 flex flex-col gap-2">
                            <?php foreach ($wheelSizeOptions as $wheelSizeOption): ?>
                                <?php
                                $isActiveWheelSize = in_array($wheelSizeOption, $selectedWheelSizes, true);
                                $nextWheelSizes = $toggleValue($selectedWheelSizes, $wheelSizeOption);
                                $wheelSizeUrl = $buildShowroomFilterUrl(
                                    $nextWheelSizes,
                                    $selectedBikeTypes,
                                    $selectedCategories
                                );
                                $wheelSizeCount = $countFilteredBikes(
                                    $bikes,
                                    [$wheelSizeOption],
                                    $selectedBikeTypes,
                                    $selectedCategories
                                );
                                ?>
                                <a href="<?php echo e($wheelSizeUrl); ?>" class="<?php echo $isActiveWheelSize ? 'border-[#d4af37] bg-[#fff4cc] text-[#7a5b0f]' : 'border-slate-200 bg-white text-slate-700 hover:border-[#ead9a2] hover:text-[#7a5b0f]'; ?> flex items-center justify-between gap-3 rounded-2xl border px-4 py-3 text-sm font-semibold transition">
                                    <span><?php echo e($wheelSizeOption); ?></span>
                                    <?php if ($wheelSizeCount > 0 && ($isActiveWheelSize || ($selectedWheelSizes === [] && $hasActiveFilters))): ?>
                                        <span class="text-xs font-bold text-slate-400"><?php echo $wheelSizeCount; ?></span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-[#ead9a2] pt-6">
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-[#a07a17]">Druh kola</p>
                        <div class="mt-3 flex flex-col gap-2">
                            <?php foreach ($bikeTypeOptions as $bikeTypeOption): ?>
                                <?php
                                $isActiveBikeType = in_array($bikeTypeOption, $selectedBikeTypes, true);
                                $nextBikeTypes = $toggleValue($selectedBikeTypes, $bikeTypeOption);
                                $bikeTypeUrl = $buildShowroomFilterUrl(
                                    $selectedWheelSizes,
                                    $nextBikeTypes,
                                    $selectedCategories
                                );
                                $bikeTypeCount = $countFilteredBikes(
                                    $bikes,
                                    $selectedWheelSizes,
                                    [$bikeTypeOption],
                                    $selectedCategories
                                );
                                ?>
                                <a href="<?php echo e($bikeTypeUrl); ?>" class="<?php echo $isActiveBikeType ? 'border-[#d4af37] bg-[#fff4cc] text-[#7a5b0f]' : 'border-slate-200 bg-white text-slate-700 hover:border-[#ead9a2] hover:text-[#7a5b0f]'; ?> flex items-center justify-between gap-3 rounded-2xl border px-4 py-3 text-sm font-semibold capitalize transition">
                                    <span><?php echo e(mb_convert_case($bikeTypeOption, MB_CASE_TITLE, 'UTF-8')); ?></span>
                                    <?php if ($bikeTypeCount > 0 && ($isActiveBikeType || ($selectedBikeTypes === [] && $hasActiveFilters))): ?>
                                        <span class="text-xs font-bold text-slate-400"><?php echo $bikeTypeCount; ?></span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-[#ead9a2] pt-6">
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-[#a07a17]">Kategorie</p>
                        <div class="mt-3 flex flex-col gap-2">
                            <?php foreach ($categoryOptions as $categoryOption): ?>
                                <?php
                                $isActiveCategory = in_array($categoryOption, $selectedCategories, true);
                                $nextCategories = $toggleValue($selectedCategories, $categoryOption);
                                $categoryUrl = $buildShowroomFilterUrl(
                                    $selectedWheelSizes,
                                    $selectedBikeTypes,
                                    $nextCategories
                                );
                                $categoryCount = $countFilteredBikes(
                                    $bikes,
                                    $selectedWheelSizes,
                                    $selectedBikeTypes,
                                    [$categoryOption]
                                );
                                ?>
                                <a href="<?php echo e($categoryUrl); ?>" class="<?php echo $isActiveCategory ? 'border-[#d4af37] bg-[#fff4cc] text-[#7a5b0f]' : 'border-slate-200 bg-white text-slate-700 hover:border-[#ead9a2] hover:text-[#7a5b0f]'; ?> flex items-center justify-between gap-3 rounded-2xl border px-4 py-3 text-sm font-semibold transition">
                                    <span><?php echo e($categoryOption); ?></span>
                                    <?php if ($categoryCount > 0 && ($isActiveCategory || ($selectedCategories === [] && $hasActiveFilters))): ?>
                                        <span class="text-xs font-bold text-slate-400"><?php echo $categoryCount; ?></span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </aside>
            </div>
        <?php else: ?>
            <div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-600">
                Pro zvolenou kombinaci filtrů jsme nenašli žádná kola.
            </div>
        <?php endif; ?>
    </section>
</main>
