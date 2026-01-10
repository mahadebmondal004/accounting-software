<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-book text-success"></i> <?php echo ucfirst($data['type']); ?> Book</h4>

    <form class="d-flex align-items-center" method="get">
        <label class="me-2 fw-bold">Select Account:</label>
        <select name="ledger_id" class="form-select form-select-sm me-2" onchange="this.form.submit()">
            <?php foreach ($data['ledgers'] as $l): ?>
                <option value="<?php echo $l->id; ?>" <?php echo ($data['selected_ledger'] == $l->id) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($l->name); ?>
                </option>
            <?php endforeach; ?>
        </select>

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

<!-- Reusing Ledger Statement Logic visually -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Voucher</th>
                    <th>Description</th>
                    <th class="text-end text-success">Receipt (Dr)</th>
                    <th class="text-end text-danger">Payment (Cr)</th>
                    <th class="text-end">Balance</th>
                </tr>
            </thead>
            <tbody>
                <tr class="table-warning fw-bold">
                    <td><?php echo $data['start_date']; ?></td>
                    <td>-</td>
                    <td>Opening Balance</td>
                    <td class="text-end"><?php echo ($data['opening'] > 0) ? number_format($data['opening'], 2) : ''; ?>
                    </td>
                    <td class="text-end">
                        <?php echo ($data['opening'] < 0) ? number_format(abs($data['opening']), 2) : ''; ?></td>
                    <td class="text-end"><?php echo number_format($data['opening'], 2); ?></td>
                </tr>

                <?php
                $running_balance = $data['opening'];
                foreach ($data['transactions'] as $t):
                    $net_change = $t->debit - $t->credit;
                    $running_balance += $net_change;
                    ?>
                    <tr>
                        <td><?php echo $t->voucher_date; ?></td>
                        <td><?php echo $t->voucher_number; ?></td>
                        <td>
                            <?php echo htmlspecialchars($t->narration); ?>
                            <small class="text-muted d-block"><?php echo htmlspecialchars($t->description); ?></small>
                        </td>
                        <td class="text-end text-success"><?php echo ($t->debit > 0) ? number_format($t->debit, 2) : '-'; ?>
                        </td>
                        <td class="text-end text-danger">
                            <?php echo ($t->credit > 0) ? number_format($t->credit, 2) : '-'; ?></td>
                        <td class="text-end fw-bold"><?php echo number_format($running_balance, 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <td colspan="5" class="text-end fw-bold">Closing Balance:</td>
                    <td class="text-end fw-bold"><?php echo number_format($running_balance, 2); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>