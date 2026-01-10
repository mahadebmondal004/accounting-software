<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="card shadow-sm border-0 fade-in-up">
    <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-file-alt me-2"></i> New Estimate / Quotation</h5>
    </div>
    <div class="card-body p-4">
        <form action="<?php echo APP_URL; ?>/estimates/create" method="post">
            <?php echo csrf_field(); ?>
            <div class="card bg-light border-0 p-3 mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Estimate Number</label>
                        <input type="text" name="estimate_number" class="form-control fw-bold"
                            value="<?php echo $data['estimate_number']; ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Date</label>
                        <input type="date" name="estimate_date" class="form-control"
                            value="<?php echo $data['estimate_date']; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Valid Until</label>
                        <input type="date" name="expiry_date" class="form-control"
                            value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Customer</label>
                        <select name="customer_ledger_id" class="form-select select2-basic" required autofocus>
                            <option value="">Select Customer...</option>
                            <?php foreach ($data['customers'] as $c): ?>
                                <?php if ($c->type == 'Customer' || $c->nature == 'Assets'): ?>
                                    <option value="<?php echo $c->id; ?>">
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
                            <th width="35%" class="ps-3">Item / Service</th>
                            <th width="10%">Qty</th>
                            <th width="15%">Rate</th>
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
                                    <i class="fas fa-plus me-2"></i> Add Line
                                </button>
                            </td>
                        </tr>
                        <tr class="text-primary h5 text-end">
                            <td colspan="5" class="border-0 fw-bold">Grand Total</td>
                            <td class="border-0 fw-bold" id="grandTotal">0.00</td>
                            <td class="border-0"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <input type="hidden" name="total_payable" id="inpTotal" value="0">

            <div class="row align-items-end">
                <div class="col-md-8">
                    <div class="card p-3 bg-light border-0">
                        <label class="form-label small fw-bold text-muted">Notes / Terms</label>
                        <textarea name="notes" class="form-control" rows="3"
                            placeholder="Validity of offer, Payment terms etc..."></textarea>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <button type="submit" class="btn btn-primary px-5 py-3 w-100 shadow-sm fw-bold">
                        <i class="fas fa-paper-plane me-2"></i> Create Estimate
                    </button>
                </div>
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
                    row.querySelector('.rate').value = options[i].getAttribute('data-price');
                    row.querySelector('.tax_p').value = options[i].getAttribute('data-tax');
                    row.querySelector('.item_id').value = options[i].getAttribute('data-id');
                    calcRow(e.target);
                    break;
                }
            }
        }
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
        let grand = 0;
        document.querySelectorAll('.inv-row').forEach(row => {
            grand += parseFloat(row.querySelector('.total').value) || 0;
        });

        document.getElementById('grandTotal').innerText = grand.toFixed(2);
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