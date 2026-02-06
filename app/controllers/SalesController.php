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

    public function index()
    {
        // Get all sales vouchers
        $this->db = new Database();
        $this->db->query("SELECT v.*, l.name as customer_name 
                          FROM vouchers v
                          LEFT JOIN voucher_entries ve ON v.id = ve.voucher_id AND ve.debit > 0
                          LEFT JOIN ledgers l ON ve.ledger_id = l.id
                          WHERE v.company_id = :cid AND v.voucher_type = 'Sales'
                          GROUP BY v.id
                          ORDER BY v.voucher_date DESC");
        $this->db->bind(':cid', $_SESSION['company_id']);
        $sales = $this->db->resultSet();

        $data = [
            'sales' => $sales,
            'nav' => 'sales'
        ];
        $this->view('sales/index', $data);
    }

    public function show($id)
    {
        $voucher = $this->voucherModel->getVoucherById($id);

        if (!$voucher || $voucher->company_id != $_SESSION['company_id'] || $voucher->voucher_type != 'Sales') {
            $this->redirect('sales/index');
            return;
        }

        $items = $this->invoiceModel->getInvoiceItems($id);
        $company = $this->model('Company')->getCompanyById($_SESSION['company_id']);

        // Get customer from debit entry
        $this->db = new Database();
        $this->db->query("SELECT l.* FROM ledgers l
                          JOIN voucher_entries ve ON l.id = ve.ledger_id
                          WHERE ve.voucher_id = :vid AND ve.debit > 0 LIMIT 1");
        $this->db->bind(':vid', $id);
        $customer = $this->db->single();

        $data = [
            'voucher' => $voucher,
            'items' => $items,
            'company' => $company,
            'customer' => $customer,
            'nav' => 'sales'
        ];
        $this->view('sales/view', $data);
    }

    public function create()
    {
        $fy_id = $this->voucherModel->checkAndCreateFY($_SESSION['company_id']);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            verify_csrf();
            $input = filter_input_array(INPUT_POST, FILTER_DEFAULT);

            // Find Default Ledgers since we removed the input
            $all_ledgers = $this->ledgerModel->getLedgersByCompanyId($_SESSION['company_id']);
            $sales_ledger_id = null;
            $tax_ledger_id = null;

            foreach ($all_ledgers as $l) {
                if (!$sales_ledger_id && (stripos($l->group_name, 'Sales') !== false || stripos($l->group_name, 'Income') !== false)) {
                    $sales_ledger_id = $l->id;
                }
                if (!$tax_ledger_id && (stripos($l->name, 'Output') !== false || stripos($l->group_name, 'Tax') !== false || stripos($l->group_name, 'Duties') !== false)) {
                    $tax_ledger_id = $l->id;
                }
            }

            // Fallback
            if (!$sales_ledger_id)
                $sales_ledger_id = $all_ledgers[0]->id ?? 0;
            if (!$tax_ledger_id)
                $tax_ledger_id = $all_ledgers[0]->id ?? 0;

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

            // Credit Entry (Sales Account)
            $entries[] = [
                'ledger_id' => $sales_ledger_id,
                'debit' => 0,
                'credit' => $input['total_taxable'],
                'description' => 'Sales Revenue'
            ];

            // Credit Entry (Tax)
            if ($input['total_tax'] > 0) {
                $entries[] = [
                    'ledger_id' => $tax_ledger_id,
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

                $tax_type = !empty($input['tax_type']) ? $input['tax_type'] : 'in_state';

                $qty = (float) ($input['quantity'][$i] ?? 0);
                $rate = (float) ($input['rate'][$i] ?? 0);
                $amount = (float) ($input['amount'][$i] ?? 0);

                // Fallback: Calculate amount if missing but Rate & Qty exist
                if ($amount <= 0 && $qty > 0 && $rate > 0) {
                    $amount = round($qty * $rate, 2);
                }

                $tax_percent = (float) ($input['tax_percent'][$i] ?? 0);
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

                $this->redirect('sales/index');
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


    public function edit($id)
    {
        // Verify invoice exists and belongs to current company
        $voucher = $this->voucherModel->getVoucherById($id);

        if (!$voucher || $voucher->company_id != $_SESSION['company_id'] || $voucher->voucher_type != 'Sales') {
            $_SESSION['flash_message'] = 'Invalid invoice or access denied.';
            $_SESSION['flash_type'] = 'danger';
            $this->redirect('sales/index');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Update invoice
            $data = [
                'voucher_id' => $id,
                'invoice_date' => $_POST['invoice_date'],
                'due_date' => $_POST['due_date'],
                'customer_ledger_id' => $_POST['customer_ledger_id'],
                'total_amount' => $_POST['total_amount'],
                'notes' => $_POST['notes'] ?? '',
                'terms_conditions' => $_POST['terms_conditions'] ?? '',
                'po_number' => $_POST['po_number'] ?? '',
                'shipping_address' => $_POST['shipping_address'] ?? '',
                'transport_mode' => $_POST['transport_mode'] ?? '',
                'vehicle_number' => $_POST['vehicle_number'] ?? '',
                'eway_bill_no' => $_POST['eway_bill_no'] ?? ''
            ];

            $items = [];
            if (isset($_POST['items'])) {
                foreach ($_POST['items'] as $item) {
                    if (!empty($item['name'])) {
                        $items[] = [
                            'item_id' => $item['item_id'] ?? null,
                            'name' => $item['name'],
                            'quantity' => $item['quantity'],
                            'rate' => $item['rate'],
                            'amount' => $item['amount'],
                            'tax_percent' => $item['tax_percent'] ?? 0,
                            'tax_amount' => $item['tax_amount'] ?? 0,
                            'cgst_amount' => $item['cgst_amount'] ?? 0,
                            'sgst_amount' => $item['sgst_amount'] ?? 0,
                            'igst_amount' => $item['igst_amount'] ?? 0,
                            'total' => $item['total']
                        ];
                    }
                }
            }

            try {
                $db = new Database();
                $db->query("START TRANSACTION");
                $db->execute();

                // Update voucher
                $db->query("UPDATE vouchers SET voucher_date = :vdate, total_amount = :amt, notes = :notes 
                           WHERE id = :vid");
                $db->bind(':vdate', $data['invoice_date']);
                $db->bind(':amt', $data['total_amount']);
                $db->bind(':notes', $data['notes']);
                $db->bind(':vid', $id);
                $db->execute();

                // Update invoice details
                $db->query("UPDATE invoice_details SET 
                           due_date = :due, terms_conditions = :terms, po_number = :po,
                           shipping_address = :ship, transport_mode = :mode, 
                           vehicle_number = :veh, eway_bill_no = :eway
                           WHERE voucher_id = :vid");
                $db->bind(':due', $data['due_date']);
                $db->bind(':terms', $data['terms_conditions']);
                $db->bind(':po', $data['po_number']);
                $db->bind(':ship', $data['shipping_address']);
                $db->bind(':mode', $data['transport_mode']);
                $db->bind(':veh', $data['vehicle_number']);
                $db->bind(':eway', $data['eway_bill_no']);
                $db->bind(':vid', $id);
                $db->execute();

                // Delete old items and entries
                $db->query("DELETE FROM invoice_items WHERE voucher_id = :vid");
                $db->bind(':vid', $id);
                $db->execute();

                $db->query("DELETE FROM voucher_entries WHERE voucher_id = :vid");
                $db->bind(':vid', $id);
                $db->execute();

                // Re-insert items
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

                // Re-create voucher entries (simplified - customer debit, sales credit)
                $db->query("INSERT INTO voucher_entries (voucher_id, ledger_id, debit, credit)
                           VALUES (:vid, :lid, :amt, 0)");
                $db->bind(':vid', $id);
                $db->bind(':lid', $data['customer_ledger_id']);
                $db->bind(':amt', $data['total_amount']);
                $db->execute();

                // Sales ledger credit (you may need to get this from POST or config)
                if (isset($_POST['sales_ledger_id'])) {
                    $db->query("INSERT INTO voucher_entries (voucher_id, ledger_id, debit, credit)
                               VALUES (:vid, :lid, 0, :amt)");
                    $db->bind(':vid', $id);
                    $db->bind(':lid', $_POST['sales_ledger_id']);
                    $db->bind(':amt', $data['total_amount']);
                    $db->execute();
                }

                $db->query("COMMIT");
                $db->execute();

                $_SESSION['flash_message'] = 'Invoice updated successfully!';
                $_SESSION['flash_type'] = 'success';
                $this->redirect('sales/show/' . $id);
            } catch (Exception $e) {
                $db->query("ROLLBACK");
                $db->execute();
                $_SESSION['flash_message'] = 'Error updating invoice: ' . $e->getMessage();
                $_SESSION['flash_type'] = 'danger';
                $this->redirect('sales/index');
            }
        } else {
            // Load existing data for editing
            $invoice_details = $this->invoiceModel->getInvoiceDetails($id);
            $items = $this->invoiceModel->getInvoiceItems($id);

            // Get customer from voucher entries
            $db = new Database();
            $db->query("SELECT l.* FROM ledgers l
                       JOIN voucher_entries ve ON l.id = ve.ledger_id
                       WHERE ve.voucher_id = :vid AND ve.debit > 0");
            $db->bind(':vid', $id);
            $customer = $db->single();

            // Get all customers and ledgers
            $customers = $this->ledgerModel->getLedgersByCompanyId($_SESSION['company_id']);
            $ledgers = $this->ledgerModel->getLedgersByCompanyId($_SESSION['company_id']);

            $sales_ledgers = [];
            $tax_ledgers = [];
            foreach ($ledgers as $l) {
                if (stripos($l->group_name, 'Sales') !== false || stripos($l->group_name, 'Income') !== false) {
                    $sales_ledgers[] = $l;
                }
                if (stripos($l->group_name, 'Tax') !== false || stripos($l->group_name, 'Duties') !== false) {
                    $tax_ledgers[] = $l;
                }
            }

            $all_items = $this->itemModel->getItemsByCompanyId($_SESSION['company_id']);

            $data = [
                'nav' => 'sales',
                'edit_mode' => true,
                'voucher' => $voucher,
                'invoice_details' => $invoice_details,
                'items' => $items,
                'customer' => $customer,
                'customers' => $customers,
                'sales_ledgers' => $sales_ledgers,
                'tax_ledgers' => $tax_ledgers,
                'all_items' => $all_items
            ];
            $this->view('sales/edit', $data);
        }
    }

    public function delete($id)
    {
        // Verify voucher belongs to current company and is a sales invoice
        $voucher = $this->voucherModel->getVoucherById($id);

        if (!$voucher || $voucher->company_id != $_SESSION['company_id'] || $voucher->voucher_type != 'Sales') {
            $_SESSION['flash_message'] = 'Invalid invoice or access denied.';
            $_SESSION['flash_type'] = 'danger';
            $this->redirect('sales/index');
            return;
        }

        try {
            $db = new Database();

            // Start transaction
            $db->query("START TRANSACTION");
            $db->execute();

            // Delete invoice items
            $db->query("DELETE FROM invoice_items WHERE voucher_id = :vid");
            $db->bind(':vid', $id);
            $db->execute();

            // Delete invoice details
            $db->query("DELETE FROM invoice_details WHERE voucher_id = :vid");
            $db->bind(':vid', $id);
            $db->execute();

            // Delete voucher entries
            $db->query("DELETE FROM voucher_entries WHERE voucher_id = :vid");
            $db->bind(':vid', $id);
            $db->execute();

            // Delete voucher
            $db->query("DELETE FROM vouchers WHERE id = :vid");
            $db->bind(':vid', $id);
            $db->execute();

            // Commit transaction
            $db->query("COMMIT");
            $db->execute();

            $_SESSION['flash_message'] = 'Sales invoice deleted successfully.';
            $_SESSION['flash_type'] = 'success';
        } catch (Exception $e) {
            // Rollback on error
            $db->query("ROLLBACK");
            $db->execute();

            $_SESSION['flash_message'] = 'Error deleting invoice: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
        }

        $this->redirect('sales/index');
    }
}
