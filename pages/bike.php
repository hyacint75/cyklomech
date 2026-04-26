<?php
require __DIR__ . '/../app/bootstrap.php';

require APP_ROOT . '/app/db.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function imageSrc(string $path): string
{
    if ($path === '') {
        return '';
    }
    if (preg_match('#^(https?:)?//#i', $path) || str_starts_with($path, '/')) {
        return $path;
    }
    return '../' . ltrim($path, '/');
}

function specValue(mixed $value, string $fallback = 'Neuvedeno'): string
{
    if ($value === null) {
        return $fallback;
    }

    $text = trim((string) $value);
    return $text === '' ? $fallback : $text;
}

function hasSpecValue(mixed $value): bool
{
    return trim((string) ($value ?? '')) !== '';
}

function specOptions(mixed $value): array
{
    if ($value === null) {
        return [];
    }

    $text = trim((string) $value);
    if ($text === '') {
        return [];
    }

    $parts = preg_split('/\s*[,;\/|]\s*/u', $text) ?: [];
    $options = [];

    foreach ($parts as $part) {
        $option = trim($part);
        if ($option === '') {
            continue;
        }

        $options[] = $option;
    }

    return array_values(array_unique($options));
}

function isElectricBike(array $bike): bool
{
    $category = mb_strtolower(trim((string) ($bike['category'] ?? '')), 'UTF-8');
    return str_contains($category, 'elektro');
}

$bikeId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$bike = null;

if ($dbError === null) {
    if ($bikeId <= 0) {
        $dbError = 'Neplatné ID kola.';
    } else {
        $stmt = $mysqli->prepare('SELECT id, name, category, bike_type, manufacturer, price_czk, old_price_czk, is_new, description, image_url, color, frame_size, wheel_size, weight_kg, tires, lighting, brakes, bottom_bracket, front_hub, rear_hub, front_rotor, rear_rotor, front_derailleur, rear_derailleur, wheels, battery, motor, display_name, saddle, cassette, frame_spec, fork_spec, chain_spec, note, in_stock, created_at FROM bikes WHERE id = ? AND in_stock > 0 LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('i', $bikeId);
            $stmt->execute();
            $result = $stmt->get_result();
            $bike = $result ? $result->fetch_assoc() : null;
            $stmt->close();

            if (!$bike) {
                $dbError = 'Kolo s tímto ID nebylo nalezeno.';
            }
        } else {
            $dbError = 'Nepodařilo se připravit dotaz na detail kola.';
        }
    }
}

$showElectricSpecs = $bike !== null && isElectricBike($bike);
$frameSizeOptions = $bike !== null ? specOptions($bike['frame_size'] ?? null) : [];
$currentPrice = $bike !== null ? (float) ($bike['price_czk'] ?? 0) : 0.0;
$oldPrice = $bike !== null ? (float) ($bike['old_price_czk'] ?? 0) : 0.0;
$hasDiscount = $bike !== null && $oldPrice > $currentPrice && $currentPrice > 0;
$discountPercent = $hasDiscount ? (int) round((($oldPrice - $currentPrice) / $oldPrice) * 100) : 0;
$discountAmount = $hasDiscount ? ($oldPrice - $currentPrice) : 0.0;

$pageTitle = $bike ? ((string) $bike['name']) . ' | ' . APP_NAME : 'Detail kola | ' . APP_NAME;
$activePage = '';
$basePath = '../';
require APP_ROOT . '/app/layout/header.php';
?>

