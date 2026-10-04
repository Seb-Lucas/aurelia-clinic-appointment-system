<?php ob_start(); ?>
<div class="page-header"><div><p class="eyebrow">Stay informed</p><h1>Notifications</h1><p>Appointment reminders, updates, and messages from your care team appear here.</p></div></div>
<div class="card">
    <?php if (empty($notifications)): ?>
        <div class="empty-state"><p>You are all caught up.</p><span class="muted">New updates will appear here when they are available.</span></div>
    <?php else: ?>
        <ul class="list-clean">
            <?php foreach ($notifications as $notification): ?>
                <li class="list-item"><span class="badge"><?= e(ucfirst($notification->type)) ?></span><p><?= e($notification->message) ?></p><span class="muted"><?= e($notification->createdAt ?? 'Recently') ?></span></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>
