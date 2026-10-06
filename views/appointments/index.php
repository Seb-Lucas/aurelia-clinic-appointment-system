<?php ob_start(); ?>
<div class="page-header">
    <div>
        <p class="eyebrow">Patient care</p>
        <h1>Appointments</h1>
        <p>Review upcoming visits and manage your appointment history.</p>
    </div>
    <div class="actions"><a class="button" href="/appointments/create">Book an appointment</a></div>
</div>
<div class="card">
    <?php if (empty($appointments)): ?>
        <div class="empty-state">
            <p>You do not have any appointments yet.</p>
            <a class="button" href="/appointments/create">Find an appointment time</a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table app-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Visit</th>
                        <th>Doctor</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <?php
                        $status = strtolower((string) ($appointment['status'] ?? 'pending'));
                        $statusClass = match ($status) {
                            'confirmed', 'checked_in', 'completed' => 'badge-success',
                            'pending', 'requested' => 'badge-warning',
                            'cancelled', 'declined' => 'badge-danger',
                            default => 'badge-warning',
                        };
                        $dateValue = $appointment['appointment_date'] ?? '';
                        $dateDisplay = $dateValue !== '' ? date('M j, Y', strtotime($dateValue)) : '—';
                        $startValue = $appointment['start_time'] ?? '';
                        $endValue = $appointment['end_time'] ?? '';
                        $timeDisplay = $startValue !== '' && $endValue !== '' ? date('g:i A', strtotime($startValue)) . ' – ' . date('g:i A', strtotime($endValue)) : 'Time to be confirmed';
                        ?>
                        <tr>
                            <td>
                                <div class="appointment-date"><?= e($dateDisplay) ?></div>
                            </td>
                            <td>
                                <div class="appointment-visit"><?= e($appointment['service_name'] ?? 'Consultation') ?></div>
                                <div class="table-meta"><?= e($timeDisplay) ?></div>
                            </td>
                            <td>
                                <div class="appointment-doctor"><?= e($appointment['doctor_name'] ?? 'Care team') ?></div>
                            </td>
                            <td><span class="badge <?= e($statusClass) ?>"><?= e(ucfirst($status)) ?></span></td>
                            <td>
                                <?php if (in_array($status, ['pending', 'confirmed', 'requested'], true)): ?>
                                    <form method="POST" action="/appointments/<?= e($appointment['id']) ?>/cancel" data-confirm="Cancel this appointment? This action cannot be undone.">
                                        <?= csrf_field() ?>
                                        <button class="button-small button-danger" type="submit">Cancel</button>
                                    </form>
                                <?php else: ?>
                                    <span class="table-meta">No action</span>
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
