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

    public function index()
    {
        // Get all purchase vouchers
        $db = new Database();
        $db->query("SELECT v.*, l.name as supplier_name 
                          FROM vouchers v
                          LEFT JOIN voucher_entries ve ON v.id = ve.voucher_id AND ve.credit > 0
                          LEFT JOIN ledgers l ON ve.ledger_id = l.id
                          WHERE v.company_id = :cid AND v.voucher_type = 'Purchase'
                          GROUP BY v.id
                          ORDER BY v.voucher_date DESC");
        $db->bind(':cid', $_SESSION['company_id']);
        $purchases = $db->resultSet();

        $data = [
            'purchases' => $purchases,
            'nav' => 'purchases'
        ];
        $this->view('purchases/index', $data);
    }

    public function show($id)
    {
        $voucher = $this->voucherModel->getVoucherById($id);

        if (!$voucher || $voucher->company_id != $_SESSION['company_id'] || $voucher->voucher_type != 'Purchase') {
            $this->redirect('purchases/index');
            return;
        }

        $items = $this->invoiceModel->getInvoiceItems($id);
        $company = $this->model('Company')->getCompanyById($_SESSION['company_id']);

        // Get supplier from credit entry
        $db = new Database();
        $db->query("SELECT l.* FROM ledgers l
                          JOIN voucher_entries ve ON l.id = ve.ledger_id
                          WHERE ve.voucher_id = :vid AND ve.credit > 0 LIMIT 1");
        $db->bind(':vid', $id);
        $supplier = $db->single();

        $data = [
            'voucher' => $voucher,
            'items' => $items,
            'company' => $company,
            'supplier' => $supplier,
            'nav' => 'purchases'
        ];
        $this->view('purchases/view', $data);
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

            // Find Default Ledgers
            $all_ledgers = $this->ledgerModel->getLedgersByCompanyId($_SESSION['company_id']);
            $purchase_ledger_id = null;
            $tax_ledger_id = null;

            foreach ($all_ledgers as $l) {
                if (!$purchase_ledger_id && (stripos($l->group_name, 'Purchase') !== false || stripos($l->group_name, 'Expense') !== false)) {
                    $purchase_ledger_id = $l->id;
                }
                if (!$tax_ledger_id && (stripos($l->name, 'Input') !== false || stripos($l->group_name, 'Tax') !== false || stripos($l->group_name, 'Duties') !== false)) {
                    $tax_ledger_id = $l->id;
                }
            }
            if (!$purchase_ledger_id)
                $purchase_ledger_id = $all_ledgers[0]->id ?? 0;
            if (!$tax_ledger_id)
                $tax_ledger_id = $all_ledgers[0]->id ?? 0;

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
                'ledger_id' => $purchase_ledger_id,
                'debit' => $input['total_taxable'],
                'credit' => 0,
                'description' => 'Purchase Cost'
            ];

            // Debit Entry (Input Tax)
            if ($input['total_tax'] > 0) {
                $entries[] = [
                    'ledger_id' => $tax_ledger_id,
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

                $tax_type = !empty($input['tax_type']) ? $input['tax_type'] : 'in_state';

                $amount = (float) $input['amount'][$i];
                $tax_percent = (float) $input['tax_percent'][$i];
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
                    'item_id' => $input['item_id'][$i] ?? null,
                    'name' => $input['item_name'][$i],
                    'quantity' => $input['quantity'][$i],
                    'rate' => $input['rate'][$i],
                    'amount' => $input['amount'][$i],
                    'tax_percent' => $input['tax_percent'][$i],
                    'tax_amount' => $tax_amount,
                    'cgst_amount' => $cgst,
                    'sgst_amount' => $sgst,
                    'igst_amount' => $igst,
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

                    $this->redirect('purchases/index');
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


    public function edit($id)
    {
        $voucher = $this->voucherModel->getVoucherById($id);

        if (!$voucher || $voucher->company_id != $_SESSION['company_id'] || $voucher->voucher_type != 'Purchase') {
            $_SESSION['flash_message'] = 'Invalid purchase bill or access denied.';
            $_SESSION['flash_type'] = 'danger';
            $this->redirect('purchases/index');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'voucher_id' => $id,
                'invoice_date' => $_POST['invoice_date'],
                'due_date' => $_POST['due_date'],
                'supplier_ledger_id' => $_POST['supplier_ledger_id'],
                'total_amount' => $_POST['total_amount'],
                'notes' => $_POST['notes'] ?? '',
                'terms_conditions' => $_POST['terms_conditions'] ?? ''
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
                $db->bind(':vdate', $data['invoice_date']);
                $db->bind(':amt', $data['total_amount']);
                $db->bind(':notes', $data['notes']);
                $db->bind(':vid', $id);
                $db->execute();

                $db->query("UPDATE invoice_details SET due_date = :due, terms_conditions = :terms WHERE voucher_id = :vid");
                $db->bind(':due', $data['due_date']);
                $db->bind(':terms', $data['terms_conditions']);
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

                $db->query("INSERT INTO voucher_entries (voucher_id, ledger_id, debit, credit) VALUES (:vid, :lid, :amt, 0)");
                $db->bind(':vid', $id);
                $db->bind(':lid', isset($_POST['purchase_ledger_id']) ? $_POST['purchase_ledger_id'] : 0);
                $db->bind(':amt', $_POST['total_taxable']); // Purchase Ledger gets Taxable Amount, not Total
                $db->execute();

                if (isset($_POST['total_tax']) && $_POST['total_tax'] > 0) {
                    $db->query("INSERT INTO voucher_entries (voucher_id, ledger_id, debit, credit) VALUES (:vid, :lid, :amt, 0)");
                    $db->bind(':vid', $id);
                    $db->bind(':lid', isset($_POST['tax_ledger_id']) ? $_POST['tax_ledger_id'] : 0);
                    $db->bind(':amt', $_POST['total_tax']);
                    $db->execute();
                }

                $db->query("INSERT INTO voucher_entries (voucher_id, ledger_id, debit, credit) VALUES (:vid, :lid, 0, :amt)");
                $db->bind(':vid', $id);
                $db->bind(':lid', $data['supplier_ledger_id']);
                $db->bind(':amt', $data['total_amount']);
                $db->execute();

                $db->query("COMMIT");
                $db->execute();

                $_SESSION['flash_message'] = 'Purchase bill updated successfully!';
                $_SESSION['flash_type'] = 'success';
                $this->redirect('purchases/show/' . $id);
            } catch (Exception $e) {
                $db->query("ROLLBACK");
                $db->execute();
                $_SESSION['flash_message'] = 'Error updating purchase bill: ' . $e->getMessage();
                $_SESSION['flash_type'] = 'danger';
                $this->redirect('purchases/index');
            }
        } else {
            $invoice_details = $this->invoiceModel->getInvoiceDetails($id);
            $items = $this->invoiceModel->getInvoiceItems($id);

            $db = new Database();
            $db->query("SELECT l.* FROM ledgers l JOIN voucher_entries ve ON l.id = ve.ledger_id
                       WHERE ve.voucher_id = :vid AND ve.credit > 0");
            $db->bind(':vid', $id);
            $supplier = $db->single();

            $suppliers = $this->ledgerModel->getLedgersByCompanyId($_SESSION['company_id']);
            $ledgers = $this->ledgerModel->getLedgersByCompanyId($_SESSION['company_id']);

            $purchase_ledgers = [];
            $tax_ledgers = [];
            foreach ($ledgers as $l) {
                if (stripos($l->group_name, 'Purchase') !== false || stripos($l->group_name, 'Expense') !== false) {
                    $purchase_ledgers[] = $l;
                }
                if (stripos($l->group_name, 'Tax') !== false || stripos($l->group_name, 'Duties') !== false) {
                    $tax_ledgers[] = $l;
                }
            }

            $all_items = $this->itemModel->getItemsByCompanyId($_SESSION['company_id']);

            $data = [
                'nav' => 'purchases',
                'edit_mode' => true,
                'voucher' => $voucher,
                'invoice_details' => $invoice_details,
                'items' => $items,
                'supplier' => $supplier,
                'suppliers' => $suppliers,
                'purchase_ledgers' => $purchase_ledgers,
                'tax_ledgers' => $tax_ledgers,
                'all_items' => $all_items
            ];
            $this->view('purchases/edit', $data);
        }
    }

    public function delete($id)
    {
        $voucher = $this->voucherModel->getVoucherById($id);

        if (!$voucher || $voucher->company_id != $_SESSION['company_id'] || $voucher->voucher_type != 'Purchase') {
            $_SESSION['flash_message'] = 'Invalid purchase bill or access denied.';
            $_SESSION['flash_type'] = 'danger';
            $this->redirect('purchases/index');
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

            $_SESSION['flash_message'] = 'Purchase bill deleted successfully.';
            $_SESSION['flash_type'] = 'success';
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $db->execute();

            $_SESSION['flash_message'] = 'Error deleting purchase bill: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
        }

        $this->redirect('purchases/index');
    }
}
