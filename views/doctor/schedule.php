<?php ob_start(); ?>
<div class="page-header"><div><p class="eyebrow">Doctor workspace</p><h1>Schedule management</h1><p>Manage recurring availability for weekly appointments.</p></div><a class="button button-secondary" href="/doctor">Back to workspace</a></div>
<div class="card">
    <div class="card-header"><div><h2>Add availability</h2><p>Use clear start and end times. These hours guide appointment availability.</p></div></div>

    <form method="POST" action="/doctor/schedule" class="stacked-form">
        <?= csrf_field() ?>
        <input type="hidden" name="doctor_id" value="<?= e($doctor_id ?? 0) ?>">
        <div class="form-row">
            <div class="form-group"><label for="day_of_week">Day of week</label>
                <select name="day_of_week" required>
                    <option value="1">Monday</option>
                    <option value="2">Tuesday</option>
                    <option value="3">Wednesday</option>
                    <option value="4">Thursday</option>
                    <option value="5">Friday</option>
                    <option value="6">Saturday</option>
                    <option value="7">Sunday</option>
                </select></div>
            <div class="form-group"><label for="start_time">Start time</label><input id="start_time" type="time" name="start_time" required></div>
            <div class="form-group"><label for="end_time">End time</label><input id="end_time" type="time" name="end_time" required></div>
        </div>
        <button type="submit">Save schedule</button>
    </form>
</div>

<div class="card">
    <div class="card-header"><div><h2>Current schedule</h2><p>Recurring availability currently on file.</p></div></div>
    <?php if (empty($schedules)): ?>
        <p>No recurring schedules have been added yet.</p>
    <?php else: ?>
        <div class="table-wrap"><table class="table">
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schedules as $slot): ?>
                    <tr>
                        <td><?= e(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'][$slot['day_of_week'] - 1] ?? 'Unknown') ?></td>
                        <td><?= e($slot['start_time']) ?></td>
                        <td><?= e($slot['end_time']) ?></td>
                        <td><?= e((int) $slot['is_active'] === 1 ? 'Active' : 'Inactive') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table></div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>
