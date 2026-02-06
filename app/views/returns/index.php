<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 fade-in-up"
    style="position: relative; z-index: 1060;">
    <h1 class="h3 mb-0 text-gradient"><i class="fas fa-undo me-2"></i> Returns (Credit/Debit Notes)</h1>
    <div class="dropdown" style="position: relative; z-index: 1050;">
        <button class="btn btn-primary dropdown-toggle" type="button" id="newReturnDropdown" data-bs-toggle="dropdown"
            aria-expanded="false">
            <i class="fas fa-plus me-2"></i> New Return
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="newReturnDropdown">
            <li>
                <a class="dropdown-item" href="<?php echo APP_URL; ?>/returns/sales_return">
                    <i class="fas fa-undo text-danger me-2"></i> Sales Return (Credit Note)
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="<?php echo APP_URL; ?>/returns/purchase_return">
                    <i class="fas fa-undo text-warning me-2"></i> Purchase Return (Debit Note)
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="neu-card fade-in-up">
    <div class="table-responsive">
        <table class="table table-neu table-hover align-middle mb-0">
            <thead class="text-secondary small text-uppercase">
                <tr>
                    <th class="ps-4">Date</th>
                    <th>Return #</th>
                    <th>Type</th>
                    <th>Party</th>
                    <th>Amount</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['returns'])): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-undo fa-2x mb-3 opacity-50"></i>
                            <p>No returns found.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['returns'] as $return): ?>
                        <tr>
                            <td class="ps-4">
                                <?php echo date('d M Y', strtotime($return->voucher_date)); ?>
                            </td>
                            <td class="fw-bold text-primary">
                                <?php echo $return->voucher_number; ?>
                            </td>
                            <td>
                                <span
                                    class="badge rounded-pill <?php echo $return->voucher_type == 'Credit Note' ? 'bg-danger' : 'bg-warning'; ?> text-white">
                                    <?php echo $return->voucher_type; ?>
                                </span>
                            </td>
                            <td>
                                <?php echo $return->party_name; ?>
                            </td>
                            <td class="fw-bold">₹
                                <?php echo number_format($return->total_amount, 2); ?>
                            </td>
                            <td class="text-end pe-4">
                                <a href="<?php echo APP_URL; ?>/returns/show/<?php echo $return->id; ?>"
                                    class="btn-neu btn-neu-sm text-secondary me-1">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="<?php echo APP_URL; ?>/returns/edit/<?php echo $return->id; ?>"
                                    class="btn-neu btn-neu-sm text-primary me-1">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button
                                    onclick="deleteReturn(<?php echo $return->id; ?>, '<?php echo $return->voucher_number; ?>')"
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