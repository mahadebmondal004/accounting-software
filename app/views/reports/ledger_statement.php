<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="mb-3">
    <h4><i class="fas fa-book-open text-warning"></i> Ledger Statement</h4>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body bg-light">
        <form class="row g-3 align-items-end" method="get">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Select Ledger Account</label>
                <select name="ledger_id" class="form-select select2-basic" required>
                    <option value="">Choose...</option>
                    <?php foreach ($data['ledgers'] as $l): ?>
                        <option value="<?php echo $l->id; ?>" <?php echo ($data['ledger_id'] == $l->id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($l->name); ?> (<?php echo $l->group_name; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">From Date</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo $data['start_date']; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">To Date</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo $data['end_date']; ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> View</button>
            </div>
        </form>
    </div>
</div>

<?php if ($data['ledger_id']):
    $running_balance = $data['opening_balance'];
    $total_dr = 0;
    $total_cr = 0;
    ?>
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold">Transactions from <?php echo date('d-M-Y', strtotime($data['start_date'])); ?> to
                    <?php echo date('d-M-Y', strtotime($data['end_date'])); ?></span>
                <button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="fas fa-print"></i>
                    Print</button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Voucher No</th>
                        <th>Particulars</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                        <th class="text-end">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Opening Balance -->
                    <tr class="fw-bold bg-light text-muted">
                        <td colspan="3">Opening Balance</td>
                        <td class="text-end">
                            <?php echo ($data['opening_balance'] > 0) ? number_format($data['opening_balance'], 2) : ''; ?>
                        </td>
                        <td class="text-end">
                            <?php echo ($data['opening_balance'] < 0) ? number_format(abs($data['opening_balance']), 2) : ''; ?>
                        </td>
                        <td class="text-end"><?php echo number_format(abs($data['opening_balance']), 2); ?>
                            <?php echo ($data['opening_balance'] >= 0) ? 'Dr' : 'Cr'; ?></td>
                    </tr>

                    <?php foreach ($data['transactions'] as $t):
                        $dr = floatval($t->debit);
                        $cr = floatval($t->credit);
                        $total_dr += $dr;
                        $total_cr += $cr;

                        // Update running balance (Dr is positive)
                        $running_balance += ($dr - $cr);
                        ?>
                        <tr>
                            <td><?php echo date('d-M-Y', strtotime($t->voucher_date)); ?></td>
                            <td><a href="#" class="text-decoration-none"><?php echo $t->voucher_number; ?></a></td>
                            <td>
                                <span class="badge bg-light text-dark border me-1"><?php echo $t->voucher_type; ?></span>
                                <?php echo htmlspecialchars($t->narration); ?>
                            </td>
                            <td class="text-end"><?php echo ($dr > 0) ? number_format($dr, 2) : ''; ?></td>
                            <td class="text-end"><?php echo ($cr > 0) ? number_format($cr, 2) : ''; ?></td>
                            <td class="text-end fw-bold">
                                <?php echo number_format(abs($running_balance), 2); ?>
                                <span class="small text-muted"><?php echo ($running_balance >= 0) ? 'Dr' : 'Cr'; ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-active fw-bold">
                    <tr>
                        <td colspan="3" class="text-end">Current Period Totals</td>
                        <td class="text-end"><?php echo number_format($total_dr, 2); ?></td>
                        <td class="text-end"><?php echo number_format($total_cr, 2); ?></td>
                        <td></td>
                    </tr>
                    <tr class="bg-primary text-white">
                        <td colspan="5" class="text-end">Closing Balance</td>
                        <td class="text-end"><?php echo number_format(abs($running_balance), 2); ?>
                            <?php echo ($running_balance >= 0) ? 'Dr' : 'Cr'; ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>