<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 fade-in-up" style="position: relative; z-index: 50;">
    <h2 class="h3 mb-0 text-gradient"><i class="fas fa-sitemap me-2"></i> Account Groups</h2>
    <a href="<?php echo APP_URL; ?>/account_groups/create" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus me-2"></i> New Group
    </a>
</div>

<div class="card shadow-sm border-0 fade-in-up">
    <div class="card-body p-0">
        <?php if (empty($data['groups'])): ?>
            <div class="text-center py-5">
                <i class="fas fa-sitemap fa-3x text-secondary mb-3 opacity-50"></i>
                <h5 class="text-secondary">No account groups found</h5>
                <p class="text-muted">Create your first account group.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Group Name</th>
                            <th>Code</th>
                            <th>Nature</th>
                            <th>Parent Group</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $currentNature = '';
                        foreach ($data['groups'] as $index => $group):
                            if ($currentNature != $group->nature):
                                $currentNature = $group->nature;
                                ?>
                                <tr class="table-secondary">
                                    <td colspan="5" class="fw-bold text-uppercase small ps-4">
                                        <?php echo $group->nature; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <tr
                                style="animation: fadeInUp 0.5s ease-out forwards; animation-delay: <?php echo $index * 0.05; ?>s; opacity: 0;">
                                <td class="ps-4">
                                    <div class="fw-bold text-secondary">
                                        <?php echo $group->parent_id ? '&nbsp;&nbsp;&nbsp;↳ ' : ''; ?>
                                        <?php echo htmlspecialchars($group->name); ?>
                                    </div>
                                    <?php if ($group->description): ?>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($group->description); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($group->code): ?>
                                        <span class="badge bg-light text-dark border">
                                            <?php echo htmlspecialchars($group->code); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge rounded-pill 
                                        <?php
                                        echo match ($group->nature) {
                                            'Assets' => 'bg-success',
                                            'Liabilities' => 'bg-danger',
                                            'Income' => 'bg-primary',
                                            'Expenses' => 'bg-warning text-dark',
                                            'Equity' => 'bg-info',
                                            default => 'bg-secondary'
                                        };
                                        ?>">
                                        <?php echo $group->nature; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $group->parent_name ? htmlspecialchars($group->parent_name) : '<span class="text-muted">Root</span>'; ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-2">
                                        <a href="<?php echo APP_URL; ?>/account_groups/edit/<?php echo $group->id; ?>"
                                            class="btn btn-sm btn-outline-primary border-0" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?php echo APP_URL; ?>/account_groups/delete/<?php echo $group->id; ?>"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('Are you sure? This will fail if the group has sub-groups or ledgers.');">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>