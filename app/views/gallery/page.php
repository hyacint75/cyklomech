<main class="mx-auto max-w-[1680px] px-6 py-10 space-y-8">
    <section class="overflow-hidden rounded-3xl bg-forest-gradient px-6 py-14 text-white shadow-lg">
        <p class="text-sm uppercase tracking-[0.35em] text-limepop">Obrázkový katalog</p>
        <h1 class="mt-3 font-display text-4xl font-black md:text-5xl">Galerie kol k dispozici</h1>
        <p class="mt-4 max-w-2xl text-sm text-slate-100 md:text-base">Přehled kol jen přes fotografie. Otevři si detail modelu, který tě zaujme.</p>
    </section>

    <?php if ($dbError !== null): ?>
        <section class="rounded-2xl border border-amber-300 bg-amber-50 p-5 text-amber-900">
            <p class="font-semibold">Databázové upozornění</p>
            <p class="mt-2 text-sm"><?php echo htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8'); ?></p>
        </section>
    <?php elseif (count($galleryBikes) === 0): ?>
        <section class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-600 shadow-sm ring-1 ring-slate-200">
            <h2 class="font-display text-2xl font-bold text-slate-800">Zatím tu nejsou žádné fotografie</h2>
            <p class="mt-3 text-sm">Jakmile budou u kol nahrané obrázky, objeví se tady automaticky.</p>
        </section>
    <?php else: ?>
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($galleryBikes as $bike): ?>
                <button
                    type="button"
                    class="group overflow-hidden rounded-3xl bg-white text-left shadow-lg ring-1 ring-slate-200 transition hover:-translate-y-1"
                    data-gallery-item
                    data-gallery-image="<?php echo htmlspecialchars((string) $bike['image_url'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-gallery-title="<?php echo htmlspecialchars((string) $bike['name'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-gallery-category="<?php echo htmlspecialchars((string) $bike['category'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-gallery-detail="pages/bike.php?id=<?php echo (int) $bike['id']; ?>"
                >
                    <img src="<?php echo htmlspecialchars((string) $bike['image_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) $bike['name'], ENT_QUOTES, 'UTF-8'); ?>" class="h-80 w-full object-cover transition duration-500 group-hover:scale-105 group-hover:opacity-90">
                    <div class="border-t border-slate-100 bg-white p-5">
                        <p class="text-xs uppercase tracking-[0.25em] text-emerald-700"><?php echo htmlspecialchars((string) $bike['category'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <h2 class="mt-2 font-display text-2xl font-black text-slate-900"><?php echo htmlspecialchars((string) $bike['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p class="mt-2 text-sm text-slate-500">Otevřít náhled</p>
                    </div>
                </button>
            <?php endforeach; ?>
        </section>

        <div id="gallery-lightbox" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/80 p-4">
            <div class="relative w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl">
                <button id="gallery-lightbox-close" type="button" class="absolute right-4 top-4 z-10 rounded-full bg-white/90 px-3 py-2 text-sm font-semibold text-slate-700 shadow hover:bg-white">Zavřít</button>
                <img id="gallery-lightbox-image" src="" alt="" class="max-h-[75vh] w-full bg-slate-100 object-contain">
                <div class="border-t border-slate-100 bg-white p-5">
                    <p id="gallery-lightbox-category" class="text-xs uppercase tracking-[0.25em] text-emerald-700"></p>
                    <h2 id="gallery-lightbox-title" class="mt-2 font-display text-2xl font-black text-slate-900"></h2>
                    <a id="gallery-lightbox-link" href="#" class="btn-gradient mt-3 inline-flex rounded-lg px-4 py-2 text-sm font-semibold">Přejít na detail kola</a>
                </div>
            </div>
        </div>

        <script>
            (function () {
                var lightbox = document.getElementById('gallery-lightbox');
                var closeButton = document.getElementById('gallery-lightbox-close');
                var image = document.getElementById('gallery-lightbox-image');
                var title = document.getElementById('gallery-lightbox-title');
                var category = document.getElementById('gallery-lightbox-category');
                var link = document.getElementById('gallery-lightbox-link');
                var items = document.querySelectorAll('[data-gallery-item]');

                if (!lightbox || !closeButton || !image || !title || !category || !link || items.length === 0) {
                    return;
                }

                function closeLightbox() {
                    lightbox.classList.add('hidden');
                    lightbox.classList.remove('flex');
                    image.src = '';
                    image.alt = '';
                }

                items.forEach(function (item) {
                    item.addEventListener('click', function () {
                        image.src = item.getAttribute('data-gallery-image') || '';
                        image.alt = item.getAttribute('data-gallery-title') || '';
                        title.textContent = item.getAttribute('data-gallery-title') || '';
                        category.textContent = item.getAttribute('data-gallery-category') || '';
                        link.href = item.getAttribute('data-gallery-detail') || '#';
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
    <?php endif; ?>
</main>
