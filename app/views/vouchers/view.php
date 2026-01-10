<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i> Voucher Details</h5>
                <a href="<?php echo APP_URL; ?>/vouchers/print/<?php echo $data['voucher']->id; ?>"
                    class="btn btn-light btn-sm" target="_blank">
                    <i class="fas fa-print"></i> Print
                </a>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Voucher Number:</strong></p>
                        <p class="text-primary fw-bold">
                            <?php echo htmlspecialchars($data['voucher']->voucher_number); ?></p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="mb-1"><strong>Date:</strong></p>
                        <p><?php echo date('d-M-Y', strtotime($data['voucher']->voucher_date)); ?></p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Type:</strong></p>
                        <p><span class="badge bg-secondary"><?php echo $data['voucher']->voucher_type; ?></span></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Created By:</strong></p>
                        <p><?php echo htmlspecialchars($data['voucher']->created_by_name); ?></p>
                    </div>
                </div>

                <div class="mb-4">
                    <p class="mb-1"><strong>Narration:</strong></p>
                    <p class="text-muted"><?php echo htmlspecialchars($data['voucher']->narration); ?></p>
                </div>

                <h6 class="mb-3">Entries:</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Ledger</th>
                                <th class="text-end">Debit (₹)</th>
                                <th class="text-end">Credit (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $totalDebit = 0;
                            $totalCredit = 0;
                            foreach ($data['entries'] as $entry):
                                $totalDebit += $entry->debit;
                                $totalCredit += $entry->credit;
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($entry->ledger_name); ?></strong>
                                        <?php if ($entry->ledger_code): ?>
                                            <br><small
                                                class="text-muted"><?php echo htmlspecialchars($entry->ledger_code); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php echo $entry->debit > 0 ? number_format($entry->debit, 2) : '-'; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php echo $entry->credit > 0 ? number_format($entry->credit, 2) : '-'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th>Total</th>
                                <th class="text-end">₹ <?php echo number_format($totalDebit, 2); ?></th>
                                <th class="text-end">₹ <?php echo number_format($totalCredit, 2); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-4">
                    <a href="<?php echo APP_URL; ?>/vouchers/index" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>