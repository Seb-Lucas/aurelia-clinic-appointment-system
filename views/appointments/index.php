<?php ob_start(); ?>
<div class="page-header">
    <div><p class="eyebrow">Patient care</p><h1>Appointments</h1><p>Review upcoming visits and manage your appointment history.</p></div>
    <div class="actions"><a class="button" href="/appointments/create">Book an appointment</a></div>
</div>
<div class="card">
    <?php if (empty($appointments)): ?>
        <div class="empty-state"><p>You do not have any appointments yet.</p><a class="button" href="/appointments/create">Find an appointment time</a></div>
    <?php else: ?>
    <div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Doctor</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($appointments)): ?>
                <tr><td colspan="5">No appointments booked yet.</td></tr>
            <?php else: ?>
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?= e($appointment->appointmentDate) ?></td>
                        <td><?= e($appointment->startTime) ?> - <?= e($appointment->endTime) ?></td>
                        <td><?= e($appointment->doctorId) ?></td>
                        <td><span class="badge"><?= e($appointment->status) ?></span></td>
                        <td>
                            <?php if (in_array($appointment->status, ['pending', 'confirmed'], true)): ?>
                                <form method="POST" action="/appointments/<?= e($appointment->id) ?>/cancel" data-confirm="Cancel this appointment? This action cannot be undone.">
                                    <?= csrf_field() ?>
                                        <button class="button-small button-danger" type="submit">Cancel</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>
