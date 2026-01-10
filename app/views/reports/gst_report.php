<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-file-invoice text-primary"></i> GST Tax Report</h4>

    <form class="d-flex align-items-center" method="get">
        <label class="me-2 fw-bold">From:</label>
        <input type="date" name="start_date" class="form-control form-control-sm me-2"
            value="<?php echo $data['start_date']; ?>">

        <label class="me-2 fw-bold">To:</label>
        <input type="date" name="end_date" class="form-control form-control-sm me-2"
            value="<?php echo $data['end_date']; ?>">

        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="window.print()"><i
                class="fas fa-print"></i></button>
    </form>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Tax Summary Input (Purchases) vs Output (Sales)</div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tax Ledger</th>
                            <th class="text-end">Total Debit (Input Tax Claimed)</th>
                            <th class="text-end">Total Credit (Output Tax Collected)</th>
                            <th class="text-end">Net Payable / (Refundable)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_dr = 0;
                        $total_cr = 0;
                        if (empty($data['tax_rows'])): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">No Tax transactions found.</td>
                            </tr>
                        <?php else:
                            foreach ($data['tax_rows'] as $row):
                                $net = $row->total_credit - $row->total_debit; // Output - Input
                                $total_dr += $row->total_debit;
                                $total_cr += $row->total_credit;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row->name); ?></td>
                                    <td class="text-end"><?php echo number_format($row->total_debit, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($row->total_credit, 2); ?></td>
                                    <td class="text-end fw-bold <?php echo ($net > 0) ? 'text-danger' : 'text-success'; ?>">
                                        <?php echo number_format($net, 2); ?>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <td>Total</td>
                            <td class="text-end"><?php echo number_format($total_dr, 2); ?></td>
                            <td class="text-end"><?php echo number_format($total_cr, 2); ?></td>
                            <td class="text-end"><?php echo number_format($total_cr - $total_dr, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>