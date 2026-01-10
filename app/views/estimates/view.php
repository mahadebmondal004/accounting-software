<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 fade-in-up">
    <div>
        <a href="<?php echo APP_URL; ?>/estimates/index" class="btn-neu btn-neu-sm mb-2"><i
                class="fas fa-arrow-left"></i> Back</a>
        <h1 class="h3 mb-0 text-gradient">Estimate #
            <?php echo $data['estimate']->estimate_number; ?>
        </h1>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn-neu btn-neu-primary"><i class="fas fa-print me-2"></i>
            Print</button>
        <?php if ($data['estimate']->status != 'Converted'): ?>
            <!-- In Future: Link to a conversion action -->
            <button class="btn-neu btn-neu-primary text-success" disabled title="Coming Soon">
                <i class="fas fa-check-circle me-2"></i> Convert to Invoice
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="neu-card p-5 fade-in-up" id="printArea">
    <!-- Header -->
    <div class="row mb-5 border-bottom pb-4">
        <div class="col-6">
            <h4 class="fw-bold text-primary">
                <?php echo $data['company']->name; ?>
            </h4>
            <p class="mb-0 text-muted small">
                <?php echo $data['company']->address; ?><br>
                <?php echo $data['company']->city; ?>,
                <?php echo $data['company']->state; ?> -
                <?php echo $data['company']->pincode; ?><br>
                Phone:
                <?php echo $data['company']->phone; ?><br>
                Email:
                <?php echo $data['company']->email; ?>
            </p>
        </div>
        <div class="col-6 text-end">
            <h2 class="display-6 text-uppercase text-secondary opacity-25 fw-bold">Estimate</h2>
            <div class="mt-3">
                <small class="text-muted d-block uppercase fw-bold">Estimate Number</small>
                <span class="h5">
                    <?php echo $data['estimate']->estimate_number; ?>
                </span>
            </div>
            <div class="mt-2">
                <small class="text-muted d-block uppercase fw-bold">Date</small>
                <span>
                    <?php echo date('d M Y', strtotime($data['estimate']->estimate_date)); ?>
                </span>
            </div>
            <div class="mt-2 text-danger">
                <small class="text-muted d-block uppercase fw-bold">Valid Until</small>
                <span>
                    <?php echo date('d M Y', strtotime($data['estimate']->expiry_date)); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Bill To -->
    <div class="row mb-5">
        <div class="col-12">
            <small class="text-muted text-uppercase fw-bold">Estimate For</small>
            <h5 class="fw-bold mb-1">
                <?php echo $data['estimate']->customer_name; ?>
            </h5>
            <p class="mb-0 text-muted">
                <?php echo $data['estimate']->customer_address ?? 'Address not available'; ?>
            </p>
        </div>
    </div>

    <!-- Items -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered border-light">
            <thead class="bg-light">
                <tr>
                    <th scope="col" class="py-3 ps-4">Description</th>
                    <th scope="col" class="py-3 text-end" width="10%">Qty</th>
                    <th scope="col" class="py-3 text-end" width="15%">Rate</th>
                    <th scope="col" class="py-3 text-end" width="10%">Tax %</th>
                    <th scope="col" class="py-3 text-end pe-4" width="20%">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['items'] as $item): ?>
                    <tr>
                        <td class="ps-4 py-3 fw-bold text-secondary">
                            <?php echo $item->item_name; ?>
                        </td>
                        <td class="text-end py-3">
                            <?php echo $item->quantity; ?>
                        </td>
                        <td class="text-end py-3">
                            <?php echo number_format($item->rate, 2); ?>
                        </td>
                        <td class="text-end py-3">
                            <?php echo $item->tax_percent; ?>%
                        </td>
                        <td class="text-end pe-4 py-3 fw-bold">
                            <?php echo number_format($item->total, 2); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="border-top-2">
                <tr>
                    <td colspan="4" class="text-end pt-4 pb-1 small fw-bold text-uppercase text-muted">Total Amount</td>
                    <td class="text-end pt-4 pb-1 pe-4 h4 fw-bold text-primary">₹
                        <?php echo number_format($data['estimate']->total_amount, 2); ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Notes -->
    <?php if (!empty($data['estimate']->notes)): ?>
        <div class="bg-light p-4 rounded-3 border border-light">
            <small class="d-block text-uppercase fw-bold text-muted mb-2">Notes / Terms</small>
            <p class="mb-0 text-secondary">
                <?php echo nl2br($data['estimate']->notes); ?>
            </p>
        </div>
    <?php endif; ?>

    <!-- Signature -->
    <div class="row mt-5 pt-5">
        <div class="col-6 offset-6 text-center">
            <div class="border-top border-dark w-75 ms-auto"></div>
            <small class="text-muted d-block mt-2">Authorized Signature</small>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }

        #printArea,
        #printArea * {
            visibility: visible;
        }

        #printArea {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            box-shadow: none;
        }
    }
</style>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>