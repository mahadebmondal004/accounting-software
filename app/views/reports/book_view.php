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

<?php
$ledger_name = '';
foreach ($data['ledgers'] as $l) {
    if ($l->id == $data['selected_ledger']) {
        $ledger_name = $l->name;
        break;
    }
}
?>

<div class="print-header">
    <h2><?php echo $_SESSION['company_name'] ?? 'Company Name'; ?></h2>
    <h4><?php echo ucfirst($data['type']); ?> Book: <?php echo htmlspecialchars($ledger_name); ?></h4>
    <p>From: <strong><?php echo date('d-M-Y', strtotime($data['start_date'])); ?></strong> To:
        <strong><?php echo date('d-M-Y', strtotime($data['end_date'])); ?></strong></p>
</div>

<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h4><i class="fas fa-book text-success"></i> <?php echo ucfirst($data['type']); ?> Book</h4>

    <form class="d-flex align-items-center" method="get">
        <label class="me-2 fw-bold">Select Account:</label>
        <select name="ledger_id" class="form-select form-select-sm me-2" onchange="this.form.submit()">
            <?php foreach ($data['ledgers'] as $l): ?>
                <option value="<?php echo $l->id; ?>" <?php echo ($data['selected_ledger'] == $l->id) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($l->name); ?>
                </option>
            <?php endforeach; ?>
        </select>

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

<!-- Reusing Ledger Statement Logic visually -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Voucher</th>
                    <th>Description</th>
                    <th class="text-end text-success">Receipt (Dr)</th>
                    <th class="text-end text-danger">Payment (Cr)</th>
                    <th class="text-end">Balance</th>
                </tr>
            </thead>
            <tbody>
                <tr class="table-warning fw-bold">
                    <td><?php echo $data['start_date']; ?></td>
                    <td>-</td>
                    <td>Opening Balance</td>
                    <td class="text-end"><?php echo ($data['opening'] > 0) ? number_format($data['opening'], 2) : ''; ?>
                    </td>
                    <td class="text-end">
                        <?php echo ($data['opening'] < 0) ? number_format(abs($data['opening']), 2) : ''; ?>
                    </td>
                    <td class="text-end"><?php echo number_format($data['opening'], 2); ?></td>
                </tr>

                <?php
                $running_balance = $data['opening'];
                foreach ($data['transactions'] as $t):
                    $net_change = $t->debit - $t->credit;
                    $running_balance += $net_change;
                    ?>
                    <tr>
                        <td><?php echo $t->voucher_date; ?></td>
                        <td><?php echo $t->voucher_number; ?></td>
                        <td>
                            <?php echo htmlspecialchars($t->narration); ?>
                            <small class="text-muted d-block"><?php echo htmlspecialchars($t->description); ?></small>
                        </td>
                        <td class="text-end text-success"><?php echo ($t->debit > 0) ? number_format($t->debit, 2) : '-'; ?>
                        </td>
                        <td class="text-end text-danger">
                            <?php echo ($t->credit > 0) ? number_format($t->credit, 2) : '-'; ?>
                        </td>
                        <td class="text-end fw-bold"><?php echo number_format($running_balance, 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <td colspan="5" class="text-end fw-bold">Closing Balance:</td>
                    <td class="text-end fw-bold"><?php echo number_format($running_balance, 2); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>