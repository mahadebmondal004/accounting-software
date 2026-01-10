<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-calendar-day text-primary"></i> Day Book</h4>

    <form class="d-flex align-items-center" method="get">
        <label class="me-2 fw-bold">Date:</label>
        <input type="date" name="date" class="form-control form-control-sm me-2" value="<?php echo $data['date']; ?>">

        <button type="submit" class="btn btn-sm btn-primary">Go</button>
        <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="window.print()"><i
                class="fas fa-print"></i></button>
    </form>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Voc. No.</th>
                    <th>Type</th>
                    <th>Ledgers</th>
                    <th>Narration</th>
                    <th>Created By</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['vouchers'])): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">No transactions found for this date.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['vouchers'] as $v): ?>
                        <tr>
                            <td><a href="<?php echo APP_URL; ?>/vouchers/view/<?php echo $v->id; ?>"
                                    class="text-decoration-none fw-bold"><?php echo $v->voucher_number; ?></a></td>
                            <td><span class="badge bg-secondary"><?php echo $v->voucher_type; ?></span></td>
                            <td><?php echo htmlspecialchars($v->involved_ledgers); ?></td>
                            <td><?php echo htmlspecialchars($v->narration); ?></td>
                            <td><small><?php echo htmlspecialchars($v->creator_name); ?></small></td>
                            <td class="text-end fw-bold"><?php echo number_format($v->total_amount, 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>