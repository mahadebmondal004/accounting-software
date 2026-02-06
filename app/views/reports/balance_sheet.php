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
    <h4>Balance Sheet</h4>
    <p>As of: <strong><?php echo date('d-M-Y', strtotime($data['date'])); ?></strong></p>
</div>

<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h4><i class="fas fa-landmark text-primary"></i> Balance Sheet</h4>

    <form class="d-flex align-items-center" method="get">
        <label class="me-2 fw-bold">As Of:</label>
        <input type="date" name="date" class="form-control form-control-sm me-2" value="<?php echo $data['date']; ?>">

        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="window.print()"><i
                class="fas fa-print"></i></button>
    </form>
</div>

<div class="row g-4">
    <!-- Liabilities & Equity -->
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0">Liabilities & Equity</h6>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Capital Account</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['equity'] as $e): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($e->name); ?></td>
                                <td class="text-end"><?php echo number_format(abs($e->net_balance), 2); ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Net Profit is added to Equity side -->
                        <tr>
                            <td
                                class="fw-bold <?php echo ($data['current_profit'] >= 0) ? 'text-success' : 'text-danger'; ?>">
                                Current Period Net Profit/Loss
                            </td>
                            <td class="text-end fw-bold"><?php echo number_format($data['current_profit'], 2); ?></td>
                        </tr>
                    </tbody>

                    <thead class="table-light">
                        <tr>
                            <th>Liabilities</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['liabilities'] as $l): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($l->name); ?></td>
                                <td class="text-end"><?php echo number_format(abs($l->net_balance), 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                    <tfoot class="table-dark">
                        <tr>
                            <td>Total</td>
                            <td class="text-end">
                                <?php echo number_format($data['total_equity'] + $data['total_liabilities'] + $data['current_profit'], 2); ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Assets -->
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">Assets</h6>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Assets</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['assets'] as $a): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($a->name); ?></td>
                                <td class="text-end"><?php echo number_format($a->net_balance, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <td>Total</td>
                            <td class="text-end"><?php echo number_format($data['total_assets'], 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>