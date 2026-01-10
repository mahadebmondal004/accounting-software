<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 fade-in-up" style="position: relative; z-index: 50;">
    <h2 class="h3 mb-0 text-gradient"><i class="fas fa-file-invoice me-2"></i> Vouchers</h2>
    <div class="dropdown">
        <button class="btn-neu btn-neu-primary dropdown-toggle" type="button" id="dropdownMenuButton1"
            data-bs-toggle="dropdown" aria-expanded="false" style="color: white !important;">
            <i class="fas fa-plus me-2 text-white"></i> New Voucher
        </button>
        <ul class="dropdown-menu text-secondary border-0 shadow-lg" aria-labelledby="dropdownMenuButton1"
            style="border-radius: 12px; margin-top: 10px;">
            <li><a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/payments/create"><i
                        class="fas fa-money-bill-wave me-2 text-danger"></i> Payment</a></li>
            <li><a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/receipts/create"><i
                        class="fas fa-hand-holding-usd me-2 text-success"></i> Receipt</a></li>
            <li><a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/vouchers/create/Journal"><i
                        class="fas fa-book me-2 text-info"></i> Journal</a></li>
            <li><a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/vouchers/create/Contra"><i
                        class="fas fa-exchange-alt me-2 text-warning"></i> Contra</a></li>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/sales/create"><i
                        class="fas fa-file-invoice-dollar me-2 text-primary"></i> Sales Invoice</a></li>
            <li><a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/purchases/create"><i
                        class="fas fa-shopping-cart me-2 text-secondary"></i> Purchase Bill</a></li>
        </ul>
    </div>
</div>

<div class="card shadow-none bg-transparent">
    <div class="card-body p-0">
        <div class="neu-card">
            <?php if (empty($data['vouchers'])): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-receipt fa-3x mb-3 text-secondary opacity-50"></i>
                    <h5 class="text-secondary">No transactions found.</h5>
                    <p class="small">Create a new voucher to get started.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-neu">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Voucher #</th>
                                <th>Type</th>
                                <th>Narration</th>
                                <th class="text-end">Amount</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['vouchers'] as $index => $v): ?>
                                <tr
                                    style="animation: fadeInUp 0.5s ease-out forwards; animation-delay: <?php echo $index * 0.05; ?>s; opacity: 0;">
                                    <td><?php echo date('d-M-Y', strtotime($v->voucher_date)); ?></td>
                                    <td class="fw-bold text-primary"><?php echo $v->voucher_number; ?></td>
                                    <td>
                                        <span class="badge rounded-pill bg-light text-secondary shadow-sm px-3 py-2">
                                            <?php echo $v->voucher_type; ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small">
                                        <?php echo substr($v->narration, 0, 50); ?>
                                        <?php echo strlen($v->narration) > 50 ? '...' : ''; ?>
                                    </td>
                                    <td class="text-end fw-bold" style="color: var(--primary);">₹
                                        <?php echo number_format($v->total_amount, 2); ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-2">
                                            <a href="<?php echo APP_URL; ?>/vouchers/show/<?php echo $v->id; ?>"
                                                class="btn-neu btn-neu-sm text-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo APP_URL; ?>/vouchers/print/<?php echo $v->id; ?>"
                                                class="btn-neu btn-neu-sm text-secondary" title="Print" target="_blank">
                                                <i class="fas fa-print"></i>
                                            </a>
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
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>