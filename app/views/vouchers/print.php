<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher - <?php echo $data['voucher']->voucher_number; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }

            body {
                margin: 0;
                padding: 20px;
            }
        }

        body {
            font-family: 'Arial', sans-serif;
        }

        .voucher-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            border: 2px solid #333;
        }

        .company-header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .voucher-title {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 15px 0;
        }

        .voucher-info {
            margin: 20px 0;
        }

        .entries-table {
            margin: 20px 0;
        }

        .entries-table th {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
        }

        .entries-table td {
            border: 1px solid #dee2e6;
            padding: 10px;
        }

        .signature-section {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            text-align: center;
            width: 200px;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
        }
    </style>
</head>

<body>
    <div class="voucher-container">
        <!-- Company Header -->
        <div class="company-header">
            <h2><?php echo htmlspecialchars($data['company']->name); ?></h2>
            <?php if ($data['company']->address): ?>
                <p class="mb-1"><?php echo htmlspecialchars($data['company']->address); ?></p>
            <?php endif; ?>
            <?php if ($data['company']->city || $data['company']->state): ?>
                <p class="mb-1">
                    <?php echo htmlspecialchars($data['company']->city ?? ''); ?>
                    <?php echo $data['company']->state ? ', ' . htmlspecialchars($data['company']->state) : ''; ?>
                    <?php echo $data['company']->pincode ? ' - ' . htmlspecialchars($data['company']->pincode) : ''; ?>
                </p>
            <?php endif; ?>
            <?php if ($data['company']->phone || $data['company']->email): ?>
                <p class="mb-0">
                    <?php if ($data['company']->phone): ?>
                        Phone: <?php echo htmlspecialchars($data['company']->phone); ?>
                    <?php endif; ?>
                    <?php if ($data['company']->email): ?>
                        | Email: <?php echo htmlspecialchars($data['company']->email); ?>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
            <?php if ($data['company']->gstin): ?>
                <p class="mb-0">GSTIN: <?php echo htmlspecialchars($data['company']->gstin); ?></p>
            <?php endif; ?>
        </div>

        <!-- Voucher Title -->
        <div class="voucher-title text-center">
            <?php echo $data['voucher']->voucher_type; ?> VOUCHER
        </div>

        <!-- Voucher Info -->
        <div class="voucher-info">
            <div class="row">
                <div class="col-6">
                    <strong>Voucher No:</strong> <?php echo htmlspecialchars($data['voucher']->voucher_number); ?>
                </div>
                <div class="col-6 text-end">
                    <strong>Date:</strong> <?php echo date('d-M-Y', strtotime($data['voucher']->voucher_date)); ?>
                </div>
            </div>
        </div>

        <!-- Entries Table -->
        <table class="table entries-table">
            <thead>
                <tr>
                    <th width="50%">Particulars</th>
                    <th width="25%" class="text-end">Debit (₹)</th>
                    <th width="25%" class="text-end">Credit (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalDebit = 0;
                $totalCredit = 0;
                foreach ($data['entries'] as $entry):
                    $totalDebit += $entry->debit;
                    $totalCredit += $entry->credit;
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($entry->ledger_name); ?></strong>
                            <?php if ($entry->ledger_code): ?>
                                <br><small>(<?php echo htmlspecialchars($entry->ledger_code); ?>)</small>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php echo $entry->debit > 0 ? number_format($entry->debit, 2) : '-'; ?>
                        </td>
                        <td class="text-end">
                            <?php echo $entry->credit > 0 ? number_format($entry->credit, 2) : '-'; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Total</th>
                    <th class="text-end">₹ <?php echo number_format($totalDebit, 2); ?></th>
                    <th class="text-end">₹ <?php echo number_format($totalCredit, 2); ?></th>
                </tr>
            </tfoot>
        </table>

        <!-- Narration -->
        <?php if ($data['voucher']->narration): ?>
            <div class="mt-3">
                <strong>Narration:</strong> <?php echo htmlspecialchars($data['voucher']->narration); ?>
            </div>
        <?php endif; ?>

        <!-- Amount in Words -->
        <div class="mt-3">
            <strong>Amount in Words:</strong>
            <em><?php echo ucwords(numberToWords($totalDebit)); ?> Only</em>
        </div>

        <!-- Signatures -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">Prepared By</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Checked By</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Authorized Signatory</div>
            </div>
        </div>

        <!-- Print Button -->
        <div class="text-center mt-4 no-print">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Print Voucher
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                Close
            </button>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/your-code.js"></script>
</body>

</html>

<?php
// Helper function to convert number to words
function numberToWords($number)
{
    $number = (int) $number;
    $words = array(
        0 => '',
        1 => 'one',
        2 => 'two',
        3 => 'three',
        4 => 'four',
        5 => 'five',
        6 => 'six',
        7 => 'seven',
        8 => 'eight',
        9 => 'nine',
        10 => 'ten',
        11 => 'eleven',
        12 => 'twelve',
        13 => 'thirteen',
        14 => 'fourteen',
        15 => 'fifteen',
        16 => 'sixteen',
        17 => 'seventeen',
        18 => 'eighteen',
        19 => 'nineteen',
        20 => 'twenty',
        30 => 'thirty',
        40 => 'forty',
        50 => 'fifty',
        60 => 'sixty',
        70 => 'seventy',
        80 => 'eighty',
        90 => 'ninety'
    );

    if ($number < 21) {
        return $words[$number];
    } elseif ($number < 100) {
        $tens = ((int) ($number / 10)) * 10;
        $units = $number % 10;
        return $words[$tens] . ($units ? ' ' . $words[$units] : '');
    } elseif ($number < 1000) {
        $hundreds = (int) ($number / 100);
        $remainder = $number % 100;
        return $words[$hundreds] . ' hundred' . ($remainder ? ' and ' . numberToWords($remainder) : '');
    } elseif ($number < 100000) {
        $thousands = (int) ($number / 1000);
        $remainder = $number % 1000;
        return numberToWords($thousands) . ' thousand' . ($remainder ? ' ' . numberToWords($remainder) : '');
    } elseif ($number < 10000000) {
        $lakhs = (int) ($number / 100000);
        $remainder = $number % 100000;
        return numberToWords($lakhs) . ' lakh' . ($remainder ? ' ' . numberToWords($remainder) : '');
    } else {
        $crores = (int) ($number / 10000000);
        $remainder = $number % 10000000;
        return numberToWords($crores) . ' crore' . ($remainder ? ' ' . numberToWords($remainder) : '');
    }
}
?>