<main class="mx-auto max-w-[1680px] px-6 py-10">
    <div class="mb-6">
        <a href="../index.php" class="btn-gradient inline-flex rounded-lg px-4 py-2 text-sm font-semibold">Zpět na nabídku</a>
    </div>

    <?php if ($dbError !== null): ?>
        <section class="rounded-2xl border border-amber-300 bg-amber-50 p-6 text-amber-900">
            <h1 class="font-display text-2xl font-bold">Detail kola</h1>
            <p class="mt-3 text-sm"><?php echo e($dbError); ?></p>
        </section>
    <?php else: ?>
        <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <?php if (!empty($bike['image_url'])): ?>
                <button
                    type="button"
                    class="mb-6 block w-full overflow-hidden rounded-xl"
                    data-detail-lightbox-image="<?php echo e(imageSrc((string) $bike['image_url'])); ?>"
                    data-detail-lightbox-title="<?php echo e((string) $bike['name']); ?>"
                >
                    <img src="<?php echo e(imageSrc((string) $bike['image_url'])); ?>" alt="<?php echo e((string) $bike['name']); ?>" class="h-96 w-full rounded-xl bg-slate-50 object-contain p-2 transition hover:scale-[1.01]">
                </button>
            <?php else: ?>
                <div class="mb-6 flex h-96 w-full items-center justify-center rounded-xl bg-slate-100 text-sm text-slate-400">Bez obrázku</div>
            <?php endif; ?>

            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-sm uppercase tracking-widest text-slate-500">Detail kola</p>
                    <h1 class="mt-2 font-display text-3xl font-black"><?php echo e((string) $bike['name']); ?></h1>
                    <?php if ((int) ($bike['is_new'] ?? 0) === 1): ?>
                        <span class="mt-3 inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-800">Novinka</span>
                    <?php endif; ?>
                </div>
                <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-medium"><?php echo e((string) $bike['category']); ?></span>
            </div>

            <div class="mt-6">
                <?php if ($hasDiscount): ?>
                    <p class="text-lg font-semibold text-slate-400 line-through"><?php echo number_format($oldPrice, 0, ',', ' '); ?> Kč</p>
                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        <p class="text-4xl font-black text-rose-600"><?php echo number_format($currentPrice, 0, ',', ' '); ?> Kč</p>
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-700">Sleva <?php echo $discountPercent; ?> %</span>
                    </div>
                <?php else: ?>
                    <p class="text-4xl font-black text-forest"><?php echo number_format($currentPrice, 0, ',', ' '); ?> Kč</p>
                <?php endif; ?>
            </div>

            <p class="mt-4 text-base leading-relaxed text-slate-700"><?php echo nl2br(e((string) $bike['description'])); ?></p>

            <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Nová cena</p>
                    <p class="mt-1 text-sm font-semibold <?php echo $hasDiscount ? 'text-rose-600' : 'text-slate-800'; ?>"><?php echo number_format($currentPrice, 0, ',', ' '); ?> Kč</p>
                </div>
                <?php if ($hasDiscount): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Stará cena</p>
                        <p class="mt-1 text-sm font-semibold text-slate-400 line-through"><?php echo number_format($oldPrice, 0, ',', ' '); ?> Kč</p>
                    </div>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-emerald-700">Vypočítaná sleva</p>
                        <p class="mt-1 text-sm font-semibold text-emerald-700">-<?php echo $discountPercent; ?> % / <?php echo number_format($discountAmount, 0, ',', ' '); ?> Kč</p>
                    </div>
                <?php endif; ?>
                <?php if (hasSpecValue($bike['color'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Barva</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e(specValue($bike['color'] ?? null)); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (hasSpecValue($bike['manufacturer'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Výrobce</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e(specValue($bike['manufacturer'] ?? null)); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (hasSpecValue($bike['bike_type'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Druh kola</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e(mb_convert_case(specValue($bike['bike_type'] ?? null), MB_CASE_TITLE, 'UTF-8')); ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($frameSizeOptions !== []): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Velikost rámu</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <?php foreach ($frameSizeOptions as $frameSizeOption): ?>
                                <span class="rounded-full bg-white px-3 py-1 text-sm font-semibold text-slate-800 ring-1 ring-slate-200"><?php echo e($frameSizeOption); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (hasSpecValue($bike['wheel_size'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Velikost kol</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e(specValue($bike['wheel_size'] ?? null)); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (hasSpecValue($bike['weight_kg'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Hmotnost</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e((string) $bike['weight_kg']); ?> kg</p>
                    </div>
                <?php endif; ?>
                <?php if (hasSpecValue($bike['frame_spec'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Rám</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e(specValue($bike['frame_spec'] ?? null)); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (hasSpecValue($bike['fork_spec'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Vidlice</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e(specValue($bike['fork_spec'] ?? null)); ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($showElectricSpecs && hasSpecValue($bike['motor'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Motor</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e(specValue($bike['motor'] ?? null)); ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($showElectricSpecs && hasSpecValue($bike['battery'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Baterie</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e(specValue($bike['battery'] ?? null)); ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($showElectricSpecs && hasSpecValue($bike['display_name'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Display</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e(specValue($bike['display_name'] ?? null)); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (hasSpecValue($bike['front_derailleur'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Řazení vpředu</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e(specValue($bike['front_derailleur'] ?? null)); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (hasSpecValue($bike['rear_derailleur'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Řazení vzadu</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e(specValue($bike['rear_derailleur'] ?? null)); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (hasSpecValue($bike['cassette'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Kazeta</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e(specValue($bike['cassette'] ?? null)); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (hasSpecValue($bike['chain_spec'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Řetěz</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e(specValue($bike['chain_spec'] ?? null)); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (hasSpecValue($bike['bottom_bracket'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Střed</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e(specValue($bike['bottom_bracket'] ?? null)); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (hasSpecValue($bike['saddle'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Sedlo</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e(specValue($bike['saddle'] ?? null)); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (hasSpecValue($bike['brakes'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Brzdy</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e(specValue($bike['brakes'] ?? null)); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (hasSpecValue($bike['front_rotor'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Disk rotor přední</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e(specValue($bike['front_rotor'] ?? null)); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (hasSpecValue($bike['rear_rotor'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Disk rotor zadní</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e(specValue($bike['rear_rotor'] ?? null)); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (hasSpecValue($bike['wheels'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Kola</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e(specValue($bike['wheels'] ?? null)); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (hasSpecValue($bike['front_hub'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Střed kola přední</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e(specValue($bike['front_hub'] ?? null)); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (hasSpecValue($bike['rear_hub'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Střed kola zadní</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e(specValue($bike['rear_hub'] ?? null)); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (hasSpecValue($bike['tires'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Pneu</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e(specValue($bike['tires'] ?? null)); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (hasSpecValue($bike['lighting'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Osvětlení</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e(specValue($bike['lighting'] ?? null)); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (hasSpecValue($bike['note'] ?? null)): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Poznámka</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?php echo e(specValue($bike['note'] ?? null)); ?></p>
                    </div>
                <?php endif; ?>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Dostupnost</p>
                    <p class="mt-1 text-sm font-semibold <?php echo ((int) $bike['in_stock'] > 0) ? 'text-emerald-700' : 'text-red-600'; ?>">
                        <?php echo ((int) $bike['in_stock'] > 0) ? 'Skladem' : 'Na objednání'; ?>
                    </p>
                </div>
            </div>
        </article>
    <?php endif; ?>
</main>

<div id="detail-lightbox" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/85 p-4">
    <div class="relative w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl">
        <button id="detail-lightbox-close" type="button" class="absolute right-4 top-4 z-10 rounded-full bg-white/90 px-3 py-2 text-sm font-semibold text-slate-700 shadow hover:bg-white">Zavřít</button>
        <img id="detail-lightbox-image" src="" alt="" class="max-h-[80vh] w-full bg-slate-100 object-contain">
        <div class="border-t border-slate-100 bg-white p-5">
            <h2 id="detail-lightbox-title" class="font-display text-2xl font-black text-slate-900"></h2>
        </div>
    </div>
</div>

<script>
    (function () {
        var lightbox = document.getElementById('detail-lightbox');
        var closeButton = document.getElementById('detail-lightbox-close');
        var image = document.getElementById('detail-lightbox-image');
        var title = document.getElementById('detail-lightbox-title');
        var trigger = document.querySelector('[data-detail-lightbox-image]');

        if (!lightbox || !closeButton || !image || !title || !trigger) {
            return;
        }

        function closeLightbox() {
            lightbox.classList.add('hidden');
            lightbox.classList.remove('flex');
            image.src = '';
            image.alt = '';
            title.textContent = '';
        }

        trigger.addEventListener('click', function () {
            image.src = trigger.getAttribute('data-detail-lightbox-image') || '';
            image.alt = trigger.getAttribute('data-detail-lightbox-title') || '';
            title.textContent = trigger.getAttribute('data-detail-lightbox-title') || '';
            lightbox.classList.remove('hidden');
            lightbox.classList.add('flex');
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

<?php require APP_ROOT . '/app/layout/footer.php'; ?>
