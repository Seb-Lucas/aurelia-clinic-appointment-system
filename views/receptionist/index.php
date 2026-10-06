<?php ob_start(); ?>
<div class="page-header">
    <div>
        <span class="workspace-topline">Front desk workspace</span>
        <h1>Reception workspace</h1>
        <p>Welcome, <?= e($user['name'] ?? 'Receptionist') ?>. Coordinate arrivals and the daily patient flow.</p>
    </div>
</div>

<div class="action-grid">
    <a class="action-card" href="/notifications">
        <span class="action-icon" aria-hidden="true">N</span>
        <strong>Notifications</strong>
        <span>Check operational updates and reminders.</span>
    </a>
    <div class="action-card-static">
        <span class="action-icon" aria-hidden="true">P</span>
        <strong>Patient lookup</strong>
        <span>Confirm identity from the appointment queue before sharing operational details.</span>
    </div>
    <div class="action-card-static">
        <span class="action-icon" aria-hidden="true">C</span>
        <strong>Clinical privacy</strong>
        <span>Clinical notes, diagnoses, prescriptions, and medical records are restricted.</span>
    </div>
</div>

<div class="stats-grid">
    <div class="card stat-card"><span class="stat-label">Queue date</span><span class="stat-value"><?= e($queueDate ?? date('Y-m-d')) ?></span></div>
    <div class="card stat-card"><span class="stat-label">Pending arrivals</span><span class="stat-value"><?= e($pendingCount ?? 0) ?></span></div>
    <div class="card stat-card"><span class="stat-label">In waiting room</span><span class="stat-value"><?= e($waitingCount ?? 0) ?></span></div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h2>Today's appointment queue</h2>
            <p>Operational details only. No clinical information is shown here.</p>
        </div>
        <form method="GET" action="/receptionist" class="actions">
            <label class="sr-only" for="queue-date">Queue date</label>
            <input id="queue-date" name="date" type="date" value="<?= e($queueDate ?? date('Y-m-d')) ?>">
            <button class="button-small" type="submit">View date</button>
        </form>
    </div>
    <?php if (empty($appointments)): ?>
        <div class="empty-state"><p>No appointments are scheduled for this date.</p></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Time</th><th>Patient</th><th>Doctor</th><th>Service</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?= e($appointment['start_time']) ?> - <?= e($appointment['end_time']) ?></td>
                        <td><?= e($appointment['patient_name']) ?></td>
                        <td><?= e($appointment['doctor_name']) ?></td>
                        <td><?= e($appointment['service_name']) ?></td>
                        <td><span class="badge badge-<?= e($appointment['status'] === 'checked_in' ? 'success' : ($appointment['status'] === 'cancelled' || $appointment['status'] === 'declined' ? 'danger' : 'warning')) ?>"><?= e(str_replace('_', ' ', $appointment['status'])) ?></span></td>
                        <td>
                            <?php if (in_array($appointment['status'], ['pending', 'confirmed'], true)): ?>
                                <form method="POST" action="/receptionist/appointments/<?= e($appointment['id']) ?>/check-in">
                                    <?= csrf_field() ?><button class="button-small" type="submit">Mark Arrived</button>
                                </form>
                            <?php elseif ($appointment['status'] === 'checked_in'): ?>
                                <span class="muted">In waiting room</span>
                            <?php else: ?>
                                <span class="muted">No action</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>
