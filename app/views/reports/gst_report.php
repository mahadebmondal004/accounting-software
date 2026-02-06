<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<style>
    @media print {
        @page {
            size: landscape;
            margin: 10mm;
        }

        body {
            background: white !important;
            font-family: serif;
        }

        #sidebarMenu,
        .navbar,
        .navbar-neu,
        .sidebar-neu,
        .btn,
        form,
        footer,
        .no-print {
            display: none !important;
        }

        .container-fluid,
        .row,
        .col-md-9,
        .col-lg-10,
        main,
        .ms-sm-auto {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            flex: 0 0 100% !important;
            max-width: 100% !important;
            display: block !important;
            position: static !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .card-body {
            padding: 0 !important;
        }

        .print-header {
            display: block !important;
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        .table th,
        .table td {
            border: 1px solid #ddd !important;
            padding: 8px !important;
            font-size: 12pt;
        }

        .table thead th {
            background-color: #f0f0f0 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color: #000 !important;
        }

        /* Force background print for inline styled rows */
        .table tr[style*="background-color"] {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .badge {
            border: 1px solid #000;
            color: #000 !important;
            background: none !important;
            padding: 2px 5px;
        }

        a {
            text-decoration: none !important;
            color: #000 !important;
        }

        /* Tabs Printing */
        .nav-tabs {
            display: none !important;
        }

        .tab-content {
            border: none !important;
        }

        .tab-pane {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
            margin-bottom: 30px;
        }
    }

    .print-header {
        display: none;
    }
</style>

<div class="print-header">
    <h2><?php echo $_SESSION['company_name'] ?? 'Company Name'; ?></h2>
    <h4>GST Report</h4>
    <p>From: <strong><?php echo date('d-M-Y', strtotime($data['start_date'])); ?></strong> To:
        <strong><?php echo date('d-M-Y', strtotime($data['end_date'])); ?></strong>
    </p>
</div>

<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h4><i class="fas fa-file-invoice text-primary"></i> GST Tax Report</h4>

    <form class="d-flex align-items-center" method="get">
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

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Tax Summary Input (Purchases) vs Output (Sales)</div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tax Ledger</th>
                            <th class="text-end">Total Debit (Input Tax Claimed)</th>
                            <th class="text-end">Total Credit (Output Tax Collected)</th>
                            <th class="text-end">Net Payable / (Refundable)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_dr = 0;
                        $total_cr = 0;
                        if (empty($data['tax_rows'])): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">No Tax transactions found.</td>
                            </tr>
                        <?php else:
                            foreach ($data['tax_rows'] as $row):
                                $net = $row->total_credit - $row->total_debit; // Output - Input
                                $total_dr += $row->total_debit;
                                $total_cr += $row->total_credit;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row->name); ?></td>
                                    <td class="text-end"><?php echo number_format($row->total_debit, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($row->total_credit, 2); ?></td>
                                    <td class="text-end fw-bold <?php echo ($net > 0) ? 'text-danger' : 'text-success'; ?>">
                                        <?php echo number_format($net, 2); ?>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <td>Total</td>
                            <td class="text-end"><?php echo number_format($total_dr, 2); ?></td>
                            <td class="text-end"><?php echo number_format($total_cr, 2); ?></td>
                            <td class="text-end"><?php echo number_format($total_cr - $total_dr, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>


<div class="row mt-4">
    <div class="col-12">
        <ul class="nav nav-tabs" id="gstTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="gstr1-tab" data-bs-toggle="tab" href="#gstr1" role="tab">GSTR-1 (Sales
                    Output)</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="gstr2-tab" data-bs-toggle="tab" href="#gstr2" role="tab">GSTR-2 (Purchase
                    Input)</a>
            </li>
        </ul>
        <div class="tab-content border border-top-0 p-3 bg-white" id="gstTabsContent">
            <!-- GSTR-1 -->
            <div class="tab-pane fade show active" id="gstr1" role="tabpanel">
                <h5 class="d-none d-print-block mb-3">GSTR-1 (Sales Output)</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-sm align-middle">
                        <!-- ... -->

                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Inv #</th>
                                <th>Customer</th>
                                <th>Item</th>
                                <th class="text-end">Taxable</th>
                                <th class="text-end">CGST</th>
                                <th class="text-end">SGST</th>
                                <th class="text-end">IGST</th>
                                <th class="text-end">Total Tax</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['gstr1'])): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No Sales found.</td>
                                </tr>
                            <?php else:
                                $gt_taxable = 0;
                                $gt_cgst = 0;
                                $gt_sgst = 0;
                                $gt_igst = 0;
                                $gt_total = 0;
                                foreach ($data['gstr1'] as $row):
                                    $gt_taxable += $row->taxable_amount;
                                    $gt_cgst += $row->cgst_amount;
                                    $gt_sgst += $row->sgst_amount;
                                    $gt_igst += $row->igst_amount;
                                    $gt_total += $row->tax_amount;
                                    ?>
                                    <tr>
                                        <td><?php echo date('d-M-Y', strtotime($row->voucher_date)); ?></td>
                                        <td><?php echo htmlspecialchars($row->voucher_number); ?></td>
                                        <td><?php echo htmlspecialchars($row->party_name); ?></td>
                                        <td><?php echo htmlspecialchars($row->item_name); ?></td>
                                        <td class="text-end"><?php echo number_format($row->taxable_amount, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row->cgst_amount, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row->sgst_amount, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row->igst_amount, 2); ?></td>
                                        <td class="text-end fw-bold"><?php echo number_format($row->tax_amount, 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="table-dark fw-bold">
                                    <td colspan="4">Total</td>
                                    <td class="text-end"><?php echo number_format($gt_taxable, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($gt_cgst, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($gt_sgst, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($gt_igst, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($gt_total, 2); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- GSTR-2 -->
            <div class="tab-pane fade" id="gstr2" role="tabpanel">
                <h5 class="d-none d-print-block mb-3">GSTR-2 (Purchase Input)</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Bill #</th>
                                <th>Supplier</th>
                                <th>Item</th>
                                <th class="text-end">Taxable</th>
                                <th class="text-end">CGST</th>
                                <th class="text-end">SGST</th>
                                <th class="text-end">IGST</th>
                                <th class="text-end">Total Tax</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['gstr2'])): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No Purchases found.</td>
                                </tr>
                            <?php else:
                                $gt_taxable = 0;
                                $gt_cgst = 0;
                                $gt_sgst = 0;
                                $gt_igst = 0;
                                $gt_total = 0;
                                foreach ($data['gstr2'] as $row):
                                    $gt_taxable += $row->taxable_amount;
                                    $gt_cgst += $row->cgst_amount;
                                    $gt_sgst += $row->sgst_amount;
                                    $gt_igst += $row->igst_amount;
                                    $gt_total += $row->tax_amount;
                                    ?>
                                    <tr>
                                        <td><?php echo date('d-M-Y', strtotime($row->voucher_date)); ?></td>
                                        <td><?php echo htmlspecialchars($row->voucher_number); ?></td>
                                        <td><?php echo htmlspecialchars($row->party_name); ?></td>
                                        <td><?php echo htmlspecialchars($row->item_name); ?></td>
                                        <td class="text-end"><?php echo number_format($row->taxable_amount, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row->cgst_amount, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row->sgst_amount, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row->igst_amount, 2); ?></td>
                                        <td class="text-end fw-bold"><?php echo number_format($row->tax_amount, 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="fw-bold" style="background-color: #343a40; color: white;">
                                    <td colspan="4">Total</td>
                                    <td class="text-end"><?php echo number_format($gt_taxable, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($gt_cgst, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($gt_sgst, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($gt_igst, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($gt_total, 2); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>