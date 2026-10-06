<?php ob_start(); ?>
<div class="page-header">
    <div>
        <p class="eyebrow">Your care history</p>
        <h1>Medical records</h1>
        <p>Review visit summaries and clinical information shared with your patient account.</p>
    </div>
</div>

<div class="card">
    <div class="notice-panel">
        <div class="notice-mark" aria-hidden="true">!</div>
        <div>
            <strong>Privacy reminder</strong>
            Keep this information private and sign out when using a shared device.
        </div>
    </div>
</div>

<div class="card">
    <?php if (empty($records)): ?>
        <div class="empty-state">
            <p>No medical records are available yet.</p>
            <span class="muted">Your care team will add visit information when it is ready to share.</span>
        </div>
    <?php else: ?>
        <ul class="record-list">
            <?php foreach ($records as $record): ?>
                <li class="record-item">
                    <strong><?= e($record->recordType ?? 'General record') ?></strong>
                    <p><?= e($record->summary ?? 'No summary available.') ?></p>
                    <span class="record-meta"><?= e($record->createdAt ?? 'Unknown date') ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>
