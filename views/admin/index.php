<?php ob_start(); ?>
<div class="page-header">
    <div>
        <span class="workspace-topline">System operations</span>
        <h1>Administration</h1>
        <p>Welcome, <?= e($user['name'] ?? 'Administrator') ?>. Manage access and monitor appointment activity.</p>
    </div>
</div>

<div class="action-grid">
    <a class="action-card" href="/admin/users">
        <span class="action-icon" aria-hidden="true">U</span>
        <strong>User management</strong>
        <span>Create and review system accounts.</span>
    </a>
    <a class="action-card" href="/admin/reports">
        <span class="action-icon" aria-hidden="true">R</span>
        <strong>Appointment reports</strong>
        <span>Review operational totals and service activity.</span>
    </a>
    <a class="action-card" href="/notifications">
        <span class="action-icon" aria-hidden="true">N</span>
        <strong>Notifications</strong>
        <span>Review system updates available to your account.</span>
    </a>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h2>Administration principles</h2>
            <p>Use least privilege, preserve historical records, and review sensitive changes through the appropriate audit workflow.</p>
        </div>
        <span class="pilot-badge">Governance</span>
    </div>
    <ul class="admin-principles">
        <li>Maintain least-privilege access across patient, clinical, and operational roles.</li>
        <li>Preserve a complete record of booking, cancellation, and status-change activity.</li>
        <li>Review user lifecycle changes and service configuration with the same care as patient data.</li>
    </ul>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>
