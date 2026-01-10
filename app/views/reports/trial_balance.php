<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-balance-scale text-info"></i> Trial Balance</h4>

    <form class="d-flex align-items-center" method="get">
        <label class="me-2 fw-bold">As Of:</label>
        <input type="date" name="date" class="form-control form-control-sm me-2" value="<?php echo $data['date']; ?>">
        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="window.print()"><i
                class="fas fa-print"></i></button>
    </form>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light text-uppercase small">
                    <tr>
                        <th style="width: 50%">Particulars</th>
                        <th style="width: 25%" class="text-end">Debit (₹)</th>
                        <th style="width: 25%" class="text-end">Credit (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['records'])): ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">No records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['records'] as $row): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($row['name']); ?></span>
                                    <br><small
                                        class="text-muted fst-italic"><?php echo htmlspecialchars($row['group']); ?></small>
                                </td>
                                <td class="text-end"><?php echo ($row['debit'] > 0) ? number_format($row['debit'], 2) : ''; ?>
                                </td>
                                <td class="text-end"><?php echo ($row['credit'] > 0) ? number_format($row['credit'], 2) : ''; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot class="fw-bold table-active">
                    <tr>
                        <td class="text-uppercase">Total</td>
                        <td class="text-end text-primary"><?php echo number_format($data['totalDr'], 2); ?></td>
                        <td class="text-end text-primary"><?php echo number_format($data['totalCr'], 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <?php if (abs($data['totalDr'] - $data['totalCr']) > 0.01): ?>
            <div class="alert alert-danger mt-3 text-center">
                <strong>Difference:</strong> <?php echo number_format(abs($data['totalDr'] - $data['totalCr']), 2); ?>
                <br> The accounts are not balanced! Check voucher integrity.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>