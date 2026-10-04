<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f7f7f4">
    <meta name="description" content="A considered digital care experience for patients and clinical teams.">
    <title><?= e($title ?? 'Aurelia Clinic') ?> | Aurelia</title>
    <link rel="preload" href="/css/app.css" as="style">
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to main content</a>
    <div class="app-shell">
        <header class="topbar">
            <div class="container topbar-inner">
                <a class="brand-wrap" href="/" aria-label="Aurelia Clinic home">
                    <span class="brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 40 40" fill="none">
                            <path d="M20 8v24M8 20h24" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"/>
                            <circle cx="20" cy="20" r="17" stroke="currentColor" stroke-width="1.2" opacity=".38"/>
                        </svg>
                    </span>
                    <span class="brand-copy">
                        <span class="brand-name">AURELIA</span>
                        <span class="brand-subtitle">PRIVATE CLINIC</span>
                    </span>
                </a>

                <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="main-navigation">
                    <span class="nav-toggle-icon" aria-hidden="true"><span></span><span></span></span>
                    <span class="nav-toggle-label">Menu</span>
                </button>
                <nav class="nav-links" id="main-navigation" aria-label="Main navigation">
                    <?php $user = $user ?? null; ?>
                    <?php if ($user): ?>
                        <?php
                        $role = $user['role'] ?? 'patient';
                        $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
                        $accountPath = match ($role) {
                            'doctor' => '/doctor',
                            'receptionist' => '/receptionist',
                            'admin' => '/admin',
                            default => '/patient/profile',
                        };
                        ?>
                        <?php if ($role !== 'doctor'): ?>
                            <a href="/dashboard" <?= $currentPath === '/dashboard' ? 'aria-current="page"' : '' ?>>Dashboard</a>
                        <?php endif; ?>
                        <?php if ($role === 'patient'): ?>
                            <a href="/appointments" <?= str_starts_with((string) $currentPath, '/appointments') ? 'aria-current="page"' : '' ?>>Appointments</a>
                            <a href="/notifications" <?= $currentPath === '/notifications' ? 'aria-current="page"' : '' ?>>Notifications</a>
                            <a href="/patient/profile" <?= $currentPath === '/patient/profile' ? 'aria-current="page"' : '' ?>>Profile</a>
                            <a href="/patient/records" <?= $currentPath === '/patient/records' ? 'aria-current="page"' : '' ?>>Records</a>
                        <?php elseif ($role === 'doctor'): ?>
                            <a href="/doctor" <?= $currentPath === '/doctor' ? 'aria-current="page"' : '' ?>>Care workspace</a>
                            <a href="/doctor/schedule" <?= $currentPath === '/doctor/schedule' ? 'aria-current="page"' : '' ?>>Schedule</a>
                        <?php elseif ($role === 'receptionist'): ?>
                            <a href="/receptionist" <?= $currentPath === '/receptionist' ? 'aria-current="page"' : '' ?>>Reception</a>
                        <?php elseif ($role === 'admin'): ?>
                            <a href="/admin" <?= $currentPath === '/admin' ? 'aria-current="page"' : '' ?>>Administration</a>
                            <a href="/admin/users" <?= $currentPath === '/admin/users' ? 'aria-current="page"' : '' ?>>Users</a>
                            <a href="/admin/reports" <?= $currentPath === '/admin/reports' ? 'aria-current="page"' : '' ?>>Reports</a>
                        <?php endif; ?>
                        <a class="nav-account" href="<?= e($accountPath) ?>" aria-label="<?= e($user['name'] ?? 'Your account') ?> profile">
                            <span class="avatar" aria-hidden="true"><?= e(strtoupper(substr((string) ($user['name'] ?? 'U'), 0, 1))) ?></span>
                            <span class="nav-account-name"><?= e(explode(' ', (string) ($user['name'] ?? 'Account'))[0]) ?></span>
                        </a>
                        <form action="/logout" method="post" class="nav-logout-form">
                            <?= csrf_field() ?>
                            <button type="submit" class="nav-logout">Sign out</button>
                        </form>
                    <?php else: ?>
                        <a class="nav-anchor" href="/#approach">Our approach</a>
                        <a class="nav-anchor" href="/#care-teams">For care teams</a>
                        <a class="nav-login" href="/login">Patient portal <span aria-hidden="true">↗</span></a>
                    <?php endif; ?>
                </nav>
            </div>
        </header>

        <main class="container page-content <?= !$user ? 'page-content-public' : '' ?>" id="main-content">
            <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="status"><?= e($success) ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-error" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <?= $content ?? '' ?>
        </main>

        <?php if (!$user): ?>
            <footer class="site-footer">
                <div class="container footer-inner">
                    <a class="footer-brand" href="/" aria-label="Aurelia Clinic home">
                        <span class="brand-mark brand-mark-small" aria-hidden="true">+</span>
                        <span><strong>AURELIA</strong><small>PRIVATE CLINIC</small></span>
                    </a>
                    <p>Thoughtfully designed care, connected.</p>
                    <p class="footer-note">Portfolio demonstration · Fictional clinic · No real patient data</p>
                </div>
                <div class="container photo-credit">Photography via <a href="https://unsplash.com/license" target="_blank" rel="noopener noreferrer">Unsplash</a></div>
            </footer>
        <?php endif; ?>
    </div>

    <script src="/js/app.js" defer></script>
</body>
</html>
