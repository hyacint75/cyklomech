<main class="mx-auto max-w-[1680px] space-y-8 px-6 py-10">
    <section class="relative overflow-hidden rounded-2xl px-6 py-8 text-white">
        <div class="absolute inset-0 bg-slate-950/50"></div>
        <div class="absolute inset-0 bg-forest-gradient opacity-95"></div>
        <div class="relative">
            <p class="text-limepop text-sm uppercase tracking-widest">Servis</p>
            <h1 class="mt-3 font-display text-4xl font-black">Rezervace servisu kola</h1>
            <p class="mt-4 max-w-2xl text-slate-100">Přístup do formuláře je chráněný ověřením výrobního čísla kola. Po přihlášení může zákazník odeslat požadovaný termín a popis servisu.</p>
        </div>
    </section>

    <?php if ($okMessage !== ''): ?>
        <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-sm text-emerald-900"><?php echo e($okMessage); ?></div>
    <?php endif; ?>

    <?php if ($dbError !== null): ?>
        <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900"><?php echo e($dbError); ?></div>
    <?php endif; ?>

    <section class="grid gap-6 lg:grid-cols-[0.95fr,1.05fr]">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="font-display text-2xl font-bold text-forest">Přístup do systému</h2>
            <p class="mt-2 text-sm text-slate-600">Pro vstup do formuláře zadejte výrobní číslo kola. Přístup bude povolen jen pro čísla uložená v administraci.</p>

            <?php if (count($accessErrors) > 0): ?>
                <div class="mt-4 rounded-xl border border-red-300 bg-red-50 p-4 text-sm text-red-900">
                    <ul class="list-inside list-disc space-y-1">
                        <?php foreach ($accessErrors as $error): ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!$isUnlocked): ?>
                <form method="post" class="mt-5 space-y-4">
                    <input type="hidden" name="action" value="service_access_login">
                    <div>
                        <label for="serial_number" class="mb-1 block text-sm font-medium text-slate-700">Výrobní číslo kola</label>
                        <input id="serial_number" name="serial_number" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" autocomplete="off">
                    </div>
                    <button type="submit" class="btn-gradient rounded-lg px-4 py-2 text-sm font-semibold">Ověřit výrobní číslo</button>
                </form>
            <?php else: ?>
                <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                    Přístup je aktivní. Formulář rezervace je odemknutý pro tuto relaci.
                </div>
                <form method="post" class="mt-4">
                    <input type="hidden" name="action" value="service_access_logout">
                    <button type="submit" class="btn-gradient rounded-lg px-4 py-2 text-sm font-semibold">Zamknout přístup</button>
                </form>
            <?php endif; ?>

            <div class="mt-6 rounded-xl bg-slate-50 p-4 text-sm text-slate-700 ring-1 ring-slate-200">
                <p class="font-semibold text-slate-900">Jak rezervace funguje</p>
                <ul class="mt-3 list-inside list-disc space-y-2">
                    <li>Zadáte preferovaný den a čas.</li>
                    <li>Do poznámky můžete připsat závadu nebo rozsah servisu.</li>
                    <li>V administraci pak rezervaci potvrdíte nebo změníte její stav.</li>
                </ul>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="font-display text-2xl font-bold text-forest">Formulář rezervace</h2>
            <p class="mt-2 text-sm text-slate-600">Vyplňte kontakt, termín a stručný popis kola nebo požadavku.</p>

            <?php if (!$isUnlocked): ?>
                <div class="mt-5 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-600">
                    Nejprve zadejte platné výrobní číslo kola.
                </div>
            <?php else: ?>
                <?php if (count($reservationErrors) > 0): ?>
                    <div class="mt-4 rounded-xl border border-red-300 bg-red-50 p-4 text-sm text-red-900">
                        <ul class="list-inside list-disc space-y-1">
                            <?php foreach ($reservationErrors as $error): ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" class="mt-5 grid gap-4 md:grid-cols-2">
                    <input type="hidden" name="action" value="create_service_reservation">
                    <div>
                        <label for="customer_name" class="mb-1 block text-sm font-medium text-slate-700">Jméno a příjmení</label>
                        <input id="customer_name" name="customer_name" type="text" value="<?php echo e($form['customer_name']); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    </div>
                    <div>
                        <label for="phone" class="mb-1 block text-sm font-medium text-slate-700">Telefon</label>
                        <input id="phone" name="phone" type="text" value="<?php echo e($form['phone']); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    </div>
                    <div>
                        <label for="email" class="mb-1 block text-sm font-medium text-slate-700">E-mail</label>
                        <input id="email" name="email" type="email" value="<?php echo e($form['email']); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    </div>
                    <div>
                        <label for="service_type" class="mb-1 block text-sm font-medium text-slate-700">Typ servisu</label>
                        <select id="service_type" name="service_type" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                            <?php foreach ($serviceTypes as $serviceType): ?>
                                <option value="<?php echo e($serviceType); ?>" <?php echo $form['service_type'] === $serviceType ? 'selected' : ''; ?>><?php echo e($serviceType); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="preferred_date" class="mb-1 block text-sm font-medium text-slate-700">Preferovaný den</label>
                        <input id="preferred_date" name="preferred_date" type="date" value="<?php echo e($form['preferred_date']); ?>" min="<?php echo e(date('Y-m-d')); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    </div>
                    <div>
                        <label for="preferred_time" class="mb-1 block text-sm font-medium text-slate-700">Preferovaný čas</label>
                        <input id="preferred_time" name="preferred_time" type="text" value="<?php echo e($form['preferred_time']); ?>" placeholder="např. 9:00-11:00" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    </div>
                    <div class="md:col-span-2">
                        <label for="bike_info" class="mb-1 block text-sm font-medium text-slate-700">Kolo nebo závada</label>
                        <input id="bike_info" name="bike_info" type="text" value="<?php echo e($form['bike_info']); ?>" placeholder="např. Apache Wakita, seřízení brzd a přehazovačky" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    </div>
                    <div class="md:col-span-2">
                        <label for="note" class="mb-1 block text-sm font-medium text-slate-700">Poznámka</label>
                        <textarea id="note" name="note" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2"><?php echo e($form['note']); ?></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="btn-gradient rounded-lg px-4 py-2 text-sm font-semibold">Odeslat rezervaci</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php if ($isUnlocked): ?>
    <script>
        (function () {
            var inactivityMs = <?php echo (int) (service_session_inactivity_timeout_seconds() * 1000); ?>;
            var inactivityTimer = null;

            function scheduleServiceAccessTimeout() {
                window.clearTimeout(inactivityTimer);
                inactivityTimer = window.setTimeout(function () {
                    window.location.href = 'servis.php?ok=access_expired';
                }, inactivityMs);
            }

            ['click', 'keydown', 'mousemove', 'scroll', 'touchstart', 'focus'].forEach(function (eventName) {
                document.addEventListener(eventName, scheduleServiceAccessTimeout, { passive: true });
            });

            scheduleServiceAccessTimeout();
        })();
    </script>
<?php endif; ?>
