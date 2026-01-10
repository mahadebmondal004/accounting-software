<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 fade-in-up">
    <h2 class="h3 mb-0 text-gradient"><i class="fas fa-user-tag me-2"></i> Roles & Permissions</h2>
    <a href="<?php echo APP_URL; ?>/roles/create" class="btn-neu btn-neu-primary">
        <i class="fas fa-plus me-2"></i> Create New Role
    </a>
</div>

<div class="card shadow-none bg-transparent">
    <div class="card-body p-0">
        <div class="neu-card">
            <div class="table-responsive">
                <table class="table table-neu">
                    <thead>
                        <tr>
                            <th>Role Name</th>
                            <th>Permissions</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['roles'] as $index => $role): ?>
                            <tr
                                style="animation: fadeInUp 0.5s ease-out forwards; animation-delay: <?php echo $index * 0.05; ?>s; opacity: 0;">
                                <td>
                                    <div class="fw-bold text-secondary"><?php echo htmlspecialchars($role->name); ?></div>
                                </td>
                                <td>
                                    <?php
                                    $perms = json_decode($role->permissions, true);
                                    if ($perms) {
                                        if (isset($perms['all']) && $perms['all']) {
                                            echo '<span class="badge rounded-pill bg-success text-white px-3 py-2 shadow-sm">All Access</span>';
                                        } else {
                                            foreach ($perms as $key => $val) {
                                                if ($val) {
                                                    echo '<span class="badge rounded-pill bg-light text-secondary border border-light me-1 mb-1">' . str_replace('_', ' ', ucwords($key)) . '</span>';
                                                }
                                            }
                                        }
                                    } else {
                                        echo '<span class="text-muted small">No specific permissions</span>';
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-2">
                                        <a href="<?php echo APP_URL; ?>/roles/edit/<?php echo $role->id; ?>"
                                            class="btn-neu btn-neu-sm text-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($role->id != 1): // Prevent deleting Super Admin ?>
                                            <form action="<?php echo APP_URL; ?>/roles/delete/<?php echo $role->id; ?>"
                                                method="post" class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this role?');">
                                                <input type="hidden" name="csrf_token"
                                                    value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <button type="submit" class="btn-neu btn-neu-sm text-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn-neu btn-neu-sm text-muted" disabled
                                                title="Cannot delete Super Admin">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>