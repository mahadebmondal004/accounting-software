<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<style>
    @media print {
        @page {
            size: A4;
            margin: 10mm;
        }

        body {
            background: white !important;
            font-family: serif;
        }

        /* Hide non-printable elements */
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

        /* Layout adjustments */
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
            /* Reset any positioning */
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .card-body {
            padding: 0 !important;
        }

        /* Print Header */
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
            color: #000 !important;
        }

        /* Undo badge styles */
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
    }

    .print-header {
        display: none;
    }
</style>

<!-- Print Header Overlay -->
<div class="print-header">
    <h2><?php echo $_SESSION['company_name'] ?? 'Company Name'; ?></h2>
    <h4>Day Book Report</h4>
    <p>Date: <strong><?php echo date('d-M-Y', strtotime($data['date'])); ?></strong></p>
</div>

<div class="d-flex justify-content-between align-items-center mb-3 no-print">
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