<?php
class EstimatesController extends Controller
{
    private $estimateModel;
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

        $this->estimateModel = $this->model('Estimate');
        $this->ledgerModel = $this->model('Ledger');
        $this->itemModel = $this->model('Item');
    }

    public function index()
    {
        $estimates = $this->estimateModel->getEstimatesByCompanyId($_SESSION['company_id']);
        $data = [
            'estimates' => $estimates,
            'nav' => 'estimates'
        ];
        $this->view('estimates/index', $data);
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            verify_csrf();
            $input = filter_input_array(INPUT_POST, FILTER_DEFAULT);

            $estimateData = [
                'company_id' => $_SESSION['company_id'],
                'estimate_number' => $input['estimate_number'],
                'estimate_date' => $input['estimate_date'],
                'expiry_date' => $input['expiry_date'],
                'customer_ledger_id' => $input['customer_ledger_id'],
                'total_amount' => $input['total_payable'],
                'notes' => $input['notes'],
                'created_by' => $_SESSION['user_id']
            ];

            $estimateItems = [];
            if (isset($_POST['item_name'])) {
                for ($i = 0; $i < count($_POST['item_name']); $i++) {
                    if (empty($_POST['item_name'][$i]))
                        continue;

                    $tax_type = !empty($input['tax_type']) ? $input['tax_type'] : 'in_state';
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

                    $item_id = !empty($_POST['item_id'][$i]) ? $_POST['item_id'][$i] : null;

                    $estimateItems[] = [
                        'item_id' => $item_id,
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

            if ($this->estimateModel->createEstimate($estimateData, $estimateItems)) {
                $this->redirect('estimates/index');
            } else {
                die("Failed to create Estimate");
            }

        } else {
            $customers = $this->ledgerModel->getLedgersByCompanyId($_SESSION['company_id']);
            $items = $this->itemModel->getItemsByCompanyId($_SESSION['company_id']);

            $data = [
                'nav' => 'estimates',
                'estimate_number' => $this->estimateModel->generateEstimateNumber($_SESSION['company_id']),
                'estimate_date' => date('Y-m-d'),
                'customers' => $customers,
                'items' => $items
            ];
            $this->view('estimates/create', $data);
        }
    }

    public function show($id)
    {
        $estimate = $this->estimateModel->getEstimateById($id);
        if (!$estimate || $estimate->company_id != $_SESSION['company_id']) {
            $this->redirect('estimates/index');
            return;
        }

        $items = $this->estimateModel->getEstimateItems($id);
        $company = $this->model('Company')->getCompanyById($_SESSION['company_id']);

        $data = [
            'estimate' => $estimate,
            'items' => $items,
            'company' => $company,
            'nav' => 'estimates'
        ];
        $this->view('estimates/view', $data);
    }


    public function edit($id)
    {
        $estimate = $this->estimateModel->getEstimateById($id);

        if (!$estimate || $estimate->company_id != $_SESSION['company_id']) {
            $_SESSION['flash_message'] = 'Invalid estimate or access denied.';
            $_SESSION['flash_type'] = 'danger';
            $this->redirect('estimates/index');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'estimate_id' => $id,
                'estimate_date' => $_POST['estimate_date'],
                'expiry_date' => $_POST['expiry_date'],
                'customer_ledger_id' => $_POST['customer_ledger_id'], // Changed from customer_name
                'total_amount' => $_POST['total_amount'],
                'notes' => $_POST['notes'] ?? '',
                'terms' => $_POST['terms'] ?? ''
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

                    $item_id = !empty($_POST['item_id'][$i]) ? $_POST['item_id'][$i] : null;

                    $items[] = [
                        'item_id' => $item_id,
                        'name' => $_POST['item_name'][$i],
                        'description' => '', // Description not typically in grid inputs unless added
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

                $db->query("UPDATE estimates SET estimate_date = :edate, expiry_date = :valid, 
                           customer_ledger_id = :clid,
                           total_amount = :amt, notes = :notes, terms = :terms WHERE id = :eid");
                $db->bind(':edate', $data['estimate_date']);
                $db->bind(':valid', $data['expiry_date']);
                $db->bind(':clid', $data['customer_ledger_id']);
                $db->bind(':amt', $data['total_amount']);
                $db->bind(':notes', $data['notes']);
                $db->bind(':terms', $data['terms']);
                $db->bind(':eid', $id);
                $db->execute();

                $db->query("DELETE FROM estimate_items WHERE estimate_id = :eid");
                $db->bind(':eid', $id);
                $db->execute();

                foreach ($items as $item) {
                    $db->query('INSERT INTO estimate_items (estimate_id, item_id, item_name, description, quantity, rate, amount, tax_percent, tax_amount, cgst_amount, sgst_amount, igst_amount, total)
                        VALUES (:eid, :iid, :name, :desc, :qty, :rate, :amt, :tax_p, :tax_a, :cgst, :sgst, :igst, :total)');
                    $db->bind(':eid', $id);
                    $item_id = isset($item['item_id']) && $item['item_id'] > 0 ? $item['item_id'] : null;
                    $db->bind(':iid', $item_id);
                    $db->bind(':name', $item['name']);
                    $db->bind(':desc', $item['description']);
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

                $db->query("COMMIT");
                $db->execute();

                $_SESSION['flash_message'] = 'Estimate updated successfully!';
                $_SESSION['flash_type'] = 'success';
                $this->redirect('estimates/show/' . $id);
            } catch (Exception $e) {
                $db->query("ROLLBACK");
                $db->execute();
                $_SESSION['flash_message'] = 'Error updating estimate: ' . $e->getMessage();
                $_SESSION['flash_type'] = 'danger';
                $this->redirect('estimates/index');
            }
        } else {
            $items = $this->estimateModel->getEstimateItems($id);
            $all_items = $this->itemModel->getItemsByCompanyId($_SESSION['company_id']);
            $customers = $this->ledgerModel->getLedgersByCompanyId($_SESSION['company_id']);

            $data = [
                'nav' => 'estimates',
                'edit_mode' => true,
                'estimate' => $estimate,
                'items' => $items,
                'customers' => $customers,
                'all_items' => $all_items
            ];
            $this->view('estimates/edit', $data);
        }
    }

    public function delete($id)
    {
        $estimate = $this->estimateModel->getEstimateById($id);

        if (!$estimate || $estimate->company_id != $_SESSION['company_id']) {
            $_SESSION['flash_message'] = 'Invalid estimate or access denied.';
            $_SESSION['flash_type'] = 'danger';
            $this->redirect('estimates/index');
            return;
        }

        try {
            $db = new Database();
            $db->query("START TRANSACTION");
            $db->execute();

            // Delete estimate items
            $db->query("DELETE FROM estimate_items WHERE estimate_id = :eid");
            $db->bind(':eid', $id);
            $db->execute();

            // Delete estimate
            $db->query("DELETE FROM estimates WHERE id = :eid");
            $db->bind(':eid', $id);
            $db->execute();

            $db->query("COMMIT");
            $db->execute();

            $_SESSION['flash_message'] = 'Estimate deleted successfully.';
            $_SESSION['flash_type'] = 'success';
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $db->execute();

            $_SESSION['flash_message'] = 'Error deleting estimate: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
        }

        $this->redirect('estimates/index');
    }
}
