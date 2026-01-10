<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="mb-4 fade-in-up">
    <h2 class="h3 mb-0 text-gradient"><i class="fas fa-cog me-2"></i> Settings</h2>
</div>

<div class="row g-4 fade-in-up">
    <div class="col-md-3">
        <div class="d-flex flex-column gap-3">
            <a href="<?php echo APP_URL; ?>/settings/index" class="btn-neu text-start text-secondary w-100">
                <i class="fas fa-building me-2"></i> Company Profile
            </a>
            <a href="<?php echo APP_URL; ?>/settings/users" class="btn-neu btn-neu-primary text-start w-100 text-white">
                <i class="fas fa-users me-2"></i> User Management
            </a>
            <a href="<?php echo APP_URL; ?>/roles/index" class="btn-neu text-start text-secondary w-100">
                <i class="fas fa-user-tag me-2"></i> Role Management
            </a>
            <a href="<?php echo APP_URL; ?>/settings/backup" class="btn-neu text-start text-success w-100">
                <i class="fas fa-database me-2"></i> Download Backup
            </a>
        </div>
    </div>

    <div class="col-md-9 fade-in-up" style="animation-delay: 0.1s;">
        <div class="neu-card mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-light pb-3">
                <h5 class="fw-bold text-secondary mb-0"><i class="fas fa-users me-2"></i> Users</h5>
                <button type="button" class="btn-neu btn-neu-sm btn-neu-primary" data-bs-toggle="modal"
                    data-bs-target="#addUserModal">
                    <i class="fas fa-plus me-1"></i> Add User
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-neu">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['users'] as $index => $u): ?>
                            <tr
                                style="animation: fadeInUp 0.5s ease-out forwards; animation-delay: <?php echo $index * 0.05; ?>s; opacity: 0;">
                                <td>
                                    <div class="fw-bold text-secondary"><?php echo htmlspecialchars($u->name); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($u->email); ?></td>
                                <td><span
                                        class="badge rounded-pill bg-light text-secondary shadow-sm px-3 py-2"><?php echo htmlspecialchars($u->role_name); ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($u->is_active): ?>
                                        <div class="d-inline-flex align-items-center text-success">
                                            <div class="online-indicator me-2" style="background-color: #2ecc71;"></div>
                                            Active
                                        </div>
                                    <?php else: ?>
                                        <div class="d-inline-flex align-items-center text-danger">
                                            <div class="online-indicator me-2" style="background-color: #e74c3c;"></div>
                                            Inactive
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content neu-card border-0 p-0">
            <form action="<?php echo APP_URL; ?>/settings/add_user" method="post">
                <div class="modal-header border-bottom border-light">
                    <h5 class="modal-title text-gradient">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label ms-1 text-secondary fw-bold small">Full Name</label>
                        <input type="text" name="name" class="form-control-neu" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label ms-1 text-secondary fw-bold small">Email Address</label>
                        <input type="email" name="email" class="form-control-neu" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label ms-1 text-secondary fw-bold small">Password</label>
                        <input type="password" name="password" class="form-control-neu" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label ms-1 text-secondary fw-bold small">Role</label>
                        <select name="role_id" class="form-control-neu" required style="appearance: auto;">
                            <?php foreach ($data['roles'] as $role): ?>
                                <option value="<?php echo $role->id; ?>"><?php echo htmlspecialchars($role->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4">
                    <button type="button" class="btn-neu text-danger" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn-neu btn-neu-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>