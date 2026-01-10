<?php
class ReturnsController extends Controller
{
    private $voucherModel;
    private $invoiceModel;
    private $ledgerModel;
    private $itemModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        if (!isset($_SESSION['user_id']))
            $this->redirect('auth/login');
        if (!isset($_SESSION['company_id']))
            $this->redirect('companies/index');

        $this->voucherModel = $this->model('Voucher');
        $this->invoiceModel = $this->model('Invoice'); // Use existing Invoice Model tables?? 
        // Invoice Model stores into `invoice_details` and `invoice_items` linked to voucher_id.
        // We can REUSE this for Credit Note / Debit Note if we just TREAT them as Vouchers with Items.
        // Yes, Credit Note is just a negative Invoice visually, or positive values with reversed GL.

        $this->ledgerModel = $this->model('Ledger');
        $this->itemModel = $this->model('Item');
    }

    // Sales Return (Credit Note)
    public function sales_return()
    {
        $this->processReturn('Credit Note', 'Sales Return');
    }

    // Purchase Return (Debit Note)
    public function purchase_return()
    {
        $this->processReturn('Debit Note', 'Purchase Return');
    }

    private function processReturn($voucherType, $title)
    {
        $fy_id = $this->voucherModel->checkAndCreateFY($_SESSION['company_id']);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            verify_csrf();
            $input = filter_input_array(INPUT_POST, FILTER_DEFAULT);

            $voucherData = [
                'company_id' => $_SESSION['company_id'],
                'financial_year_id' => $fy_id,
                'voucher_type' => $voucherType,
                'voucher_number' => $input['return_number'],
                'voucher_date' => $input['return_date'],
                'narration' => $title . " against Invoice #" . $input['original_invoice'],
                'total_amount' => $input['total_payable'],
                'created_by' => $_SESSION['user_id']
            ];

            // Accounting Logic for Returns
            // Sales Return (Credit Note):
            // Dr Sales Return Account (or Sales) - Reduces Income
            // Dr Output Tax - Reduces Tax Liability
            // Cr Customer - Reduces Receivable

            // Purchase Return (Debit Note):
            // Dr Vendor - Reduces Payable
            // Cr Purchase Return Account (or Purchase) - Reduces Expense
            // Cr Input Tax - Reduces Tax Asset

            $entries = [];

            if ($voucherType == 'Credit Note') {
                // Dr Sales Return (Taxable)
                $entries[] = [
                    'ledger_id' => $input['sales_ledger_id'], // User should pick "Sales Return" ledger
                    'debit' => $input['total_taxable'],
                    'credit' => 0,
                    'description' => 'Sales Return'
                ];
                // Dr Tax
                if ($input['total_tax'] > 0) {
                    $entries[] = [
                        'ledger_id' => $input['tax_ledger_id'],
                        'debit' => $input['total_tax'],
                        'credit' => 0,
                        'description' => 'Tax Reversal'
                    ];
                }
                // Cr Customer
                $entries[] = [
                    'ledger_id' => $input['customer_ledger_id'],
                    'debit' => 0,
                    'credit' => $input['total_payable'],
                    'description' => 'Credit Note #' . $input['return_number']
                ];

            } else {
                // Debit Note (Purchase Return)
                // Dr Vendor
                $entries[] = [
                    'ledger_id' => $input['customer_ledger_id'], // Vendor
                    'debit' => $input['total_payable'],
                    'credit' => 0,
                    'description' => 'Debit Note #' . $input['return_number']
                ];
                // Cr Purchase Return
                $entries[] = [
                    'ledger_id' => $input['sales_ledger_id'], // Purchase Ledger
                    'debit' => 0,
                    'credit' => $input['total_taxable'],
                    'description' => 'Purchase Return'
                ];
                // Cr Tax
                if ($input['total_tax'] > 0) {
                    $entries[] = [
                        'ledger_id' => $input['tax_ledger_id'],
                        'debit' => 0,
                        'credit' => $input['total_tax'],
                        'description' => 'Tax Reversal'
                    ];
                }
            }

            $voucher_id = $this->voucherModel->createVoucher($voucherData, $entries);

            if ($voucher_id) {
                // Save Item Details
                $invoiceItems = [];
                for ($i = 0; $i < count($input['item_name']); $i++) {
                    if (empty($input['item_name'][$i]))
                        continue;
                    $invoiceItems[] = [
                        'item_id' => $input['item_id'][$i] ?? null,
                        'name' => $input['item_name'][$i],
                        'quantity' => $input['quantity'][$i],
                        'rate' => $input['rate'][$i],
                        'amount' => $input['amount'][$i],
                        'tax_percent' => $input['tax_percent'][$i],
                        'tax_amount' => ($input['amount'][$i] * $input['tax_percent'][$i] / 100),
                        'total' => $input['row_total'][$i]
                    ];
                }

                $invoiceDetails = [
                    'voucher_id' => $voucher_id,
                    'due_date' => $input['return_date'],
                    'po_number' => $input['original_invoice'], // Reuse column for ref
                    'shipping_address' => 'Return',
                    'transport_mode' => '',
                    'vehicle_number' => '',
                    'eway_bill_no' => '',
                    'terms_conditions' => $input['terms_conditions'] ?? ''
                ];
                $this->invoiceModel->createInvoice($invoiceDetails, $invoiceItems);

                // Update Stock (Reverse Effect)
                foreach ($invoiceItems as $itm) {
                    if ($itm['item_id']) {
                        if ($voucherType == 'Credit Note') {
                            // Sales Return -> Stock Comes IN
                            $this->itemModel->updateStock($itm['item_id'], $itm['quantity'], 'in');
                        } else {
                            // Purchase Return -> Stock Goes OUT
                            $this->itemModel->updateStock($itm['item_id'], $itm['quantity'], 'out');
                        }
                    }
                }
                $this->redirect('vouchers/index');

            } else {
                die("Failed to create Return");
            }


        } else {
            // Prepare Data
            $customers = $this->ledgerModel->getLedgersByCompanyId($_SESSION['company_id']);
            $all_ledgers = $this->ledgerModel->getLedgersByCompanyId($_SESSION['company_id']);
            $items = $this->itemModel->getItemsByCompanyId($_SESSION['company_id']);

            $sales_ledgers = [];
            $tax_ledgers = [];

            // Filter logic
            foreach ($all_ledgers as $l) {
                if ($voucherType == 'Credit Note') {
                    // For Sales Return: Look for 'Sales', 'Income' or 'Return'
                    if (strpos($l->name, 'Return') !== false || stripos($l->group_name, 'Sales') !== false || stripos($l->group_name, 'Income') !== false)
                        $sales_ledgers[] = $l;
                } else {
                    // For Purchase Return: Look for 'Purchase', 'Expense'
                    if (stripos($l->group_name, 'Purchase') !== false || stripos($l->group_name, 'Expense') !== false)
                        $sales_ledgers[] = $l;
                }
                if (stripos($l->group_name, 'Tax') !== false || stripos($l->group_name, 'Duties') !== false) {
                    $tax_ledgers[] = $l;
                }
            }

            $data = [
                'nav' => 'vouchers', // Or Returns
                'type' => $voucherType,
                'title' => $title,
                'return_number' => $this->voucherModel->generateVoucherNumber($_SESSION['company_id'], $voucherType == 'Credit Note' ? 'CN' : 'DN'), // Short code
                'return_date' => date('Y-m-d'),
                'customers' => $customers,
                'sales_ledgers' => $sales_ledgers,
                'tax_ledgers' => $tax_ledgers,
                'items' => $items
            ];
            $this->view('returns/create', $data);
        }
    }
}
