<?php
ob_start();
?>
<div class="page-header">
    <div>
        <p class="eyebrow">Your workspace</p>
        <h1>Welcome, <?= e($user['name'] ?? 'User') ?></h1>
        <p><?= $role === 'patient' ? 'Here is the information you need for your care today.' : 'Here is a clear view of your current responsibilities.' ?></p>
    </div>
    <?php if (($role ?? '') === 'patient'): ?>
        <div class="actions"><a class="button" href="/appointments/create">Book an appointment</a></div>
    <?php endif; ?>
</div>

<div class="stats-grid" aria-label="Dashboard summary">
    <?php foreach (($stats ?? []) as $label => $value): ?>
        <div class="card stat-card"><span class="stat-label"><?= e($label) ?></span><span class="stat-value"><?= e($value) ?></span></div>
    <?php endforeach; ?>
</div>

<?php if (($role ?? '') === 'patient'): ?>
    <div class="card">
        <div class="card-header"><div><h2>Upcoming appointments</h2><p>Your next scheduled visits appear here.</p></div><a href="/appointments">View all</a></div>
        <?php if (empty($upcomingAppointments)): ?>
            <div class="empty-state"><p>No upcoming appointments yet.</p><a class="button button-secondary" href="/appointments/create">Find an appointment time</a></div>
        <?php else: ?>
            <ul class="list-clean">
                <?php foreach ($upcomingAppointments as $appointment): ?>
                    <li class="list-item">
                        <strong><?= e($appointment->appointmentDate) ?></strong><br>
                        <span class="muted"><?= e($appointment->startTime) ?> - <?= e($appointment->endTime) ?></span>
                        <span class="badge badge-<?= e($appointment->status === 'confirmed' ? 'success' : ($appointment->status === 'cancelled' ? 'danger' : 'warning')) ?>"><?= e($appointment->status) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/app.php';
