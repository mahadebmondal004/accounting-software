<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 fade-in-up">
    <h1 class="h3 mb-0 text-gradient"><i class="fas fa-file-alt me-2"></i> Estimates / Quotations</h1>
    <a href="<?php echo APP_URL; ?>/estimates/create" class="btn-neu btn-neu-primary">
        <i class="fas fa-plus me-2"></i> New Estimate
    </a>
</div>

<div class="neu-card fade-in-up">
    <div class="table-responsive">
        <table class="table table-neu table-hover align-middle mb-0">
            <thead class="text-secondary small text-uppercase">
                <tr>
                    <th class="ps-4">Date</th>
                    <th>Estimate #</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['estimates'])): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-file-alt fa-2x mb-3 opacity-50"></i>
                            <p>No estimates found.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['estimates'] as $est): ?>
                        <tr>
                            <td class="ps-4">
                                <?php echo date('d M Y', strtotime($est->estimate_date)); ?>
                            </td>
                            <td class="fw-bold text-primary">
                                <?php echo $est->estimate_number; ?>
                            </td>
                            <td>
                                <?php echo $est->customer_name; ?>
                            </td>
                            <td class="fw-bold">₹
                                <?php echo number_format($est->total_amount, 2); ?>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-light text-secondary border">
                                    <?php echo $est->status; ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="<?php echo APP_URL; ?>/estimates/show/<?php echo $est->id; ?>"
                                    class="btn-neu btn-neu-sm text-secondary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>