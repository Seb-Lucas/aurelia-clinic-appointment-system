<?php ob_start(); ?>
<div class="page-header"><div><p class="eyebrow">System operations</p><h1>Appointment reports</h1><p>Operational summary for appointments, service utilization, and provider workload.</p></div><a class="button button-secondary" href="/admin">Back to administration</a></div>

<div class="row">
    <div class="col">
        <div class="card stat-card">
            <span class="stat-label">Total appointments</span>
            <span class="stat-value"><?= e($report['total'] ?? 0) ?></span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col">
        <div class="card"><h2>By status</h2><ul class="list-clean">
                <?php foreach (($report['by_status'] ?? []) as $entry): ?>
                    <li class="list-item"><span class="badge"><?= e($entry['status']) ?></span> <strong><?= e($entry['total']) ?></strong></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="col">
        <div class="card"><h2>By doctor</h2><ul class="list-clean">
                <?php foreach (($report['by_doctor'] ?? []) as $entry): ?>
                    <li class="list-item">Doctor #<?= e($entry['doctor_id']) ?> <strong><?= e($entry['total']) ?></strong></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="col">
        <div class="card"><h2>By service</h2><ul class="list-clean">
                <?php foreach (($report['by_service'] ?? []) as $entry): ?>
                    <li class="list-item">Service #<?= e($entry['service_id']) ?> <strong><?= e($entry['total']) ?></strong></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>
