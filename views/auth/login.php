<?php
ob_start();
?>
<div class="login-wrap">
    <div class="card login-card">
        <div class="login-brand" aria-label="Aurelia Private Clinic">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 4v16M4 12h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.2" opacity=".45"/></svg>
            AURELIA <span aria-hidden="true">/</span> PATIENT PORTAL
        </div>
        <div class="login-header">
            <p class="eyebrow">Secure care access</p>
            <h1>Welcome back</h1>
            <p>Sign in to continue to your private care workspace.</p>
        </div>

        <form method="POST" action="/login">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" placeholder="you@example.com" autocomplete="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required>
            </div>
            <button type="submit">Continue securely <span aria-hidden="true">↗</span></button>
        </form>

        <div class="mt-3 demo-block">
            <p><strong>Demonstration portal</strong><br>This portfolio experience uses fabricated information only. Never enter real patient or health data.</p>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
