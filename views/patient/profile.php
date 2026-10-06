<?php ob_start(); ?>
<div class="page-header">
    <div>
        <p class="eyebrow">Your account</p>
        <h1>Profile</h1>
        <p>Review the personal information connected to your care account.</p>
    </div>
</div>

<div class="profile-overview">
    <div class="profile-identity">
        <div class="profile-avatar" aria-hidden="true"><?= e(strtoupper(substr((string) ($user['name'] ?? 'P'), 0, 1))) ?></div>
        <div>
            <h2><?= e($user['name'] ?? 'Patient') ?></h2>
            <p><?= e($user['email'] ?? '') ?></p>
        </div>
    </div>
    <div class="profile-panel">
        <h3>Care account</h3>
        <ul>
            <li>Access level: <?= e(ucfirst($user['role'] ?? 'patient')) ?></li>
            <li>Status: <span class="badge badge-success"><?= e(ucfirst($user['status'] ?? 'active')) ?></span></li>
        </ul>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h2>Personal information</h2>
            <p>Contact your clinic if any of these details need to be changed.</p>
        </div>
    </div>
    <dl class="info-grid">
        <div class="info-item"><dt>Full name</dt><dd><?= e($user['name'] ?? 'Patient') ?></dd></div>
        <div class="info-item"><dt>Email address</dt><dd><?= e($user['email'] ?? '') ?></dd></div>
        <div class="info-item"><dt>Account type</dt><dd><?= e(ucfirst($user['role'] ?? 'patient')) ?></dd></div>
        <div class="info-item"><dt>Account status</dt><dd><span class="badge badge-success"><?= e(ucfirst($user['status'] ?? 'active')) ?></span></dd></div>
    </dl>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>
