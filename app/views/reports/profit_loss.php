<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-file-invoice-dollar text-success"></i> Profit & Loss Statement</h4>

    <form class="d-flex align-items-center" method="get">
        <label class="me-2 fw-bold">From:</label>
        <input type="date" name="from_date" class="form-control form-control-sm me-2"
            value="<?php echo $data['from_date']; ?>">

        <label class="me-2 fw-bold">To:</label>
        <input type="date" name="to_date" class="form-control form-control-sm me-2"
            value="<?php echo $data['to_date']; ?>">

        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="window.print()"><i
                class="fas fa-print"></i></button>
    </form>
</div>

<div class="row g-4">
    <!-- Expenses (Dr) -->
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0">Expenses (Debit)</h6>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <tbody>
                        <?php foreach ($data['expense_groups'] as $grp): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($grp->name); ?></td>
                                <td class="text-end"><?php echo number_format($grp->amount, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if ($data['net_profit'] > 0): ?>
                            <tr class="fw-bold bg-light">
                                <td>Net Profit (To Capital)</td>
                                <td class="text-end text-success"><?php echo number_format($data['net_profit'], 2); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <td>Total</td>
                            <td class="text-end">
                                <?php echo number_format(max($data['total_expense'] + max(0, $data['net_profit']), $data['total_income']), 2); ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Income (Cr) -->
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0">Income (Credit)</h6>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <tbody>
                        <?php foreach ($data['income_groups'] as $grp): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($grp->name); ?></td>
                                <td class="text-end"><?php echo number_format($grp->amount, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if ($data['net_profit'] < 0): ?>
                            <tr class="fw-bold bg-light">
                                <td>Net Loss (From Capital)</td>
                                <td class="text-end text-danger"><?php echo number_format(abs($data['net_profit']), 2); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <td>Total</td>
                            <td class="text-end">
                                <?php echo number_format(max($data['total_income'] + max(0, -$data['net_profit']), $data['total_expense']), 2); ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>