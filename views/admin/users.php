<?php ob_start(); ?>
<div class="page-header"><div><p class="eyebrow">System operations</p><h1>User management</h1><p>Create development accounts and review existing access.</p></div><a class="button button-secondary" href="/admin">Back to administration</a></div>
<div class="card">
    <div class="card-header"><div><h2>Create a user</h2><p>Use a role that matches the person’s responsibilities.</p></div></div>

    <form method="POST" action="/admin/users" class="stacked-form">
        <?= csrf_field() ?>
        <div class="form-row">
            <label>
                Full name
                <input type="text" name="name" required>
            </label>
            <label>
                Email
                <input type="email" name="email" required>
            </label>
        </div>
        <div class="form-row">
            <label>
                Password
                <input type="password" name="password" minlength="8" required>
            </label>
            <label>
                Role
                <select name="role">
                    <option value="patient">Patient</option>
                    <option value="doctor">Doctor</option>
                    <option value="receptionist">Receptionist</option>
                    <option value="admin">Admin</option>
                </select>
            </label>
        </div>
        <button type="submit">Create user</button>
    </form>
</div>

<div class="card">
    <div class="card-header"><div><h2>Existing users</h2><p>Review account names, roles, and status.</p></div></div>
    <?php if (empty($users)): ?>
        <p>No users found.</p>
    <?php else: ?>
        <div class="table-wrap"><table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $account): ?>
                    <tr>
                        <td><?= e($account->name) ?></td>
                        <td><?= e($account->email) ?></td>
                        <td><?= e($account->role) ?></td>
                        <td><?= e($account->status) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table></div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>
