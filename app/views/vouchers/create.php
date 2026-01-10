<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="row justify-content-center fade-in-up">
    <div class="col-md-11">
        <div class="neu-card">
            <div
                class="card-header border-bottom border-light pb-3 mb-4 d-flex justify-content-between align-items-center bg-transparent">
                <h5 class="mb-0 text-gradient font-weight-bold">
                    <i class="fas fa-edit me-2"></i> <?php echo $data['type']; ?> Voucher Entry
                </h5>
                <span
                    class="badge bg-light text-secondary shadow-sm px-3 py-2 rounded-pill"><?php echo $data['voucher_number']; ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($data['error'])): ?>
                    <div class="alert alert-danger shadow-sm border-0 rounded-3 mb-4"><?php echo $data['error']; ?></div>
                <?php endif; ?>

                <form action="<?php echo APP_URL; ?>/vouchers/create/<?php echo $data['type']; ?>" method="post"
                    id="voucherForm">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="voucher_type" value="<?php echo $data['type']; ?>">

                    <div class="neu-card mb-4 p-4">
                        <div class="row g-4">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-secondary ms-1">Voucher No</label>
                                <input type="text" name="voucher_number" class="form-control-neu bg-light"
                                    value="<?php echo $data['voucher_number']; ?>" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-secondary ms-1">Date</label>
                                <input type="date" name="voucher_date" class="form-control-neu"
                                    value="<?php echo $data['voucher_date']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary ms-1">Narration</label>
                                <input type="text" name="narration" class="form-control-neu"
                                    placeholder="Enter transaction details..." value="<?php echo $data['narration']; ?>"
                                    required>
                            </div>
                        </div>
                    </div>

                    <h6 class="text-secondary text-uppercase fw-bold ms-1 mb-3 small"><i
                            class="fas fa-list-ul me-2"></i> Transaction Details (Double Entry)</h6>

                    <div class="neu-card p-0 overflow-hidden mb-3">
                        <div class="table-responsive">
                            <table class="table table-neu table-hover align-middle mb-0" id="entriesTable">
                                <thead class="text-secondary small text-uppercase">
                                    <tr>
                                        <th style="width: 5%" class="text-center">#</th>
                                        <th style="width: 45%" class="ps-3">Account / Ledger</th>
                                        <th style="width: 20%" class="text-end">Debit (₹)</th>
                                        <th style="width: 20%" class="text-end">Credit (₹)</th>
                                        <th style="width: 10%"></th>
                                    </tr>
                                </thead>
                                <tbody class="border-top-0">
                                    <!-- Entries will be generated here. Default 2 rows. -->
                                    <?php
                                    $rows = 2; // Default
                                    // If we have old input (error case), use that
                                    if (isset($data['input']['ledger_id'])) {
                                        $rows = count($data['input']['ledger_id']);
                                    }

                                    for ($i = 0; $i < $rows; $i++):
                                        $ledger_val = $data['input']['ledger_id'][$i] ?? '';
                                        $dr_val = $data['input']['debit'][$i] ?? '';
                                        $cr_val = $data['input']['credit'][$i] ?? '';
                                        ?>
                                        <tr>
                                            <td class="text-center align-middle text-secondary"><?php echo $i + 1; ?></td>
                                            <td class="ps-3">
                                                <select name="ledger_id[]"
                                                    class="form-control-neu form-control-sm select2-basic">
                                                    <option value="">Select Ledger...</option>
                                                    <?php foreach ($data['ledgers'] as $l): ?>
                                                        <option value="<?php echo $l->id; ?>" <?php echo ($ledger_val == $l->id) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($l->name); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="debit[]"
                                                    class="form-control-neu form-control-sm text-end dr-input"
                                                    placeholder="0.00" value="<?php echo $dr_val; ?>"
                                                    onchange="calcTotal()">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="credit[]"
                                                    class="form-control-neu form-control-sm text-end cr-input"
                                                    placeholder="0.00" value="<?php echo $cr_val; ?>"
                                                    onchange="calcTotal()">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn-neu btn-neu-sm text-danger shadow-sm"
                                                    onclick="removeRow(this)"
                                                    style="width: 30px; height: 30px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"><i
                                                        class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    <?php endfor; ?>
                                </tbody>
                                <tfoot class="bg-transparent fw-bold text-secondary">
                                    <tr>
                                        <td colspan="2" class="text-end border-0">Total</td>
                                        <td class="text-end text-success border-0" id="totalDr">0.00</td>
                                        <td class="text-end text-success border-0" id="totalCr">0.00</td>
                                        <td class="border-0"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-end text-danger border-0" id="diffLabel">Difference
                                        </td>
                                        <td colspan="2" class="text-center text-danger border-0" id="diffAmount">0.00
                                        </td>
                                        <td class="border-0"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="mb-4">
                        <button type="button" class="btn-neu btn-neu-sm btn-neu-primary" onclick="addRow()"><i
                                class="fas fa-plus me-2"></i> Add Row</button>
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top border-light"
                        style="border-top: 1px solid rgba(0,0,0,0.05) !important;">
                        <a href="<?php echo APP_URL; ?>/vouchers/index" class="btn-neu"
                            style="color: var(--secondary);">Cancel</a>
                        <button type="submit" class="btn-neu btn-neu-primary px-5" id="submitBtn" disabled><i
                                class="fas fa-save me-2"></i> Save Voucher</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function calcTotal() {
        let totalDr = 0;
        let totalCr = 0;

        document.querySelectorAll('.dr-input').forEach(i => totalDr += parseFloat(i.value) || 0);
        document.querySelectorAll('.cr-input').forEach(i => totalCr += parseFloat(i.value) || 0);

        document.getElementById('totalDr').innerText = totalDr.toFixed(2);
        document.getElementById('totalCr').innerText = totalCr.toFixed(2);

        let diff = Math.abs(totalDr - totalCr);
        document.getElementById('diffAmount').innerText = diff.toFixed(2);

        const submitBtn = document.getElementById('submitBtn');
        if (diff < 0.01 && totalDr > 0) {
            submitBtn.disabled = false;
            document.getElementById('diffLabel').style.display = 'none';
            document.getElementById('diffAmount').style.display = 'none';
        } else {
            submitBtn.disabled = true;
            document.getElementById('diffLabel').style.display = 'table-cell';
            document.getElementById('diffAmount').style.display = 'table-cell';
        }
    }

    function addRow() {
        const table = document.getElementById('entriesTable').getElementsByTagName('tbody')[0];
        const newRow = table.rows[0].cloneNode(true);

        // Reset values
        newRow.querySelector('select').value = '';
        newRow.querySelector('.dr-input').value = '';
        newRow.querySelector('.cr-input').value = '';

        table.appendChild(newRow);
        updateIndices();
    }

    function removeRow(btn) {
        const row = btn.parentNode.parentNode;
        const table = document.getElementById('entriesTable').getElementsByTagName('tbody')[0];
        if (table.rows.length > 2) {
            row.parentNode.removeChild(row);
            calcTotal();
            updateIndices();
        } else {
            alert("Minimum 2 rows required.");
        }
    }

    function updateIndices() {
        const table = document.getElementById('entriesTable').getElementsByTagName('tbody')[0];
        for (let i = 0; i < table.rows.length; i++) {
            table.rows[i].cells[0].innerText = i + 1;
        }
    }

    // Init
    document.addEventListener("DOMContentLoaded", function () {
        calcTotal();
    });
</script>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>