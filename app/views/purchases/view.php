<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 fade-in-up">
    <div>
        <a href="<?php echo APP_URL; ?>/purchases/index" class="btn-neu btn-neu-sm mb-2"><i
                class="fas fa-arrow-left"></i>
            Back</a>
        <h1 class="h3 mb-0 text-gradient">Purchase Bill #
            <?php echo $data['voucher']->voucher_number; ?>
        </h1>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn-neu btn-neu-primary"><i class="fas fa-print me-2"></i>
            Print</button>
    </div>
</div>

<div class="bg-white p-5 fade-in-up" id="printArea">
    <!-- Enhanced Header with Logo -->
    <div class="row mb-4 align-items-start">
        <div class="col-7">
            <?php if (!empty($data['company']->logo_path)): ?>
                <img src="<?php echo APP_URL . '/' . $data['company']->logo_path; ?>" alt="Logo" class="mb-3"
                    style="max-height: 80px; max-width: 200px; object-fit: contain;">
            <?php endif; ?>
            <h4 class="fw-bold mb-1 text-uppercase" style="color: #2c3e50; letter-spacing: 0.5px;">
                <?php echo $data['company']->name; ?>
            </h4>
            <div style="font-size: 0.9rem; color: #555; line-height: 1.5;">
                <?php echo $data['company']->address; ?><br>
                <?php echo $data['company']->city; ?>, <?php echo $data['company']->state; ?> -
                <?php echo $data['company']->pincode; ?><br>
                <span>Phone: <?php echo $data['company']->phone; ?></span> | <span>Email:
                    <?php echo $data['company']->email; ?></span>
            </div>
        </div>
        <div class="col-5 text-end">
            <h1 class="fw-bold text-uppercase mb-3"
                style="font-size: 3rem; color: #eaeaec; letter-spacing: 2px; line-height: 1;">
                PURCHASE
            </h1>
            <table class="table table-bordered mb-0" style="width: 100%; border-color: #dee2e6;">
                <tr>
                    <td class="text-start py-2 px-3 align-middle"
                        style="width: 40%; font-size: 0.8rem; font-weight: bold; color: #6c757d; text-transform: uppercase;">
                        Bill No</td>
                    <td class="text-end py-2 px-3 align-middle fw-bold" style="font-size: 1rem; color: #2c3e50;">
                        <?php echo $data['voucher']->voucher_number; ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-start py-2 px-3 align-middle"
                        style="font-size: 0.8rem; font-weight: bold; color: #6c757d; text-transform: uppercase;">Date
                    </td>
                    <td class="text-end py-2 px-3 align-middle fw-bold" style="font-size: 1rem; color: #2c3e50;">
                        <?php echo date('d M Y', strtotime($data['voucher']->voucher_date)); ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Bill To / Supplier Section -->
    <div class="my-4 border-top border-bottom py-3" style="border-color: #eee !important;">
        <div class="row">
            <div class="col-6">
                <small class="text-uppercase fw-bold text-muted mb-2 d-block"
                    style="font-size: 0.75rem; letter-spacing: 1px;">Supplier:</small>
                <h5 class="fw-bold mb-1" style="color: #2c3e50;"><?php echo $data['supplier']->name; ?></h5>
                <p class="mb-0 text-muted" style="font-size: 0.9rem; max-width: 80%;">
                    <?php echo $data['supplier']->address ?? 'Address not available'; ?>
                </p>
            </div>
            <div class="col-6 text-end">
                <!-- Optional: Add Ship To or Order Details here in future -->
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="table-responsive mb-4">
        <?php
        $show_igst = false;
        foreach ($data['items'] as $item) {
            if ($item->igst_amount > 0) {
                $show_igst = true;
                break;
            }
        }
        ?>
        <table class="table mb-0 border-0" style="border-collapse: separate; border-spacing: 0;">
            <thead style="border-bottom: 2px solid #000;">
                <tr>
                    <th class="py-2 ps-3 border-0 rounded-start"
                        style="font-weight: 600; font-size: 0.85rem; letter-spacing: 0.5px;">ITEM DESCRIPTION</th>
                    <th class="py-2 text-center border-0" width="10%" style="font-weight: 600; font-size: 0.85rem;">QTY
                    </th>
                    <th class="py-2 text-end border-0" width="15%" style="font-weight: 600; font-size: 0.85rem;">RATE
                    </th>
                    <?php if ($show_igst): ?>
                        <th class="py-2 text-center border-0" width="10%" style="font-weight: 600; font-size: 0.85rem;">IGST
                        </th>
                    <?php else: ?>
                        <th class="py-2 text-center border-0" width="10%" style="font-weight: 600; font-size: 0.85rem;">CGST
                        </th>
                        <th class="py-2 text-center border-0" width="10%" style="font-weight: 600; font-size: 0.85rem;">SGST
                        </th>
                    <?php endif; ?>
                    <th class="py-2 text-end pe-3 border-0 rounded-end" width="18%"
                        style="font-weight: 600; font-size: 0.85rem;">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['items'] as $index => $item): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td class="ps-3 py-3 align-middle" style="color: #2c3e50; font-weight: 600;">
                            <?php echo $item->item_name; ?>
                        </td>
                        <td class="text-center py-3 align-middle" style="color: #555;">
                            <?php echo $item->quantity; ?>
                        </td>
                        <td class="text-end py-3 align-middle" style="color: #555;">
                            ₹ <?php echo number_format($item->rate, 2); ?>
                        </td>

                        <?php if ($show_igst): ?>
                            <td class="text-center py-3 align-middle" style="color: #555;">
                                <?php $igst = $item->igst_amount > 0 ? $item->igst_amount : $item->tax_amount; ?>
                                <div style="font-size: 0.8rem;"><?php echo number_format($item->tax_percent, 0); ?>%</div>
                                <small class="text-muted">₹ <?php echo number_format($igst, 2); ?></small>
                            </td>
                        <?php else: ?>
                            <?php
                            $cgst = $item->cgst_amount > 0 ? $item->cgst_amount : ($item->tax_amount / 2);
                            $sgst = $item->sgst_amount > 0 ? $item->sgst_amount : ($item->tax_amount / 2);
                            ?>
                            <td class="text-center py-3 align-middle" style="color: #555;">
                                <div style="font-size: 0.8rem;"><?php echo number_format($item->tax_percent / 2, 1); ?>%</div>
                                <small class="text-muted">₹ <?php echo number_format($cgst, 2); ?></small>
                            </td>
                            <td class="text-center py-3 align-middle" style="color: #555;">
                                <div style="font-size: 0.8rem;"><?php echo number_format($item->tax_percent / 2, 1); ?>%</div>
                                <small class="text-muted">₹ <?php echo number_format($sgst, 2); ?></small>
                            </td>
                        <?php endif; ?>

                        <td class="text-end pe-3 py-3 align-middle fw-bold" style="color: #2c3e50;">
                            ₹ <?php echo number_format($item->total, 2); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer Totals -->
    <div class="row mt-0">
        <div class="col-7">
            <div class="p-3 rounded" style="border: 1px solid #eee;">
                <p class="mb-1 text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">Notes:</p>
                <p class="mb-0 text-muted fst-italic" style="font-size: 0.85rem;">
                    All goods received in good condition. Payment terms as agreed.
                </p>
            </div>
        </div>
        <div class="col-5">
            <table class="table table-borderless mb-0">
                <?php
                $total_taxable = 0;
                $total_tax = 0;
                foreach ($data['items'] as $item) {
                    $total_taxable += $item->amount;
                    $total_tax += $item->tax_amount;
                }
                ?>
                <tr>
                    <td class="text-end text-muted py-1">Taxable Amount:</td>
                    <td class="text-end fw-bold py-1 text-dark" style="font-size: 1rem;">₹
                        <?php echo number_format($total_taxable, 2); ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-end text-muted py-1">Total Tax:</td>
                    <td class="text-end fw-bold py-1 text-dark" style="font-size: 1rem;">₹
                        <?php echo number_format($total_tax, 2); ?>
                    </td>
                </tr>
                <tr class="border-top" style="border-width: 2px !important; border-color: #2c3e50 !important;">
                    <td class="text-end fw-bold py-3 text-uppercase" style="color: #2c3e50; font-size: 1.1rem;">Grand
                        Total:</td>
                    <td class="text-end fw-bold py-3" style="color: #2c3e50; font-size: 1.4rem;">₹
                        <?php echo number_format($data['voucher']->total_amount, 2); ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>


    <!-- Signature Section -->
    <div class="row mt-5">
        <div class="col-6">
            <div class="mt-4 pt-4 border-top border-secondary w-75 mx-auto text-center">
                <small class="fw-bold text-uppercase" style="letter-spacing: 1px;">Received By</small>
            </div>
        </div>
        <div class="col-6">
            <div class="mt-4 pt-4 border-top border-secondary w-75 mx-auto text-center">
                <small class="fw-bold text-uppercase" style="letter-spacing: 1px;">Authorized Signature</small>
            </div>
        </div>
    </div>

    <!-- Final Footer -->
    <div class="row mt-4">
        <div class="col-12 text-center text-muted">
            <p class="small mb-0">This is a system generated document.</p>
        </div>
    </div>
</div>

<style>
    @media print {
        body {
            background-color: white !important;
            -webkit-print-color-adjust: exact;
        }

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
            padding: 0 !important;
            box-shadow: none !important;
        }

        /* Typography sizing for print */
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            margin: 0;
        }

        /* Layout Adjustments */
        .neu-card {
            border: none !important;
        }

        .row {
            margin-left: 0;
            margin-right: 0;
        }

        .col-7,
        .col-5,
        .col-6,
        .col-12 {
            padding-left: 10px;
            padding-right: 10px;
        }

        /* Table Specifics */
        table {
            width: 100%;
        }

        th,
        td {
            padding: 6px 8px !important;
        }

        /* Page Settings */
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        /* Force color printing */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Prevent Page Breaks */
        tr,
        td,
        th {
            page-break-inside: avoid;
        }

        .row {
            page-break-inside: avoid;
        }
    }
</style>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>