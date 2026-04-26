<section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <h2 class="text-xl font-bold"><?php echo $isEditMode ? 'Upravit kolo' : 'Přidat nové kolo'; ?></h2>

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

    <form method="post" enctype="multipart/form-data" class="mt-5 space-y-4" data-bike-admin-form>
        <input type="hidden" name="action" value="<?php echo $isEditMode ? 'update' : 'create'; ?>">
        <input type="hidden" name="id" value="<?php echo (int) ($form['id'] ?? 0); ?>">
        <input type="hidden" name="existing_image_url" value="<?php echo e((string) $form['image_url']); ?>">
        <input type="hidden" name="redirect_to" value="<?php echo e($isEditMode ? 'admin.php?edit=' . (int) ($form['id'] ?? 0) : 'admin.php?new=1'); ?>">

        <div>
            <label for="name" class="mb-1 block text-sm font-medium">Název kola</label>
            <input id="name" name="name" type="text" required class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e((string) $form['name']); ?>">
        </div>

        <div>
            <label for="category" class="mb-1 block text-sm font-medium">Kategorie</label>
            <input id="category" name="category" type="text" required class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e((string) $form['category']); ?>">
        </div>

        <div>
            <label for="bike_type" class="mb-1 block text-sm font-medium">Druh kola</label>
            <select id="bike_type" name="bike_type" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                <?php foreach (admin_bike_type_options() as $bikeTypeOption): ?>
                    <option value="<?php echo e($bikeTypeOption); ?>" <?php echo (($form['bike_type'] ?? 'uni') === $bikeTypeOption) ? 'selected' : ''; ?>><?php echo e(mb_convert_case($bikeTypeOption, MB_CASE_TITLE, 'UTF-8')); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="manufacturer" class="mb-1 block text-sm font-medium">Výrobce</label>
            <input id="manufacturer" name="manufacturer" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e((string) ($form['manufacturer'] ?? '')); ?>">
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="price_czk" class="mb-1 block text-sm font-medium">Nová cena (Kč)</label>
                <input id="price_czk" name="price_czk" type="number" step="0.01" min="0" required class="w-full rounded-lg border border-slate-300 px-3 py-2 transition-colors" value="<?php echo e((string) $form['price_czk']); ?>" data-highlight-required>
            </div>
            <div>
                <label for="old_price_czk" class="mb-1 block text-sm font-medium">Původní cena (Kč)</label>
                <input id="old_price_czk" name="old_price_czk" type="number" step="0.01" min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e((string) ($form['old_price_czk'] ?? '')); ?>">
                <p class="mt-1 text-xs text-slate-500">Volitelné. Pokud je vyšší než nová cena, zobrazí se sleva.</p>
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_new" value="1" <?php echo ((int) ($form['is_new'] ?? 0) === 1) ? 'checked' : ''; ?>>
            Označit jako novinka
        </label>

        <div>
            <label for="description" class="mb-1 block text-sm font-medium">Popis</label>
            <textarea id="description" name="description" required rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2"><?php echo e((string) $form['description']); ?></textarea>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div><label for="color" class="mb-1 block text-sm font-medium">Barva</label><input id="color" name="color" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 transition-colors" value="<?php echo e((string) $form['color']); ?>" data-highlight-required></div>
            <div>
                <label for="frame_size" class="mb-1 block text-sm font-medium">Velikost rámu</label>
                <input id="frame_size" name="frame_size" type="text" list="frame-size-options" class="w-full rounded-lg border border-slate-300 px-3 py-2 transition-colors" value="<?php echo e((string) $form['frame_size']); ?>" data-highlight-required>
                <datalist id="frame-size-options">
                    <?php foreach (admin_frame_size_presets() as $preset): ?>
                        <option value="<?php echo e($preset); ?>"></option>
                    <?php endforeach; ?>
                </datalist>
                <p class="mt-1 text-xs text-slate-500">Více velikostí zadej jako `S, M, L, XL`.</p>
            </div>
            <div>
                <label for="wheel_size" class="mb-1 block text-sm font-medium">Velikost kol</label>
                <input id="wheel_size" name="wheel_size" type="text" list="wheel-size-options" class="w-full rounded-lg border border-slate-300 px-3 py-2 transition-colors" value="<?php echo e((string) $form['wheel_size']); ?>" data-highlight-required>
                <datalist id="wheel-size-options">
                    <?php foreach (admin_wheel_size_presets() as $preset): ?>
                        <option value="<?php echo e($preset); ?>"></option>
                    <?php endforeach; ?>
                </datalist>
                <p class="mt-1 text-xs text-slate-500">Pokud je více variant, odděl je čárkou.</p>
            </div>
            <div><label for="weight_kg" class="mb-1 block text-sm font-medium">Hmotnost (kg, volitelné)</label><input id="weight_kg" name="weight_kg" type="number" step="0.01" min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 transition-colors" value="<?php echo e((string) $form['weight_kg']); ?>" data-highlight-required></div>
            <div><label for="frame_spec" class="mb-1 block text-sm font-medium">Rám</label><input id="frame_spec" name="frame_spec" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e((string) $form['frame_spec']); ?>"></div>
            <div><label for="fork_spec" class="mb-1 block text-sm font-medium">Vidlice</label><input id="fork_spec" name="fork_spec" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e((string) $form['fork_spec']); ?>"></div>
            <div><label for="motor" class="mb-1 block text-sm font-medium">Motor</label><input id="motor" name="motor" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e((string) $form['motor']); ?>"></div>
            <div><label for="battery" class="mb-1 block text-sm font-medium">Baterie</label><input id="battery" name="battery" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e((string) $form['battery']); ?>"></div>
            <div><label for="display_name" class="mb-1 block text-sm font-medium">Display</label><input id="display_name" name="display_name" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e((string) $form['display_name']); ?>"></div>
            <div><label for="front_derailleur" class="mb-1 block text-sm font-medium">Řazení vpředu</label><input id="front_derailleur" name="front_derailleur" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e((string) $form['front_derailleur']); ?>"></div>
            <div><label for="rear_derailleur" class="mb-1 block text-sm font-medium">Řazení vzadu</label><input id="rear_derailleur" name="rear_derailleur" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e((string) $form['rear_derailleur']); ?>"></div>
            <div><label for="cassette" class="mb-1 block text-sm font-medium">Kazeta</label><input id="cassette" name="cassette" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e((string) $form['cassette']); ?>"></div>
            <div><label for="chain_spec" class="mb-1 block text-sm font-medium">Řetěz</label><input id="chain_spec" name="chain_spec" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e((string) $form['chain_spec']); ?>"></div>
            <div><label for="bottom_bracket" class="mb-1 block text-sm font-medium">Střed</label><input id="bottom_bracket" name="bottom_bracket" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e((string) $form['bottom_bracket']); ?>"></div>
            <div><label for="saddle" class="mb-1 block text-sm font-medium">Sedlo</label><input id="saddle" name="saddle" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e((string) $form['saddle']); ?>"></div>
            <div><label for="brakes" class="mb-1 block text-sm font-medium">Brzdy</label><input id="brakes" name="brakes" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e((string) $form['brakes']); ?>"></div>
            <div><label for="front_rotor" class="mb-1 block text-sm font-medium">Disk rotor přední</label><input id="front_rotor" name="front_rotor" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e((string) $form['front_rotor']); ?>"></div>
            <div><label for="rear_rotor" class="mb-1 block text-sm font-medium">Disk rotor zadní</label><input id="rear_rotor" name="rear_rotor" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e((string) $form['rear_rotor']); ?>"></div>
            <div><label for="wheels" class="mb-1 block text-sm font-medium">Kola</label><input id="wheels" name="wheels" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e((string) $form['wheels']); ?>"></div>
            <div><label for="front_hub" class="mb-1 block text-sm font-medium">Střed kola přední</label><input id="front_hub" name="front_hub" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e((string) $form['front_hub']); ?>"></div>
            <div><label for="rear_hub" class="mb-1 block text-sm font-medium">Střed kola zadní</label><input id="rear_hub" name="rear_hub" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e((string) $form['rear_hub']); ?>"></div>
            <div><label for="tires" class="mb-1 block text-sm font-medium">Pneu</label><input id="tires" name="tires" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e((string) $form['tires']); ?>"></div>
            <div><label for="lighting" class="mb-1 block text-sm font-medium">Osvětlení</label><input id="lighting" name="lighting" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e((string) $form['lighting']); ?>"></div>
        </div>
        <div><label for="note" class="mb-1 block text-sm font-medium">Poznámka</label><input id="note" name="note" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" value="<?php echo e((string) $form['note']); ?>"></div>

        <div>
            <label for="image_file" class="mb-1 block text-sm font-medium">Obrázek kola (soubor)</label>
            <input id="image_file" name="image_file" type="file" accept=".jpg,.jpeg,.png,.webp,.gif,image/*" class="w-full rounded-lg border border-slate-300 px-3 py-2">
            <p class="mt-1 text-xs text-slate-500">Povolené formáty: JPG, PNG, WEBP, GIF. Maximálně 5 MB.</p>
            <?php if (!empty($form['image_url'])): ?>
                <div class="mt-2 flex items-center gap-3">
                    <img src="<?php echo e((string) $form['image_url']); ?>" alt="Aktuální obrázek kola" class="h-16 w-24 rounded object-cover">
                    <div class="space-y-1">
                        <p class="text-xs text-slate-500">Aktuální obrázek se ponechá, pokud nenahraješ nový.</p>
                        <label class="flex items-center gap-2 text-xs text-slate-600"><input type="checkbox" name="remove_image" value="1">Odstranit aktuální obrázek</label>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <label class="flex items-center gap-2 rounded-lg border border-transparent px-3 py-2 text-sm transition-colors" data-highlight-required-checkbox>
            <input type="checkbox" name="in_stock" value="1" <?php echo ((int) $form['in_stock'] === 1) ? 'checked' : ''; ?> data-highlight-required>
            Skladem
        </label>
        <div class="flex flex-wrap gap-2">
            <button type="submit" class="btn-gradient rounded-lg px-4 py-2 text-sm font-semibold"><?php echo $isEditMode ? 'Uložit změny' : 'Přidat kolo'; ?></button>
            <?php if ($isEditMode): ?>
                <a href="admin.php" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Zrušit úpravu</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<script>
    (function () {
        var form = document.querySelector('[data-bike-admin-form]');

        if (!form) {
            return;
        }

        function hasValue(field) {
            if (field.type === 'checkbox') {
                return field.checked;
            }

            return field.value.trim() !== '';
        }

        function updateFieldState(field) {
            var filled = hasValue(field);

            if (field.type === 'checkbox') {
                var wrapper = field.closest('[data-highlight-required-checkbox]');

                if (!wrapper) {
                    return;
                }

                wrapper.classList.toggle('border-rose-300', !filled);
                wrapper.classList.toggle('bg-rose-50', !filled);
                wrapper.classList.toggle('border-transparent', filled);
                wrapper.classList.toggle('bg-transparent', filled);
                return;
            }

            field.classList.toggle('border-rose-300', !filled);
            field.classList.toggle('bg-rose-50', !filled);
            field.classList.toggle('border-slate-300', filled);
            field.classList.toggle('bg-white', filled);
        }

        var watchedFields = form.querySelectorAll('[data-highlight-required]');

        watchedFields.forEach(function (field) {
            updateFieldState(field);
            field.addEventListener(field.type === 'checkbox' ? 'change' : 'input', function () {
                updateFieldState(field);
            });
        });
    })();
</script>
