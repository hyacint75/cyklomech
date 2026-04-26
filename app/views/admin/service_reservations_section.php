<section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <?php
    $calendarReservations = $serviceReservations;
    usort($calendarReservations, static function (array $left, array $right): int {
        $leftKey = sprintf(
            '%s %s %010d',
            (string) ($left['preferred_date'] ?? ''),
            (string) ($left['preferred_time'] ?? ''),
            (int) ($left['id'] ?? 0)
        );
        $rightKey = sprintf(
            '%s %s %010d',
            (string) ($right['preferred_date'] ?? ''),
            (string) ($right['preferred_time'] ?? ''),
            (int) ($right['id'] ?? 0)
        );

        return strcmp($leftKey, $rightKey);
    });

    $calendarDays = [];
    foreach ($calendarReservations as $reservation) {
        $dateKey = (string) ($reservation['preferred_date'] ?? '');
        if ($dateKey === '') {
            continue;
        }

        if (!isset($calendarDays[$dateKey])) {
            $calendarDays[$dateKey] = [];
        }

        $calendarDays[$dateKey][] = $reservation;
    }

    $statusStyles = [
        'nova' => 'border-sky-200 bg-sky-50 text-sky-900',
        'potvrzena' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
        'vyrizena' => 'border-slate-200 bg-slate-100 text-slate-800',
        'zrusena' => 'border-rose-200 bg-rose-50 text-rose-900',
    ];

    $calendarMonthSource = date('Y-m-01');
    if ($calendarReservations !== []) {
        $firstReservationDate = (string) ($calendarReservations[0]['preferred_date'] ?? '');
        if ($firstReservationDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $firstReservationDate) === 1) {
            $calendarMonthSource = substr($firstReservationDate, 0, 8) . '01';
        }
    }

    $monthTimestamp = strtotime($calendarMonthSource) ?: time();
    $monthStart = date('Y-m-01', $monthTimestamp);
    $monthLabel = date('F Y', $monthTimestamp);
    $monthDayCount = (int) date('t', $monthTimestamp);
    $monthStartWeekday = (int) date('N', strtotime($monthStart));
    $calendarCells = [];

    for ($blankIndex = 1; $blankIndex < $monthStartWeekday; $blankIndex++) {
        $calendarCells[] = null;
    }

    for ($dayNumber = 1; $dayNumber <= $monthDayCount; $dayNumber++) {
        $dateKey = date('Y-m-d', strtotime($monthStart . ' +' . ($dayNumber - 1) . ' days'));
        $calendarCells[] = [
            'dayNumber' => $dayNumber,
            'dateKey' => $dateKey,
            'reservations' => $calendarDays[$dateKey] ?? [],
            'isToday' => $dateKey === date('Y-m-d'),
        ];
    }

    while (count($calendarCells) % 7 !== 0) {
        $calendarCells[] = null;
    }

    $calendarWeeks = array_chunk($calendarCells, 7);
    $calendarWeekdayLabels = ['Po', 'Út', 'St', 'Čt', 'Pá', 'So', 'Ne'];
    ?>

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
            <h2 class="text-xl font-bold">Rezervace servisu</h2>
            <p class="mt-2 text-sm text-slate-600">Přehled všech přijatých rezervací servisu a jejich další zpracování.</p>
        </div>
        <a href="servis.php" target="_blank" rel="noopener noreferrer" class="btn-gradient rounded-lg px-4 py-2 text-sm font-semibold">Otevřít zákaznickou stránku</a>
    </div>

    <div class="mt-6 hidden lg:block">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold">Měsíční kalendář</h3>
                <p class="mt-1 text-sm text-slate-600">Klasický přehled rezervací po týdnech pro měsíc <strong><?php echo e($monthLabel); ?></strong>.</p>
            </div>
        </div>

        <div class="mt-4 overflow-x-auto">
            <div class="min-w-[980px] rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50">
                    <?php foreach ($calendarWeekdayLabels as $weekdayLabel): ?>
                        <div class="px-3 py-3 text-center text-sm font-semibold text-slate-600"><?php echo e($weekdayLabel); ?></div>
                    <?php endforeach; ?>
                </div>

                <?php foreach ($calendarWeeks as $week): ?>
                    <div class="grid grid-cols-7 border-b border-slate-200 last:border-b-0">
                        <?php foreach ($week as $cell): ?>
                            <?php if ($cell === null): ?>
                                <div class="min-h-44 border-r border-slate-200 bg-slate-50/70 last:border-r-0"></div>
                            <?php else: ?>
                                <div class="min-h-44 border-r border-slate-200 p-3 last:border-r-0 <?php echo $cell['isToday'] ? 'bg-amber-50/60' : 'bg-white'; ?>">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-sm font-semibold <?php echo $cell['isToday'] ? 'text-amber-900' : 'text-slate-800'; ?>"><?php echo (int) $cell['dayNumber']; ?></span>
                                        <?php if (count($cell['reservations']) > 0): ?>
                                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600"><?php echo count($cell['reservations']); ?>x</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mt-3 space-y-2">
                                        <?php foreach ($cell['reservations'] as $reservation): ?>
                                            <?php
                                            $statusValue = (string) ($reservation['status'] ?? 'nova');
                                            $statusLabel = service_booking_status_options()[$statusValue] ?? $statusValue;
                                            $statusClass = $statusStyles[$statusValue] ?? 'border-slate-200 bg-slate-50 text-slate-800';
                                            ?>
                                            <article class="rounded-lg border p-2 text-xs shadow-sm <?php echo $statusClass; ?>">
                                                <div class="flex items-start justify-between gap-2">
                                                    <span class="font-semibold"><?php echo e((string) $reservation['preferred_time']); ?></span>
                                                    <span class="rounded-full border border-current/20 px-1.5 py-0.5 text-[10px] font-semibold"><?php echo e($statusLabel); ?></span>
                                                </div>
                                                <p class="mt-1 font-semibold"><?php echo e((string) $reservation['customer_name']); ?></p>
                                                <p class="mt-1"><?php echo e((string) $reservation['bike_info']); ?></p>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="mt-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold">Kalendářový board</h3>
                <p class="mt-1 text-sm text-slate-600">Rezervace jsou seskupené podle dnů, ať je plán servisu vidět na první pohled.</p>
            </div>
        </div>

        <?php if (count($calendarDays) === 0): ?>
            <div class="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500">
                Zatím zde nejsou žádné rezervace pro zobrazení v kalendáři.
            </div>
        <?php else: ?>
            <div class="mt-4 grid gap-4 md:flex md:gap-4 md:overflow-x-auto md:pb-2">
                <?php foreach ($calendarDays as $dateKey => $dayReservations): ?>
                    <?php
                    $dateLabel = $dateKey;
                    $timestamp = strtotime($dateKey);
                    if ($timestamp !== false) {
                        $dateLabel = date('j. n. Y', $timestamp);
                    }
                    ?>
                    <section class="w-full rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm md:w-80 md:min-w-[20rem]">
                        <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-3">
                            <div>
                                <h4 class="font-semibold text-slate-900"><?php echo e($dateLabel); ?></h4>
                                <p class="text-xs text-slate-500"><?php echo count($dayReservations); ?> rezervací</p>
                            </div>
                        </div>

                        <div class="mt-4 space-y-3">
                            <?php foreach ($dayReservations as $reservation): ?>
                                <?php
                                $statusValue = (string) ($reservation['status'] ?? 'nova');
                                $statusLabel = service_booking_status_options()[$statusValue] ?? $statusValue;
                                $statusClass = $statusStyles[$statusValue] ?? 'border-slate-200 bg-white text-slate-800';
                                ?>
                                <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="font-semibold text-slate-900"><?php echo e((string) $reservation['customer_name']); ?></p>
                                            <p class="mt-1 text-sm text-slate-600"><?php echo e((string) $reservation['preferred_time']); ?></p>
                                        </div>
                                        <span class="rounded-full border px-2.5 py-1 text-xs font-semibold <?php echo $statusClass; ?>">
                                            <?php echo e($statusLabel); ?>
                                        </span>
                                    </div>

                                    <div class="mt-3 space-y-2 text-sm text-slate-700">
                                        <p><strong>Servis:</strong> <?php echo e((string) $reservation['service_type']); ?></p>
                                        <p><strong>Kolo / závada:</strong> <?php echo e((string) $reservation['bike_info']); ?></p>
                                        <p><strong>Telefon:</strong> <?php echo e((string) $reservation['phone']); ?></p>
                                        <?php if (trim((string) ($reservation['email'] ?? '')) !== ''): ?>
                                            <p><strong>E-mail:</strong> <?php echo e((string) $reservation['email']); ?></p>
                                        <?php endif; ?>
                                        <?php if (trim((string) ($reservation['note'] ?? '')) !== ''): ?>
                                            <p><strong>Poznámka:</strong> <?php echo e((string) $reservation['note']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-6 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="text-left text-slate-500">
                <tr class="border-b border-slate-200">
                    <th class="px-2 py-2">Zákazník</th>
                    <th class="px-2 py-2">Kontakt</th>
                    <th class="px-2 py-2">Termín</th>
                    <th class="px-2 py-2">Servis</th>
                    <th class="px-2 py-2">Kolo / závada</th>
                    <th class="px-2 py-2">Poznámka</th>
                    <th class="px-2 py-2">Stav</th>
                    <th class="px-2 py-2">Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($serviceReservations) === 0): ?>
                    <tr>
                        <td colspan="8" class="px-2 py-6 text-center text-slate-500">Zatím zde nejsou žádné rezervace servisu.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($serviceReservations as $reservation): ?>
                        <tr class="border-b border-slate-100 align-top">
                            <td class="px-2 py-2 font-medium"><?php echo e((string) $reservation['customer_name']); ?></td>
                            <td class="px-2 py-2">
                                <div><?php echo e((string) $reservation['phone']); ?></div>
                                <?php if (trim((string) ($reservation['email'] ?? '')) !== ''): ?>
                                    <div class="text-slate-500"><?php echo e((string) $reservation['email']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-2 py-2">
                                <div><?php echo e((string) $reservation['preferred_date']); ?></div>
                                <div class="text-slate-500"><?php echo e((string) $reservation['preferred_time']); ?></div>
                            </td>
                            <td class="px-2 py-2"><?php echo e((string) $reservation['service_type']); ?></td>
                            <td class="px-2 py-2"><?php echo e((string) $reservation['bike_info']); ?></td>
                            <td class="px-2 py-2"><?php echo e((string) ($reservation['note'] ?? '')); ?></td>
                            <td class="px-2 py-2">
                                <form method="post" class="flex gap-2">
                                    <input type="hidden" name="action" value="update_service_reservation_status">
                                    <input type="hidden" name="reservation_id" value="<?php echo (int) $reservation['id']; ?>">
                                    <select name="reservation_status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                        <?php foreach (service_booking_status_options() as $statusValue => $statusLabel): ?>
                                            <option value="<?php echo e($statusValue); ?>" <?php echo (string) $reservation['status'] === $statusValue ? 'selected' : ''; ?>><?php echo e($statusLabel); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn-gradient rounded-lg px-3 py-2 text-sm font-semibold">Uložit</button>
                                </form>
                            </td>
                            <td class="px-2 py-2">
                                <div class="flex flex-col gap-2">
                                    <form method="post">
                                        <input type="hidden" name="action" value="confirm_service_reservation">
                                        <input type="hidden" name="reservation_id" value="<?php echo (int) $reservation['id']; ?>">
                                        <button type="submit" class="btn-gradient w-full rounded-lg px-3 py-2 text-sm font-semibold">Potvrdit + e-mail</button>
                                    </form>
                                    <form method="post">
                                        <input type="hidden" name="action" value="reject_service_reservation">
                                        <input type="hidden" name="reservation_id" value="<?php echo (int) $reservation['id']; ?>">
                                        <button type="submit" class="btn-gradient w-full rounded-lg px-3 py-2 text-sm font-semibold">Odmítnout + e-mail</button>
                                    </form>
                                    <form method="post" onsubmit="return confirm('Opravdu chcete smazat tuto rezervaci?');">
                                        <input type="hidden" name="action" value="delete_service_reservation">
                                        <input type="hidden" name="reservation_id" value="<?php echo (int) $reservation['id']; ?>">
                                        <button type="submit" class="w-full rounded-lg border border-red-300 px-3 py-2 text-red-700 hover:bg-red-50">Smazat</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
