<?php ob_start(); ?>
<div class="page-header"><div><p class="eyebrow">System operations</p><h1>Administration</h1><p>Welcome, <?= e($user['name'] ?? 'Administrator') ?>. Manage access and monitor appointment activity.</p></div></div>
<div class="action-grid">
    <a class="action-card" href="/admin/users"><strong>User management</strong><span>Create and review system accounts.</span></a>
    <a class="action-card" href="/admin/reports"><strong>Appointment reports</strong><span>Review operational totals and service activity.</span></a>
    <a class="action-card" href="/notifications"><strong>Notifications</strong><span>Review system updates available to your account.</span></a>
</div>
<div class="card">
    <h2>Administration principles</h2>
    <p>Use least privilege, preserve historical records, and review sensitive changes through the appropriate audit workflow.</p>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>
