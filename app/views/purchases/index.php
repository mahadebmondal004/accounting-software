<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 fade-in-up">
    <h1 class="h3 mb-0 text-gradient"><i class="fas fa-shopping-cart me-2"></i> Purchase Bills</h1>
    <a href="<?php echo APP_URL; ?>/purchases/create" class="btn-neu btn-neu-primary">
        <i class="fas fa-plus me-2"></i> New Purchase Bill
    </a>
</div>

<div class="neu-card fade-in-up">
    <div class="table-responsive">
        <table class="table table-neu table-hover align-middle mb-0">
            <thead class="text-secondary small text-uppercase">
                <tr>
                    <th class="ps-4">Date</th>
                    <th>Bill #</th>
                    <th>Supplier</th>
                    <th>Amount</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['purchases'])): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-shopping-cart fa-2x mb-3 opacity-50"></i>
                            <p>No purchase bills found.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['purchases'] as $purchase): ?>
                        <tr>
                            <td class="ps-4">
                                <?php echo date('d M Y', strtotime($purchase->voucher_date)); ?>
                            </td>
                            <td class="fw-bold text-primary">
                                <?php echo $purchase->voucher_number; ?>
                            </td>
                            <td>
                                <?php echo $purchase->supplier_name; ?>
                            </td>
                            <td class="fw-bold">₹
                                <?php echo number_format($purchase->total_amount, 2); ?>
                            </td>
                            <td class="text-end pe-4">
                                <a href="<?php echo APP_URL; ?>/purchases/show/<?php echo $purchase->id; ?>"
                                    class="btn-neu btn-neu-sm text-secondary me-1">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="<?php echo APP_URL; ?>/purchases/edit/<?php echo $purchase->id; ?>"
                                    class="btn-neu btn-neu-sm text-primary me-1">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button
                                    onclick="deletePurchase(<?php echo $purchase->id; ?>, '<?php echo $purchase->voucher_number; ?>')"
                                    class="btn-neu btn-neu-sm text-danger">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>