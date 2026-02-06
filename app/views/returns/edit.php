<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="card shadow-sm border-0 fade-in-up">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-edit me-2"></i> Edit <?php echo $data['return_type']; ?></h5>
        <div class="text-muted small fw-bold badge bg-light text-dark border px-3 py-2">
            Date: <?php echo date('d-M-Y'); ?>
        </div>
    </div>
    <div class="card-body p-4">
        <form action="<?php echo APP_URL; ?>/returns/edit/<?php echo $data['voucher']->id; ?>" method="post">
            <?php echo csrf_field(); ?>
            <div class="card bg-light border-0 p-3 mb-4">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Return Number</label>
                        <input type="text" name="invoice_number" class="form-control fw-bold"
                            value="<?php echo $data['voucher']->voucher_number; ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Return Date</label>
                        <input type="date" name="return_date" class="form-control"
                            value="<?php echo $data['voucher']->voucher_date; ?>">
                    </div>
                    <?php
                    $tax_type_val = 'in_state';
                    if (!empty($data['items'])) {
                        foreach ($data['items'] as $it) {
                            if ($it->igst_amount > 0) {
                                $tax_type_val = 'out_state';
                                break;
                            }
                        }
                    }
                    ?>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Tax Type</label>
                        <select name="tax_type" id="taxType" class="form-select" onchange="toggleTaxColumns()">
                            <option value="in_state" <?php echo $tax_type_val == 'in_state' ? 'selected' : ''; ?>>In-State (CGST+SGST)</option>
                            <option value="out_state" <?php echo $tax_type_val == 'out_state' ? 'selected' : ''; ?>>Out-State (IGST)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Reason</label>
                        <input type="text" name="reason" class="form-control"
                            value="<?php echo $data['voucher']->notes ?? ''; ?>" placeholder="Return reason">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted"><?php echo $data['return_type'] == 'Credit Note' ? 'Customer' : 'Supplier'; ?></label>
                        <select name="party_ledger_id" class="form-select select2-basic" required autofocus>
                            <option value="">Select Customer...</option>
                            <?php foreach ($data['customers'] as $c): ?>
                                <?php 
                                $party_type = $data['return_type'] == 'Credit Note' ? 'Customer' : 'Supplier';
                                if ($c->type == $party_type): 
                                ?>
                                    <option value="<?php echo $c->id; ?>" <?php echo ($data['party']->id == $c->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c->name); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>



            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle mb-0" id="invTable">
                    <thead class="bg-light text-secondary small text-uppercase">
                        <tr>
                            <th width="25%" class="ps-3">Item / Product</th>
                            <th width="8%">Qty</th>
                            <th width="12%">Rate</th>
                            <th width="12%">Amount</th>
                            <th width="8%">Tax %</th>

                            <!-- Dynamic Tax Columns -->
                            <th width="8%" class="tax-col-in">CGST</th>
                            <th width="8%" class="tax-col-in">SGST</th>
                            <th width="10%" class="tax-col-out d-none">IGST</th>

                            <th width="14%">Total</th>
                            <th width="5%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data['items'])): ?>
                            <?php foreach ($data['items'] as $item): ?>
                                <tr class="inv-row">
                                    <td class="ps-3">
                                        <input type="hidden" name="item_id[]" class="item_id" value="<?php echo $item->item_id ?? ''; ?>">
                                        <input type="text" name="item_name[]"
                                            class="form-control form-control-sm item-autocomplete" 
                                            placeholder="Type item..."
                                            value="<?php echo htmlspecialchars($item->item_name); ?>"
                                            list="itemList">
                                        <datalist id="itemList">
                                            <?php foreach ($data['all_items'] as $it): ?>
                                                <option value="<?php echo htmlspecialchars($it->name); ?>"
                                                    data-price="<?php echo $it->sale_price; ?>"
                                                    data-tax="<?php echo $it->tax_rate; ?>" data-id="<?php echo $it->id; ?>">
                                            <?php endforeach; ?>
                                        </datalist>
                                    </td>
                                    <td><input type="number" name="quantity[]" class="form-control form-control-sm text-end qty"
                                            value="<?php echo $item->quantity; ?>" oninput="calcRow(this)"></td>
                                    <td><input type="number" name="rate[]" step="0.01"
                                            class="form-control form-control-sm text-end rate" value="<?php echo $item->rate; ?>"
                                            oninput="calcRow(this)"></td>
                                    <td><input type="number" name="amount[]"
                                            class="form-control form-control-sm text-end bg-light amt" value="<?php echo $item->amount; ?>" readonly></td>
                                    <td><input type="number" name="tax_percent[]"
                                            class="form-control form-control-sm text-end tax_p" value="<?php echo $item->tax_percent; ?>"
                                            oninput="calcRow(this)"></td>

                                    <!-- Tax Inputs -->
                                    <td class="tax-col-in"><input type="number" class="form-control form-control-sm text-end bg-light cgst_amt" value="<?php echo $item->cgst_amount ?? 0; ?>" readonly></td>
                                    <td class="tax-col-in"><input type="number" class="form-control form-control-sm text-end bg-light sgst_amt" value="<?php echo $item->sgst_amount ?? 0; ?>" readonly></td>
                                    <td class="tax-col-out d-none"><input type="number" class="form-control form-control-sm text-end bg-light igst_amt" value="<?php echo $item->igst_amount ?? 0; ?>" readonly></td>

                                    <td><input type="number" name="row_total[]"
                                            class="form-control form-control-sm text-end bg-light fw-bold total" value="<?php echo $item->total; ?>" readonly></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger border-0"
                                            onclick="removeRow(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Default empty row -->
                            <tr class="inv-row">
                                <td class="ps-3">
                                    <input type="hidden" name="item_id[]" class="item_id">
                                    <input type="text" name="item_name[]"
                                        class="form-control form-control-sm item-autocomplete" placeholder="Type item..."
                                        list="itemList">
                                    <datalist id="itemList">
                                        <?php foreach ($data['all_items'] as $it): ?>
                                            <option value="<?php echo htmlspecialchars($it->name); ?>"
                                                data-price="<?php echo $it->sale_price; ?>"
                                                data-tax="<?php echo $it->tax_rate; ?>" data-id="<?php echo $it->id; ?>">
                                        <?php endforeach; ?>
                                    </datalist>
                                </td>
                                <td><input type="number" name="quantity[]" class="form-control form-control-sm text-end qty"
                                        value="1" oninput="calcRow(this)"></td>
                                <td><input type="number" name="rate[]" step="0.01"
                                        class="form-control form-control-sm text-end rate" value="0.00"
                                        oninput="calcRow(this)"></td>
                                <td><input type="number" name="amount[]"
                                        class="form-control form-control-sm text-end bg-light amt" readonly></td>
                                <td><input type="number" name="tax_percent[]"
                                        class="form-control form-control-sm text-end tax_p" value="18"
                                        oninput="calcRow(this)"></td>
                                
                                <td class="tax-col-in"><input type="number" class="form-control form-control-sm text-end bg-light cgst_amt" readonly></td>
                                <td class="tax-col-in"><input type="number" class="form-control form-control-sm text-end bg-light sgst_amt" readonly></td>
                                <td class="tax-col-out d-none"><input type="number" class="form-control form-control-sm text-end bg-light igst_amt" readonly></td>

                                <td><input type="number" name="row_total[]"
                                        class="form-control form-control-sm text-end bg-light fw-bold total" readonly></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger border-0"
                                        onclick="removeRow(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="bg-white border-top-0">
                        <tr>
                            <td colspan="7" class="p-3">
                                <button type="button" class="btn btn-sm btn-primary" onclick="addInvRow()">
                                    <i class="fas fa-plus me-2"></i> Add Item
                                </button>
                            </td>
                        </tr>
                        <tr class="text-muted text-end">
                            <td colspan="7" class="border-0 col-span-target">Taxable Amount</td>
                            <td class="border-0 fw-bold" id="sumTaxable">0.00</td>
                            <td class="border-0"></td>
                        </tr>
                        <tr class="text-muted text-end">
                            <td colspan="7" class="border-0 col-span-target">Total Tax</td>
                            <td class="border-0 fw-bold" id="sumTax">0.00</td>
                            <td class="border-0"></td>
                        </tr>
                        <tr class="text-primary h5 text-end">
                            <td colspan="7" class="border-0 fw-bold col-span-target">Grand Total</td>
                            <td class="border-0 fw-bold" id="grandTotal">0.00</td>
                            <td class="border-0"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Hidden Summaries for POST -->
            <input type="hidden" name="total_taxable" id="inpTaxable" value="0">
            <input type="hidden" name="total_tax" id="inpTax" value="0">
            <input type="hidden" name="total_payable" id="inpTotal" value="0">

            <div class="row align-items-end">
                <div class="col-md-8">
                    <div class="card p-3 bg-light border-0">
                        <label class="form-label small fw-bold text-muted">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"
                            placeholder="Enter notes..."><?php echo $data['voucher']->notes ?? ''; ?></textarea>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <input type="hidden" name="party_name" id="partyName">
                    <input type="hidden" name="total_amount" id="inpTotal" value="0">
                    <button type="submit" class="btn btn-primary px-5 py-3 w-100 shadow-sm fw-bold">
                        <i class="fas fa-save me-2"></i> Update Return
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Calculate totals on page load (for edit mode)
    document.addEventListener('DOMContentLoaded', function() {
        toggleTaxColumns();
    });

    // Handle Datalist selection
    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('item-autocomplete')) {
            var val = e.target.value;
            var list = document.getElementById('itemList');
            var options = list.childNodes;
            for (var i = 0; i < options.length; i++) {
                if (options[i].value === val) {
                    var row = e.target.closest('tr');
                    row.querySelector('.rate').value = options[i].getAttribute('data-price');
                    row.querySelector('.tax_p').value = options[i].getAttribute('data-tax');
                    row.querySelector('.item_id').value = options[i].getAttribute('data-id');
                    calcRow(e.target);
                    break;
                }
            }
        }
    });

    // Handle Customer Name for narration
    if (document.querySelector('select[name="party_ledger_id"]')) {
        document.querySelector('select[name="party_ledger_id"]').addEventListener('change', function (e) {
            let text = e.target.options[e.target.selectedIndex].text;
            let target = document.getElementById('partyName'); 
            if(target) target.value = text;
        });
    }

    function toggleTaxColumns() {
        var taxType = document.getElementById('taxType').value;
        var inCols = document.querySelectorAll('.tax-col-in');
        var outCols = document.querySelectorAll('.tax-col-out');
        var colSpans = document.querySelectorAll('.col-span-target');

        if (taxType === 'in_state') {
            inCols.forEach(el => el.classList.remove('d-none'));
            outCols.forEach(el => el.classList.add('d-none'));
            colSpans.forEach(el => el.setAttribute('colspan', '7'));
        } else {
            inCols.forEach(el => el.classList.add('d-none'));
            outCols.forEach(el => el.classList.remove('d-none'));
            colSpans.forEach(el => el.setAttribute('colspan', '6'));
        }

        // Recalculate all rows
        document.querySelectorAll('.qty').forEach(el => calcRow(el));
    }

    function calcRow(el) {
        let row = el.closest('tr');
        let qty = parseFloat(row.querySelector('.qty').value) || 0;
        let rate = parseFloat(row.querySelector('.rate').value) || 0;
        let taxP = parseFloat(row.querySelector('.tax_p').value) || 0;
        let taxType = document.getElementById('taxType').value;

        let amount = qty * rate;
        let taxAmt = amount * (taxP / 100);

        // Split Tax Logic
        if (taxType === 'in_state') {
            let halfTax = taxAmt / 2;
            row.querySelector('.cgst_amt').value = halfTax.toFixed(2);
            row.querySelector('.sgst_amt').value = halfTax.toFixed(2);
            row.querySelector('.igst_amt').value = "0.00";
        } else {
            row.querySelector('.cgst_amt').value = "0.00";
            row.querySelector('.sgst_amt').value = "0.00";
            row.querySelector('.igst_amt').value = taxAmt.toFixed(2);
        }

        let total = amount + taxAmt;

        row.querySelector('.amt').value = amount.toFixed(2);
        row.querySelector('.total').value = total.toFixed(2);

        calcTotals();
    }

    function calcTotals() {
        let taxable = 0;
        let tax = 0;
        let grand = 0;

        document.querySelectorAll('.amt').forEach(e => taxable += parseFloat(e.value) || 0);
        document.querySelectorAll('.inv-row').forEach(row => {
            let amt = parseFloat(row.querySelector('.amt').value) || 0;
            let tp = parseFloat(row.querySelector('.tax_p').value) || 0;
            tax += amt * (tp / 100);
        });

        grand = taxable + tax;

        document.getElementById('sumTaxable').innerText = taxable.toFixed(2);
        document.getElementById('sumTax').innerText = tax.toFixed(2);
        document.getElementById('grandTotal').innerText = grand.toFixed(2);

        document.getElementById('inpTaxable').value = taxable;
        document.getElementById('inpTax').value = tax;
        document.getElementById('inpTotal').value = grand;
    }

    function addInvRow() {
        let table = document.getElementById('invTable').getElementsByTagName('tbody')[0];
        let newRow = table.rows[0].cloneNode(true);
        newRow.querySelectorAll('input').forEach(i => {
            if (i.classList.contains('qty')) i.value = 1;
            else if (i.classList.contains('tax_p')) i.value = 18; // Keep default Tax
            else i.value = '';
        });
        table.appendChild(newRow);
        newRow.querySelector('.item-autocomplete').focus();
    }

    function removeRow(btn) {
        let row = btn.closest('tr');
        if (document.querySelectorAll('.inv-row').length > 1) {
            row.remove();
            calcTotals();
        }
    }
</script>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>