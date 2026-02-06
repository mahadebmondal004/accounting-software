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

    public function index()
    {
        // Get all return vouchers (Credit Note and Debit Note)
        $db = new Database();
        $db->query("SELECT v.*, l.name as party_name 
                          FROM vouchers v
                          LEFT JOIN voucher_entries ve ON v.id = ve.voucher_id
                          LEFT JOIN ledgers l ON ve.ledger_id = l.id
                          WHERE v.company_id = :cid AND v.voucher_type IN ('Credit Note', 'Debit Note')
                          GROUP BY v.id
                          ORDER BY v.voucher_date DESC");
        $db->bind(':cid', $_SESSION['company_id']);
        $returns = $db->resultSet();

        $data = [
            'returns' => $returns,
            'nav' => 'returns'
        ];
        $this->view('returns/index', $data);
    }

    public function show($id)
    {
        $voucher = $this->voucherModel->getVoucherById($id);

        if (!$voucher || $voucher->company_id != $_SESSION['company_id'] || !in_array($voucher->voucher_type, ['Credit Note', 'Debit Note'])) {
            $this->redirect('returns/index');
            return;
        }

        $items = $this->invoiceModel->getInvoiceItems($id);
        $company = $this->model('Company')->getCompanyById($_SESSION['company_id']);

        // Get party (customer or supplier)
        $db = new Database();
        if ($voucher->voucher_type == 'Credit Note') {
            // Sales Return - get customer from credit entry
            $db->query("SELECT l.* FROM ledgers l
                              JOIN voucher_entries ve ON l.id = ve.ledger_id
                              WHERE ve.voucher_id = :vid AND ve.credit > 0 LIMIT 1");
        } else {
            // Purchase Return - get supplier from debit entry
            $db->query("SELECT l.* FROM ledgers l
                              JOIN voucher_entries ve ON l.id = ve.ledger_id
                              WHERE ve.voucher_id = :vid AND ve.debit > 0 LIMIT 1");
        }
        $db->bind(':vid', $id);
        $party = $db->single();

        $data = [
            'voucher' => $voucher,
            'items' => $items,
            'company' => $company,
            'party' => $party,
            'nav' => 'returns'
        ];
        $this->view('returns/view', $data);
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

            // Find Default Ledgers
            $all_ledgers = $this->ledgerModel->getLedgersByCompanyId($_SESSION['company_id']);
            $sales_ledger_id = null; // Used for both Sales Return and Purchase Return (as generic ledger var)
            $tax_ledger_id = null;

            foreach ($all_ledgers as $l) {
                if ($voucherType == 'Credit Note') {
                    // Sales Return
                    if (!$sales_ledger_id && (stripos($l->name, 'Return') !== false || stripos($l->group_name, 'Sales') !== false)) {
                        $sales_ledger_id = $l->id;
                    }
                } else {
                    // Purchase Return
                    if (!$sales_ledger_id && (stripos($l->group_name, 'Purchase') !== false || stripos($l->group_name, 'Expense') !== false)) {
                        $sales_ledger_id = $l->id;
                    }
                }

                if (!$tax_ledger_id && (stripos($l->group_name, 'Tax') !== false || stripos($l->group_name, 'Duties') !== false)) {
                    $tax_ledger_id = $l->id;
                }
            }
            if (!$sales_ledger_id)
                $sales_ledger_id = $all_ledgers[0]->id ?? 0;
            if (!$tax_ledger_id)
                $tax_ledger_id = $all_ledgers[0]->id ?? 0;


            if ($voucherType == 'Credit Note') {
                // Dr Sales Return (Taxable)
                $entries[] = [
                    'ledger_id' => $sales_ledger_id, // User should pick "Sales Return" ledger
                    'debit' => $input['total_taxable'],
                    'credit' => 0,
                    'description' => 'Sales Return'
                ];
                // Dr Tax
                if ($input['total_tax'] > 0) {
                    $entries[] = [
                        'ledger_id' => $tax_ledger_id,
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
                    'ledger_id' => $sales_ledger_id, // Purchase Ledger
                    'debit' => 0,
                    'credit' => $input['total_taxable'],
                    'description' => 'Purchase Return'
                ];
                // Cr Tax
                if ($input['total_tax'] > 0) {
                    $entries[] = [
                        'ledger_id' => $tax_ledger_id,
                        'debit' => 0,
                        'credit' => $input['total_tax'],
                        'description' => 'Tax Reversal'
                    ];
                }
            }

            $voucher_id = $this->voucherModel->createVoucher($voucherData, $entries);

            if ($voucher_id) {
                // Save Item Details
                // Save Item Details
                $invoiceItems = [];
                if (isset($_POST['item_name'])) {
                    for ($i = 0; $i < count($_POST['item_name']); $i++) {
                        if (empty($_POST['item_name'][$i]))
                            continue;

                        $tax_type = !empty($input['tax_type']) ? $input['tax_type'] : 'in_state';
                        $amount = (float) $_POST['amount'][$i];
                        $tax_percent = (float) $_POST['tax_percent'][$i];
                        $tax_amount = ($amount * $tax_percent / 100);

                        $cgst = 0.00;
                        $sgst = 0.00;
                        $igst = 0.00;

                        if ($tax_type === 'in_state') {
                            $cgst = $tax_amount / 2;
                            $sgst = $tax_amount / 2;
                        } else {
                            $igst = $tax_amount;
                        }

                        $invoiceItems[] = [
                            'item_id' => $_POST['item_id'][$i] ?? null,
                            'name' => $_POST['item_name'][$i],
                            'quantity' => $_POST['quantity'][$i],
                            'rate' => $_POST['rate'][$i],
                            'amount' => $amount,
                            'tax_percent' => $tax_percent,
                            'tax_amount' => $tax_amount,
                            'cgst_amount' => $cgst,
                            'sgst_amount' => $sgst,
                            'igst_amount' => $igst,
                            'total' => $_POST['row_total'][$i]
                        ];
                    }
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
                $this->redirect('returns/index');

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


    public function edit($id)
    {
        $voucher = $this->voucherModel->getVoucherById($id);

        if (
            !$voucher || $voucher->company_id != $_SESSION['company_id'] ||
            ($voucher->voucher_type != 'Credit Note' && $voucher->voucher_type != 'Debit Note')
        ) {
            $_SESSION['flash_message'] = 'Invalid return or access denied.';
            $_SESSION['flash_type'] = 'danger';
            $this->redirect('returns/index');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'voucher_id' => $id,
                'return_date' => $_POST['return_date'],
                'party_ledger_id' => $_POST['party_ledger_id'],
                'total_amount' => $_POST['total_amount'],
                'notes' => $_POST['notes'] ?? '',
                'reason' => $_POST['reason'] ?? ''
            ];

            $items = [];
            if (isset($_POST['item_name'])) {
                for ($i = 0; $i < count($_POST['item_name']); $i++) {
                    if (empty($_POST['item_name'][$i]))
                        continue;

                    $tax_type = !empty($_POST['tax_type']) ? $_POST['tax_type'] : 'in_state';
                    $amount = (float) ($_POST['amount'][$i] ?? 0);
                    $tax_percent = (float) ($_POST['tax_percent'][$i] ?? 0);
                    $tax_amount = ($amount * $tax_percent / 100);

                    $cgst = 0.00;
                    $sgst = 0.00;
                    $igst = 0.00;

                    if ($tax_type === 'in_state') {
                        $cgst = $tax_amount / 2;
                        $sgst = $tax_amount / 2;
                    } else {
                        $igst = $tax_amount;
                    }

                    $items[] = [
                        'item_id' => $_POST['item_id'][$i] ?? null,
                        'name' => $_POST['item_name'][$i],
                        'quantity' => $_POST['quantity'][$i],
                        'rate' => $_POST['rate'][$i],
                        'amount' => $amount,
                        'tax_percent' => $tax_percent,
                        'tax_amount' => $tax_amount,
                        'cgst_amount' => $cgst,
                        'sgst_amount' => $sgst,
                        'igst_amount' => $igst,
                        'total' => $_POST['row_total'][$i]
                    ];
                }
            }

            try {
                $db = new Database();
                $db->query("START TRANSACTION");
                $db->execute();

                $db->query("UPDATE vouchers SET voucher_date = :vdate, total_amount = :amt, notes = :notes WHERE id = :vid");
                $db->bind(':vdate', $data['return_date']);
                $db->bind(':amt', $data['total_amount']);
                $db->bind(':notes', $data['notes']);
                $db->bind(':vid', $id);
                $db->execute();

                $db->query("DELETE FROM invoice_items WHERE voucher_id = :vid");
                $db->bind(':vid', $id);
                $db->execute();

                $db->query("DELETE FROM voucher_entries WHERE voucher_id = :vid");
                $db->bind(':vid', $id);
                $db->execute();

                foreach ($items as $item) {
                    $db->query('INSERT INTO invoice_items 
                        (voucher_id, item_id, item_name, quantity, rate, amount, tax_percent, tax_amount, cgst_amount, sgst_amount, igst_amount, total)
                        VALUES (:vid, :iid, :name, :qty, :rate, :amt, :tax_p, :tax_a, :cgst, :sgst, :igst, :total)');
                    $db->bind(':vid', $id);
                    $item_id = isset($item['item_id']) && $item['item_id'] > 0 ? $item['item_id'] : null;
                    $db->bind(':iid', $item_id);
                    $db->bind(':name', $item['name']);
                    $db->bind(':qty', $item['quantity']);
                    $db->bind(':rate', $item['rate']);
                    $db->bind(':amt', $item['amount']);
                    $db->bind(':tax_p', $item['tax_percent']);
                    $db->bind(':tax_a', $item['tax_amount']);
                    $db->bind(':cgst', $item['cgst_amount']);
                    $db->bind(':sgst', $item['sgst_amount']);
                    $db->bind(':igst', $item['igst_amount']);
                    $db->bind(':total', $item['total']);
                    $db->execute();
                }

                // Re-create voucher entries based on type
                if ($voucher->voucher_type == 'Credit Note') {
                    // Sales Return: Customer Credit, Sales Return Debit
                    $db->query("INSERT INTO voucher_entries (voucher_id, ledger_id, debit, credit) VALUES (:vid, :lid, 0, :amt)");
                    $db->bind(':vid', $id);
                    $db->bind(':lid', $data['party_ledger_id']);
                    $db->bind(':amt', $data['total_amount']);
                    $db->execute();
                } else {
                    // Purchase Return: Supplier Debit, Purchase Return Credit
                    $db->query("INSERT INTO voucher_entries (voucher_id, ledger_id, debit, credit) VALUES (:vid, :lid, :amt, 0)");
                    $db->bind(':vid', $id);
                    $db->bind(':lid', $data['party_ledger_id']);
                    $db->bind(':amt', $data['total_amount']);
                    $db->execute();
                }

                $db->query("COMMIT");
                $db->execute();

                $_SESSION['flash_message'] = 'Return updated successfully!';
                $_SESSION['flash_type'] = 'success';
                $this->redirect('returns/show/' . $id);
            } catch (Exception $e) {
                $db->query("ROLLBACK");
                $db->execute();
                $_SESSION['flash_message'] = 'Error updating return: ' . $e->getMessage();
                $_SESSION['flash_type'] = 'danger';
                $this->redirect('returns/index');
            }
        } else {
            $items = $this->invoiceModel->getInvoiceItems($id);

            $db = new Database();
            if ($voucher->voucher_type == 'Credit Note') {
                $db->query("SELECT l.* FROM ledgers l JOIN voucher_entries ve ON l.id = ve.ledger_id
                           WHERE ve.voucher_id = :vid AND ve.credit > 0");
            } else {
                $db->query("SELECT l.* FROM ledgers l JOIN voucher_entries ve ON l.id = ve.ledger_id
                           WHERE ve.voucher_id = :vid AND ve.debit > 0");
            }
            $db->bind(':vid', $id);
            $party = $db->single();

            $customers = $this->ledgerModel->getLedgersByCompanyId($_SESSION['company_id']);
            $all_items = $this->itemModel->getItemsByCompanyId($_SESSION['company_id']);

            $data = [
                'nav' => 'returns',
                'edit_mode' => true,
                'voucher' => $voucher,
                'items' => $items,
                'party' => $party,
                'customers' => $customers,
                'all_items' => $all_items,
                'return_type' => $voucher->voucher_type
            ];
            $this->view('returns/edit', $data);
        }
    }

    public function delete($id)
    {
        $voucher = $this->voucherModel->getVoucherById($id);

        if (
            !$voucher || $voucher->company_id != $_SESSION['company_id'] ||
            ($voucher->voucher_type != 'Credit Note' && $voucher->voucher_type != 'Debit Note')
        ) {
            $_SESSION['flash_message'] = 'Invalid return or access denied.';
            $_SESSION['flash_type'] = 'danger';
            $this->redirect('returns/index');
            return;
        }

        try {
            $db = new Database();
            $db->query("START TRANSACTION");
            $db->execute();

            $db->query("DELETE FROM invoice_items WHERE voucher_id = :vid");
            $db->bind(':vid', $id);
            $db->execute();

            $db->query("DELETE FROM invoice_details WHERE voucher_id = :vid");
            $db->bind(':vid', $id);
            $db->execute();

            $db->query("DELETE FROM voucher_entries WHERE voucher_id = :vid");
            $db->bind(':vid', $id);
            $db->execute();

            $db->query("DELETE FROM vouchers WHERE id = :vid");
            $db->bind(':vid', $id);
            $db->execute();

            $db->query("COMMIT");
            $db->execute();

            $_SESSION['flash_message'] = 'Return deleted successfully.';
            $_SESSION['flash_type'] = 'success';
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $db->execute();

            $_SESSION['flash_message'] = 'Error deleting return: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
        }

        $this->redirect('returns/index');
    }
}
