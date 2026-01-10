<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="mb-4 fade-in-up">
    <h2 class="h3 mb-0 text-gradient"><i class="fas fa-sitemap me-2"></i> Create Account Group</h2>
</div>

<div class="card shadow-sm border-0 fade-in-up">
    <div class="card-body p-4">
        <form action="<?php echo APP_URL; ?>/account_groups/create" method="post">
            <?php echo csrf_field(); ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted">Group Name *</label>
                    <input type="text" name="name" class="form-control" required autofocus
                        placeholder="e.g., Sundry Debtors, Bank Accounts">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted">Group Code</label>
                    <input type="text" name="code" class="form-control" placeholder="e.g., SD, BA (optional)">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted">Nature *</label>
                    <select name="nature" class="form-select" required>
                        <option value="">Select Nature...</option>
                        <option value="Assets">Assets</option>
                        <option value="Liabilities">Liabilities</option>
                        <option value="Income">Income</option>
                        <option value="Expenses">Expenses</option>
                        <option value="Equity">Equity</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted">Parent Group</label>
                    <select name="parent_id" class="form-select">
                        <option value="">None (Root Level)</option>
                        <?php
                        $currentNature = '';
                        foreach ($data['parent_groups'] as $pg):
                            if ($currentNature != $pg->nature):
                                $currentNature = $pg->nature;
                                ?>
                                <option disabled>──
                                    <?php echo $pg->nature; ?> ──
                                </option>
                            <?php endif; ?>
                            <option value="<?php echo $pg->id; ?>">
                                <?php echo $pg->parent_id ? '&nbsp;&nbsp;&nbsp;↳ ' : ''; ?>
                                <?php echo htmlspecialchars($pg->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Select a parent to create a sub-group</small>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-bold text-muted">Description</label>
                    <textarea name="description" class="form-control" rows="3"
                        placeholder="Optional description..."></textarea>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-2"></i> Create Group
                </button>
                <a href="<?php echo APP_URL; ?>/account_groups/index" class="btn btn-outline-secondary px-4">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>