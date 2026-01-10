<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 fade-in-up">
    <h2 class="h3 mb-0 text-gradient"><i class="fas fa-book me-2"></i> Ledgers</h2>
    <a href="<?php echo APP_URL; ?>/ledgers/create" class="btn-neu btn-neu-primary"><i class="fas fa-plus me-2"></i> New
        Ledger</a>
</div>

<div class="card shadow-none bg-transparent">
    <div class="card-body p-0">
        <div class="neu-card">
            <?php if (empty($data['ledgers'])): ?>
                <div class="text-center py-5">
                    <i class="fas fa-book-open fa-3x text-secondary mb-3 opacity-50"></i>
                    <h5 class="text-secondary">No ledgers found</h5>
                    <p class="text-muted">Create your first ledger to start recording transactions.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-neu">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Group</th>
                                <th>Type</th>
                                <th class="text-end">Balance</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['ledgers'] as $index => $ledger): ?>
                                <tr
                                    style="animation: fadeInUp 0.5s ease-out forwards; animation-delay: <?php echo $index * 0.05; ?>s; opacity: 0;">
                                    <td>
                                        <div class="fw-bold text-secondary"><?php echo htmlspecialchars($ledger->name); ?></div>
                                        <?php if ($ledger->code): ?>
                                            <small class="text-muted"><i class="fas fa-hashtag me-1"></i>
                                                <?php echo htmlspecialchars($ledger->code); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill bg-light text-secondary shadow-sm px-3 py-2">
                                            <?php echo htmlspecialchars($ledger->group_name); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="small text-uppercase fw-bold text-muted">
                                            <?php echo htmlspecialchars($ledger->type); ?>
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold" style="color: var(--primary);">
                                        <?php echo number_format($ledger->current_balance ?? $ledger->opening_balance, 2); ?>
                                        <span class="small text-muted ms-1"><?php echo $ledger->opening_balance_type; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="#" class="btn-neu btn-neu-sm text-info">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>