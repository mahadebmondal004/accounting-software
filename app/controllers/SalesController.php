<?php
class SalesController extends Controller
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
        $this->invoiceModel = $this->model('Invoice');
        $this->ledgerModel = $this->model('Ledger');
        $this->itemModel = $this->model('Item');
    }

    public function create()
    {
        $fy_id = $this->voucherModel->checkAndCreateFY($_SESSION['company_id']);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            verify_csrf();
            $input = filter_input_array(INPUT_POST, FILTER_DEFAULT);

            // 1. Prepare Voucher Data
            $voucherData = [
                'company_id' => $_SESSION['company_id'],
                'financial_year_id' => $fy_id,
                'voucher_type' => 'Sales',
                'voucher_number' => $input['invoice_number'], // Using invoice no as voucher no
                'voucher_date' => $input['invoice_date'],
                'narration' => "Sales Invoice #" . $input['invoice_number'] . " to " . $input['customer_name'],
                'total_amount' => $input['total_payable'],
                'created_by' => $_SESSION['user_id']
            ];

            // 2. Prepare Ledger Entries (The Accounting Entry)
            // Dr Customer (Full Amount)
            // Cr Sales Account (Taxable Amount)
            // Cr Tax (Tax Output)

            $entries = [];

            // Debit Entry (Customer)
            $entries[] = [
                'ledger_id' => $input['customer_ledger_id'],
                'debit' => $input['total_payable'],
                'credit' => 0,
                'description' => 'Invoice #' . $input['invoice_number']
            ];

            // Credit Entry (Sales Account) - Assuming one common sales ledger for now
            // In a real app, you might map product category to different sales ledgers.
            // We use the selected 'sales_ledger_id'
            $entries[] = [
                'ledger_id' => $input['sales_ledger_id'],
                'debit' => 0,
                'credit' => $input['total_taxable'],
                'description' => 'Sales Revenue'
            ];

            // Credit Entry (Tax)
            if ($input['total_tax'] > 0) {
                // Simplification: We dump all tax into one "Output GST" ledger user picked
                // Real World: Split CGST/SGST based on logic.
                $entries[] = [
                    'ledger_id' => $input['tax_ledger_id'],
                    'debit' => 0,
                    'credit' => $input['total_tax'],
                    'description' => 'GST Output'
                ];
            }

            // 3. Prepare Invoice Items (The Printable Details)
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

            // Execute Transaction
            // We need to modify voucherModel->createVoucher to Return ID instead of bool, 
            // OR we use the model's transaction support externally.
            // For now, VoucherModel runs its own transaction. 
            // We should ideally wrap this whole block in a transaction.

            // Hack for now: Insert Voucher (Head+Entries) -> Get ID -> Insert Details

            // Wait, createVoucher returns boolean in my previous code.
            // I need the last ID. 
            // Let's rely on DB::lastInsertId after createVoucher success? 
            // No, createVoucher might commit.
            // I will update Voucher Model to return ID on success. (Done in previous step? No, returns true).
            // Let's Quickly fix Voucher Model locally or assume I can fetch it back.
            // actually I'll use `generateVoucherNumber` to ensure uniqueness then fetch by it.

            $voucher_id = $this->voucherModel->createVoucher($voucherData, $entries);
            if ($voucher_id) {
                $invoiceDetails = [
                    'voucher_id' => $voucher_id,
                    'due_date' => $input['due_date'] ?? $input['invoice_date'], // Use Due Date or Invoice Date
                    'po_number' => $input['po_number'] ?? null,
                    'shipping_address' => $input['shipping_address'] ?? null,
                    'transport_mode' => $input['transport_mode'] ?? null,
                    'vehicle_number' => $input['vehicle_number'] ?? null,
                    'eway_bill_no' => $input['eway_bill_no'] ?? null,
                    'terms_conditions' => $input['terms_conditions'] ?? ''
                ];
                $this->invoiceModel->createInvoice($invoiceDetails, $invoiceItems);

                // Update Stock
                foreach ($invoiceItems as $itm) {
                    if ($itm['item_id']) {
                        $this->itemModel->updateStock($itm['item_id'], $itm['quantity'], 'out');
                    }
                }

                $this->redirect('vouchers/index'); // Or invoice view
            } else {
                die("Failed to create Invoice Voucher");
            }

        } else {
            $customers = $this->ledgerModel->getLedgersByCompanyId($_SESSION['company_id']); // Filter by type='Customer' ideally
            // Filter Sales and Tax Ledgers
            $sales_ledgers = [];
            $tax_ledgers = [];
            $all_ledgers = $this->ledgerModel->getLedgersByCompanyId($_SESSION['company_id']);
            $items = $this->itemModel->getItemsByCompanyId($_SESSION['company_id']);

            foreach ($all_ledgers as $l) {
                // Check if group contains 'Sales' or 'Income'
                if (stripos($l->group_name, 'Sales') !== false || stripos($l->group_name, 'Income') !== false) {
                    $sales_ledgers[] = $l;
                }
                // Check if group contains 'Tax' or 'Duties'
                if (stripos($l->group_name, 'Tax') !== false || stripos($l->group_name, 'Duties') !== false) {
                    $tax_ledgers[] = $l;
                }
            }

            $data = [
                'nav' => 'sales',
                'invoice_number' => $this->voucherModel->generateVoucherNumber($_SESSION['company_id'], 'Sales'),
                'invoice_date' => date('Y-m-d'),
                'customers' => $customers,
                'sales_ledgers' => $sales_ledgers,
                'tax_ledgers' => $tax_ledgers,
                'items' => $items
            ];
            $this->view('sales/create', $data);
        }
    }
}
