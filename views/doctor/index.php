<?php ob_start(); ?>
<div class="page-header"><div><p class="eyebrow">Care team workspace</p><h1>Doctor workspace</h1><p>Welcome, <?= e($user['name'] ?? 'Doctor') ?>. Keep your schedule and care responsibilities in view.</p></div></div>
<div class="action-grid">
    <a class="action-card" href="/doctor/schedule"><strong>Manage schedule</strong><span>Set the recurring availability patients can book.</span></a>
    <a class="action-card" href="/doctor#assigned-appointments"><strong>Review appointments</strong><span>Approve requests and manage your assigned visits.</span></a>
    <a class="action-card" href="/notifications"><strong>View notifications</strong><span>Check appointment reminders and system updates.</span></a>
</div>
<div class="stats-grid">
    <div class="card stat-card"><span class="stat-label">Today's appointments</span><span class="stat-value"><?= e(count($todayAppointments ?? [])) ?></span></div>
    <div class="card stat-card"><span class="stat-label">Pending requests</span><span class="stat-value"><?= e($pendingCount ?? 0) ?></span></div>
    <div class="card stat-card"><span class="stat-label">Upcoming appointments</span><span class="stat-value"><?= e($upcomingCount ?? 0) ?></span></div>
</div>
<div class="card" id="assigned-appointments">
    <div class="card-header"><div><h2>Assigned appointments</h2><p>Review requests and update only appointments assigned to you.</p></div></div>
    <?php if (empty($appointments)): ?>
        <div class="empty-state"><p>No appointments are currently assigned to you.</p></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Date</th><th>Time</th><th>Patient</th><th>Service</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?= e($appointment['appointment_date']) ?></td>
                        <td><?= e($appointment['start_time']) ?> - <?= e($appointment['end_time']) ?></td>
                        <td><?= e($appointment['patient_name']) ?></td>
                        <td><?= e($appointment['service_name']) ?></td>
                        <td><span class="badge badge-<?= e($appointment['status'] === 'confirmed' ? 'success' : ($appointment['status'] === 'declined' || $appointment['status'] === 'cancelled' ? 'danger' : 'warning')) ?>"><?= e(str_replace('_', ' ', $appointment['status'])) ?></span></td>
                        <td>
                            <?php if ($appointment['status'] === 'pending'): ?>
                                <div class="actions">
                                    <form method="POST" action="/doctor/appointments/<?= e($appointment['id']) ?>/status">
                                        <?= csrf_field() ?><input type="hidden" name="status" value="confirmed"><button class="button-small" type="submit">Confirm Appointment</button>
                                    </form>
                                    <form method="POST" action="/doctor/appointments/<?= e($appointment['id']) ?>/status" data-confirm="Decline this appointment request?">
                                        <?= csrf_field() ?><input type="hidden" name="status" value="declined"><button class="button-small button-danger" type="submit">Decline Request</button>
                                    </form>
                                </div>
                            <?php elseif ($appointment['status'] === 'confirmed'): ?>
                                <form method="POST" action="/doctor/appointments/<?= e($appointment['id']) ?>/status">
                                    <?= csrf_field() ?><input type="hidden" name="status" value="checked_in"><button class="button-small" type="submit">Check In</button>
                                </form>
                            <?php elseif ($appointment['status'] === 'checked_in'): ?>
                                <form method="POST" action="/doctor/appointments/<?= e($appointment['id']) ?>/status">
                                    <?= csrf_field() ?><input type="hidden" name="status" value="in_progress"><button class="button-small" type="submit">Start Visit</button>
                                </form>
                            <?php elseif ($appointment['status'] === 'in_progress'): ?>
                                <form method="POST" action="/doctor/appointments/<?= e($appointment['id']) ?>/status">
                                    <?= csrf_field() ?><input type="hidden" name="status" value="completed"><button class="button-small" type="submit">Complete Visit</button>
                                </form>
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
