<?php ob_start(); ?>
<div class="page-header">
    <div>
        <p class="eyebrow">Patient appointments</p>
        <h1>Book an appointment</h1>
        <p>Choose a service, date, and available time. You can review the details before submitting.</p>
    </div>
</div>
<div class="card form-card">
    <div class="card-header">
        <div><h2>Appointment details</h2><p>All fields marked as required must be completed.</p></div>
        <span class="badge">Step 1 of 1</span>
    </div>
    <form method="POST" action="/appointments/store" data-booking-form>
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="doctor_id">Doctor</label>
            <select id="doctor_id" name="doctor_id" required data-booking-refresh>
                <?php foreach (($doctors ?? []) as $doctor): ?>
                    <option value="<?= e($doctor['id']) ?>" <?= (int) ($doctor_id ?? 1) === (int) $doctor['id'] ? 'selected' : '' ?>><?= e($doctor['name']) ?><?= !empty($doctor['specialty']) ? ' - ' . e($doctor['specialty']) : '' ?></option>
                <?php endforeach; ?>
            </select>
            <p class="help-text">Your care team will confirm the appointment details.</p>
        </div>

        <div class="form-group">
            <label for="service_id">Service</label>
            <select id="service_id" name="service_id" required data-booking-refresh>
                <?php foreach (($services ?? []) as $service): ?>
                    <option value="<?= e($service['id']) ?>" <?= (int) ($service_id ?? 1) === (int) $service['id'] ? 'selected' : '' ?>><?= e($service['name']) ?> (<?= e($service['duration_minutes']) ?> minutes)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label for="appointment_date">Date</label>
                    <input id="appointment_date" name="appointment_date" type="date" min="<?= e(date('Y-m-d')) ?>" value="<?= e($appointment_date ?? date('Y-m-d')) ?>" required data-booking-refresh>
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label for="start_time">Available time</label>
                    <select id="start_time" name="start_time" required data-end-time-target="end_time">
                        <?php if (empty($slots)): ?><option value="">No times available for this date</option><?php endif; ?>
                        <?php foreach (($slots ?? []) as $slot): ?>
                            <option value="<?= e($slot['start']) ?>" data-end-time="<?= e($slot['end']) ?>"><?= e($slot['start']) ?> - <?= e($slot['end']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" id="end_time" name="end_time" value="<?= e($slots[0]['end'] ?? '') ?>">
                </div>
            </div>
        </div>

        <?php if (empty($slots)): ?>
            <div class="alert alert-error" role="status">No appointment times are available for this doctor and date. Choose another date to continue.</div>
        <?php endif; ?>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="4" placeholder="Brief reason or notes"></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" <?= empty($slots) ? 'disabled' : '' ?>>Review and book appointment</button>
            <a class="button button-secondary" href="/appointments">Cancel</a>
        </div>
    </form>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>
