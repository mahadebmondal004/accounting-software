<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h4 class="mb-0"><i class="fas fa-edit text-primary"></i> Edit Role</h4>
            </div>
            <div class="card-body">
                <form action="<?php echo APP_URL; ?>/roles/edit/<?php echo $data['id']; ?>" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required value="<?php echo htmlspecialchars($data['name']); ?>">
                        <?php if (!empty($data['error'])): ?>
                            <div class="text-danger small mt-1"><?php echo $data['error']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block">Permissions</label>
                        <div class="card border-light">
                            <div class="card-body">
                                <?php
                                // Group permissions by category
                                $categories = [
                                    'Company & User Management' => ['manage_company', 'manage_users', 'manage_roles'],
                                    'Account Groups (Chart of Accounts)' => ['view_account_groups', 'manage_account_groups'],
                                    'Ledgers (Accounts)' => ['view_ledgers', 'manage_ledgers'],
                                    'Products/Services' => ['view_items', 'manage_items'],
                                    'Sales Module' => ['view_sales', 'create_sales', 'edit_sales', 'delete_sales'],
                                    'Purchase Module' => ['view_purchases', 'create_purchases', 'edit_purchases', 'delete_purchases'],
                                    'Estimates/Quotations' => ['view_estimates', 'create_estimates', 'edit_estimates', 'delete_estimates'],
                                    'Returns (Credit/Debit Notes)' => ['view_returns', 'create_returns', 'edit_returns', 'delete_returns'],
                                    'Vouchers & Transactions' => ['view_vouchers', 'create_vouchers', 'edit_vouchers', 'delete_vouchers'],
                                    'Receipts & Payments' => ['create_receipts', 'create_payments'],
                                    'Reports & Dashboard' => ['view_reports', 'view_dashboard', 'export_reports'],
                                    'Quick Access Roles' => ['accounting', 'accounting_view', 'entry']
                                ];

                                foreach ($categories as $category => $perms):
                                    $hasPerms = false;
                                    foreach ($perms as $p) {
                                        if (isset($data['all_permissions'][$p])) {
                                            $hasPerms = true;
                                            break;
                                        }
                                    }
                                    if (!$hasPerms) continue;
                                ?>
                                    <div class="mb-4">
                                        <h6 class="text-primary mb-3"><i class="fas fa-shield-alt me-2"></i><?php echo $category; ?></h6>
                                        <div class="row">
                                            <?php foreach ($perms as $key): ?>
                                                <?php if (isset($data['all_permissions'][$key])): ?>
                                                    <div class="col-md-6 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="permissions[]" 
                                                                value="<?php echo $key; ?>" id="perm_<?php echo $key; ?>" 
                                                                <?php echo (isset($data['permissions'][$key]) || isset($data['permissions']['all'])) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="perm_<?php echo $key; ?>">
                                                                <?php echo htmlspecialchars($data['all_permissions'][$key]); ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?php echo APP_URL; ?>/roles/index" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
