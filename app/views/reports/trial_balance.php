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
            color: #000 !important;
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
    }

    .print-header {
        display: none;
    }
</style>

<div class="print-header">
    <h2><?php echo $_SESSION['company_name'] ?? 'Company Name'; ?></h2>
    <h4>Trial Balance Report</h4>
    <p>As of: <strong><?php echo date('d-M-Y', strtotime($data['date'])); ?></strong></p>
</div>

<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h4><i class="fas fa-balance-scale text-info"></i> Trial Balance</h4>

    <form class="d-flex align-items-center" method="get">
        <label class="me-2 fw-bold">As Of:</label>
        <input type="date" name="date" class="form-control form-control-sm me-2" value="<?php echo $data['date']; ?>">
        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="window.print()"><i
                class="fas fa-print"></i></button>
    </form>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light text-uppercase small">
                    <tr>
                        <th style="width: 50%">Particulars</th>
                        <th style="width: 25%" class="text-end">Debit (₹)</th>
                        <th style="width: 25%" class="text-end">Credit (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['records'])): ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">No records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['records'] as $row): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($row['name']); ?></span>
                                    <br><small
                                        class="text-muted fst-italic"><?php echo htmlspecialchars($row['group']); ?></small>
                                </td>
                                <td class="text-end"><?php echo ($row['debit'] > 0) ? number_format($row['debit'], 2) : ''; ?>
                                </td>
                                <td class="text-end"><?php echo ($row['credit'] > 0) ? number_format($row['credit'], 2) : ''; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot class="fw-bold table-active">
                    <tr>
                        <td class="text-uppercase">Total</td>
                        <td class="text-end text-primary"><?php echo number_format($data['totalDr'], 2); ?></td>
                        <td class="text-end text-primary"><?php echo number_format($data['totalCr'], 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <?php if (abs($data['totalDr'] - $data['totalCr']) > 0.01): ?>
            <div class="alert alert-danger mt-3 text-center">
                <strong>Difference:</strong> <?php echo number_format(abs($data['totalDr'] - $data['totalCr']), 2); ?>
                <br> The accounts are not balanced! Check voucher integrity.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>