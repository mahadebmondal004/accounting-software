<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="card shadow-sm border-0 fade-in-up">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-shopping-cart me-2"></i> New Purchase Bill</h5>
        <div class="text-muted small fw-bold badge bg-light text-dark border px-3 py-2">
            Date: <?php echo date('d-M-Y'); ?>
        </div>
    </div>
    <div class="card-body p-4">
        <form action="<?php echo APP_URL; ?>/purchases/create" method="post">
            <?php echo csrf_field(); ?>
            <div class="card bg-light border-0 p-3 mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Bill / Voucher Number</label>
                        <input type="text" name="invoice_number" class="form-control fw-bold"
                            value="<?php echo $data['invoice_number']; ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Date</label>
                        <input type="date" name="invoice_date" class="form-control"
                            value="<?php echo $data['invoice_date']; ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Supplier (Creditor)</label>
                        <select name="supplier_ledger_id" class="form-select select2-basic" required autofocus>
                            <option value="">Select Supplier...</option>
                            <?php foreach ($data['suppliers'] as $c): ?>
                                <?php if ($c->type == 'Supplier' || $c->nature == 'Liabilities'): ?>
                                    <option value="<?php echo $c->id; ?>"><?php echo htmlspecialchars($c->name); ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card bg-light border-0 p-3 mb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Purchase Ledger (Debit Expense)</label>
                        <select name="purchase_ledger_id" class="form-select" required>
                            <?php foreach ($data['purchase_ledgers'] as $pl): ?>
                                <option value="<?php echo $pl->id; ?>"><?php echo $pl->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Tax Ledger (Debit Input Tax)</label>
                        <select name="tax_ledger_id" class="form-select" required>
                            <?php foreach ($data['tax_ledgers'] as $tl): ?>
                                <option value="<?php echo $tl->id; ?>"><?php echo $tl->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle mb-0" id="invTable">
                    <thead class="bg-light text-secondary small text-uppercase">
                        <tr>
                            <th width="30%" class="ps-3">Item / Product</th>
                            <th width="10%">Qty</th>
                            <th width="15%">Cost Rate</th>
                            <th width="15%">Amount</th>
                            <th width="10%">Tax %</th>
                            <th width="15%">Total</th>
                            <th width="5%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="inv-row">
                            <td class="ps-3">
                                <input type="hidden" name="item_id[]" class="item_id">
                                <input type="text" name="item_name[]"
                                    class="form-control form-control-sm item-autocomplete" placeholder="Type item..."
                                    list="itemList">
                                <datalist id="itemList">
                                    <?php foreach ($data['items'] as $it): ?>
                                        <option value="<?php echo htmlspecialchars($it->name); ?>"
                                            data-price="<?php echo $it->purchase_price; ?>"
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
                            <td><input type="number" name="row_total[]"
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
                            <td colspan="7" class="p-3">
                                <button type="button" class="btn btn-sm btn-primary" onclick="addInvRow()">
                                    <i class="fas fa-plus me-2"></i> Add Item
                                </button>
                            </td>
                        </tr>
                        <tr class="text-muted text-end">
                            <td colspan="5" class="border-0">Taxable Amount</td>
                            <td class="border-0 fw-bold" id="sumTaxable">0.00</td>
                            <td class="border-0"></td>
                        </tr>
                        <tr class="text-muted text-end">
                            <td colspan="5" class="border-0">Total Tax</td>
                            <td class="border-0 fw-bold" id="sumTax">0.00</td>
                            <td class="border-0"></td>
                        </tr>
                        <tr class="text-primary h5 text-end">
                            <td colspan="5" class="border-0 fw-bold">Total Payable</td>
                            <td class="border-0 fw-bold" id="grandTotal">0.00</td>
                            <td class="border-0"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <input type="hidden" name="total_taxable" id="inpTaxable" value="0">
            <input type="hidden" name="total_tax" id="inpTax" value="0">
            <input type="hidden" name="total_payable" id="inpTotal" value="0">

            <div class="text-end mt-4">
                <input type="hidden" name="supplier_name" id="supplierName">
                <button type="submit" class="btn btn-primary px-5 py-3 shadow-sm fw-bold">
                    <i class="fas fa-save me-2"></i> Save Purchase Bill
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('item-autocomplete')) {
            var val = e.target.value;
            var list = document.getElementById('itemList');
            var options = list.childNodes;
            for (var i = 0; i < options.length; i++) {
                if (options[i].value === val) {
                    var row = e.target.closest('tr');
                    var price = options[i].getAttribute('data-price');
                    if (price == 0) price = '';
                    row.querySelector('.rate').value = price;
                    row.querySelector('.tax_p').value = options[i].getAttribute('data-tax');
                    row.querySelector('.item_id').value = options[i].getAttribute('data-id');
                    calcRow(e.target);
                    break;
                }
            }
        }
    });

    document.querySelector('select[name="supplier_ledger_id"]').addEventListener('change', function (e) {
        let text = e.target.options[e.target.selectedIndex].text;
        document.getElementById('supplierName').value = text;
    });

    function calcRow(el) {
        let row = el.closest('tr');
        let qty = parseFloat(row.querySelector('.qty').value) || 0;
        let rate = parseFloat(row.querySelector('.rate').value) || 0;
        let taxP = parseFloat(row.querySelector('.tax_p').value) || 0;

        let amount = qty * rate;
        let taxAmt = amount * (taxP / 100);
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
            else if (i.classList.contains('tax_p')) i.value = 18;
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