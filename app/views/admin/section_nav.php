<nav class="flex flex-wrap items-center gap-3">
    <a href="admin.php" class="rounded-lg border px-4 py-2 text-sm font-medium <?php echo $adminSection === 'bikes' ? 'border-amber-200 bg-amber-50 text-amber-900' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'; ?>">Admin kol</a>
    <a href="service_admin.php" class="rounded-lg border px-4 py-2 text-sm font-medium <?php echo $adminSection === 'service' ? 'border-amber-200 bg-amber-50 text-amber-900' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'; ?>">Admin servisu</a>
    <a href="service_work.php" class="rounded-lg border px-4 py-2 text-sm font-medium <?php echo $adminSection === 'service_work' ? 'border-amber-200 bg-amber-50 text-amber-900' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'; ?>">Servisní práce</a>
</nav>
