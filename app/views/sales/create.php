<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="card shadow-sm border-0 fade-in-up">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-file-invoice me-2"></i> New Sales Invoice</h5>
        <div class="text-muted small fw-bold badge bg-light text-dark border px-3 py-2">
            Date: <?php echo date('d-M-Y'); ?>
        </div>
    </div>
    <div class="card-body p-4">
        <form action="<?php echo APP_URL; ?>/sales/create" method="post">
            <?php echo csrf_field(); ?>
            <div class="card bg-light border-0 p-3 mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Invoice Number</label>
                        <input type="text" name="invoice_number" class="form-control fw-bold"
                            value="<?php echo $data['invoice_number']; ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Date</label>
                        <input type="date" name="invoice_date" class="form-control"
                            value="<?php echo $data['invoice_date']; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Due Date</label>
                        <input type="date" name="due_date" class="form-control"
                            value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Customer (Billed To)</label>
                        <select name="customer_ledger_id" class="form-select select2-basic" required autofocus>
                            <option value="">Select Customer...</option>
                            <?php foreach ($data['customers'] as $c): ?>
                                <?php if ($c->type == 'Customer' || $c->nature == 'Assets'): ?>
                                    <option value="<?php echo $c->id; ?>"><?php echo htmlspecialchars($c->name); ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Tax Type</label>
                        <select name="tax_type" id="taxType" class="form-select" onchange="toggleTaxColumns()">
                            <option value="in_state">In-State (CGST + SGST)</option>
                            <option value="out_state">Out-State (IGST)</option>
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
                            <th width="10%">Rate</th>
                            <th width="12%">Amount</th>
                            <th width="8%">Tax %</th>
                            <th width="10%" class="tax-col-in">CGST</th>
                            <th width="10%" class="tax-col-in">SGST</th>
                            <th width="10%" class="tax-col-out d-none">IGST</th>
                            <th width="12%">Total</th>
                            <th width="5%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Row Template -->
                        <tr class="inv-row">
                            <td class="ps-3">
                                <input type="hidden" name="item_id[]" class="item_id">
                                <input type="text" name="item_name[]"
                                    class="form-control form-control-sm item-autocomplete" placeholder="Type item..."
                                    list="itemList">
                                <datalist id="itemList">
                                    <?php foreach ($data['items'] as $it): ?>
                                        <option value="<?php echo htmlspecialchars($it->name); ?>"
                                            data-price="<?php echo $it->sale_price; ?>"
                                            data-tax="<?php echo $it->tax_rate; ?>" data-id="<?php echo $it->id; ?>">
                                        <?php endforeach; ?>
                                </datalist>
                            </td>
                            <td><input type="number" name="quantity[]" step="0.01" class="form-control form-control-sm text-end qty"
                                    value="1" oninput="calcRow(this)"></td>
                            <td><input type="number" name="rate[]" step="0.01"
                                    class="form-control form-control-sm text-end rate" value="0.00"
                                    oninput="calcRow(this)"></td>
                            <td><input type="number" name="amount[]" step="0.01"
                                    class="form-control form-control-sm text-end bg-light amt" readonly></td>
                            <td><input type="number" name="tax_percent[]" step="0.01"
                                    class="form-control form-control-sm text-end tax_p" value="18"
                                    oninput="calcRow(this)"></td>

                            <!-- CGST -->
                            <td class="tax-col-in">
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control form-control-sm text-end bg-light cgst_amt"
                                        readonly value="0.00">
                                </div>
                            </td>
                            <!-- SGST -->
                            <td class="tax-col-in">
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control form-control-sm text-end bg-light sgst_amt"
                                        readonly value="0.00">
                                </div>
                            </td>
                            <!-- IGST -->
                            <td class="tax-col-out d-none">
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control form-control-sm text-end bg-light igst_amt"
                                        readonly value="0.00">
                                </div>
                            </td>

                            <td><input type="number" name="row_total[]" step="0.01"
                                    class="form-control form-control-sm text-end bg-light fw-bold total" readonly></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger border-0"
                                    onclick="removeRow(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-white border-top-0">
                        <tr>
                            <td colspan="9" class="p-3">
                                <button type="button" class="btn btn-sm btn-primary" onclick="addInvRow()">
                                    <i class="fas fa-plus me-2"></i> Add Item
                                </button>
                            </td>
                        </tr>
                        <tr class="text-muted text-end">
                            <td colspan="7" class="border-0">Taxable Amount</td>
                            <td class="border-0 fw-bold" id="sumTaxable">0.00</td>
                            <td class="border-0"></td>
                        </tr>
                        <tr class="text-muted text-end">
                            <td colspan="7" class="border-0">Total Tax</td>
                            <td class="border-0 fw-bold" id="sumTax">0.00</td>
                            <td class="border-0"></td>
                        </tr>
                        <tr class="text-primary h5 text-end">
                            <td colspan="7" class="border-0 fw-bold">Grand Total</td>
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
                        <label class="form-label small fw-bold text-muted">Terms & Conditions</label>
                        <textarea name="terms_conditions" class="form-control" rows="3"
                            placeholder="Enter terms and conditions..."></textarea>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <input type="hidden" name="customer_name" id="customerName">
                    <button type="submit" class="btn btn-primary px-5 py-3 w-100 shadow-sm fw-bold">
                        <i class="fas fa-save me-2"></i> Save Invoice
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
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
    document.querySelector('select[name="customer_ledger_id"]').addEventListener('change', function (e) {
        let text = e.target.options[e.target.selectedIndex].text;
        document.getElementById('customerName').value = text;
    });

    function toggleTaxColumns() {
        var taxType = document.getElementById('taxType').value;
        var inCols = document.querySelectorAll('.tax-col-in');
        var outCols = document.querySelectorAll('.tax-col-out');

        if (taxType === 'in_state') {
            inCols.forEach(el => el.classList.remove('d-none'));
            outCols.forEach(el => el.classList.add('d-none'));
        } else {
            inCols.forEach(el => el.classList.add('d-none'));
            outCols.forEach(el => el.classList.remove('d-none'));
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