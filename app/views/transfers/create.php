<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8 fade-in-up">
        <div class="neu-card">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-light pb-3">
                <h5 class="mb-0 text-gradient fw-bold"><i class="fas fa-exchange-alt me-2"></i> Transfer Money (Contra)
                </h5>
                <a href="<?php echo APP_URL; ?>/vouchers/index" class="btn-neu btn-neu-sm text-secondary">
                    <i class="fas fa-list me-2"></i> All Vouchers
                </a>
            </div>

            <form action="<?php echo APP_URL; ?>/transfers/create" method="post">
                <?php echo csrf_field(); ?>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary ms-1">Voucher No</label>
                        <input type="text" name="voucher_number" class="form-control-neu"
                            value="<?php echo $data['voucher_number']; ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary ms-1">Date</label>
                        <input type="date" name="voucher_date" class="form-control-neu"
                            value="<?php echo $data['voucher_date']; ?>" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-secondary ms-1">Transfer FROM (Source)</label>
                        <select name="from_ledger_id" id="fromSelect" class="form-control-neu" required>
                            <option value="">Select Account...</option>
                            <?php foreach ($data['accounts'] as $b): ?>
                                <option value="<?php echo $b->id; ?>">
                                    <?php echo $b->name; ?> (Cur: ₹
                                    <?php echo number_format($b->current_balance ?? 0, 2); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="from_ledger_name" id="fromName">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-secondary ms-1">Transfer TO (Destination)</label>
                        <select name="to_ledger_id" id="toSelect" class="form-control-neu" required>
                            <option value="">Select Account...</option>
                            <?php foreach ($data['accounts'] as $b): ?>
                                <option value="<?php echo $b->id; ?>">
                                    <?php echo $b->name; ?> (Cur: ₹
                                    <?php echo number_format($b->current_balance ?? 0, 2); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="to_ledger_name" id="toName">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-secondary ms-1">Amount</label>
                        <div class="position-relative">
                            <span class="position-absolute top-50 ms-3 translate-middle-y fw-bold text-primary">₹</span>
                            <input type="number" step="0.01" name="amount"
                                class="form-control-neu ps-5 fw-bold text-primary" style="font-size: 1.2rem;" required
                                placeholder="0.00">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-secondary ms-1">Narration</label>
                        <input type="text" name="narration" class="form-control-neu"
                            placeholder="e.g., Cash Deposit, ATM Withdrawal">
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn-neu btn-neu-primary w-100 py-3 fw-bold">
                            <i class="fas fa-check-circle me-2"></i> Save Transfer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('fromSelect').addEventListener('change', function (e) {
        let text = e.target.options[e.target.selectedIndex].text;
        let name = text.split(' (Cur:')[0];
        document.getElementById('fromName').value = name;
    });
    document.getElementById('toSelect').addEventListener('change', function (e) {
        let text = e.target.options[e.target.selectedIndex].text;
        let name = text.split(' (Cur:')[0];
        document.getElementById('toName').value = name;
    });
</script>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>