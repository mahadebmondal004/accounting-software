<?php
class PurchasesController extends Controller
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

            $voucherData = [
                'company_id' => $_SESSION['company_id'],
                'financial_year_id' => $fy_id,
                'voucher_type' => 'Purchase',
                'voucher_number' => $input['invoice_number'], // Supplier Invoice No? Usually we generate internal V.No and store Supplier Ref. For Simplification, we use internal.
                'voucher_date' => $input['invoice_date'],
                'narration' => "Purchase Bill #" . $input['invoice_number'] . " from " . $input['supplier_name'],
                'total_amount' => $input['total_payable'],
                'created_by' => $_SESSION['user_id']
            ];

            // Purchase Entry
            // Cr Supplier (Total Payable)
            // Dr Purchase Account (Taxable)
            // Dr Input Tax (Tax Amount)

            $entries = [];

            // Credit Entry (Supplier)
            $entries[] = [
                'ledger_id' => $input['supplier_ledger_id'],
                'debit' => 0,
                'credit' => $input['total_payable'],
                'description' => 'Bill #' . $input['invoice_number']
            ];

            // Debit Entry (Purchase Account)
            $entries[] = [
                'ledger_id' => $input['purchase_ledger_id'],
                'debit' => $input['total_taxable'],
                'credit' => 0,
                'description' => 'Purchase Cost'
            ];

            // Debit Entry (Input Tax)
            if ($input['total_tax'] > 0) {
                $entries[] = [
                    'ledger_id' => $input['tax_ledger_id'],
                    'debit' => $input['total_tax'],
                    'credit' => 0,
                    'description' => 'GST Input'
                ];
            }

            // Items
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

            if ($this->voucherModel->createVoucher($voucherData, $entries)) {
                $db = new Database();
                $db->query('SELECT id FROM vouchers WHERE voucher_number = :num AND company_id = :cid');
                $db->bind(':num', $voucherData['voucher_number']);
                $db->bind(':cid', $_SESSION['company_id']);
                $v = $db->single();

                if ($v) {
                    $invoiceDetails = [
                        'voucher_id' => $v->id,
                        'due_date' => $input['invoice_date'],
                        'terms_conditions' => ''
                    ];
                    $this->invoiceModel->createInvoice($invoiceDetails, $invoiceItems);

                    // Increase Stock
                    foreach ($invoiceItems as $itm) {
                        if ($itm['item_id']) {
                            $this->itemModel->updateStock($itm['item_id'], $itm['quantity'], 'in');
                        }
                    }

                    $this->redirect('vouchers/index');
                }
            } else {
                die("Failed to create Purchase Voucher");
            }

        } else {
            $suppliers = $this->ledgerModel->getLedgersByCompanyId($_SESSION['company_id']); // Filter by type='Supplier'
            $purchase_ledgers = [];
            $tax_ledgers = [];
            $all_ledgers = $this->ledgerModel->getLedgersByCompanyId($_SESSION['company_id']);
            $items = $this->itemModel->getItemsByCompanyId($_SESSION['company_id']);

            foreach ($all_ledgers as $l) {
                if (stripos($l->group_name, 'Purchase') !== false || stripos($l->group_name, 'Expense') !== false) {
                    $purchase_ledgers[] = $l;
                }
                if (stripos($l->group_name, 'Tax') !== false || stripos($l->group_name, 'Duties') !== false) {
                    $tax_ledgers[] = $l;
                }
            }

            $data = [
                'nav' => 'purchases',
                'invoice_number' => $this->voucherModel->generateVoucherNumber($_SESSION['company_id'], 'Purchase'),
                'invoice_date' => date('Y-m-d'),
                'suppliers' => $suppliers,
                'purchase_ledgers' => $purchase_ledgers,
                'tax_ledgers' => $tax_ledgers,
                'items' => $items
            ];
            $this->view('purchases/create', $data);
        }
    }
}
