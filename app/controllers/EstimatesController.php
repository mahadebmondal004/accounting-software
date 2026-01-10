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
            for ($i = 0; $i < count($input['item_name']); $i++) {
                if (empty($input['item_name'][$i]))
                    continue;

                $estimateItems[] = [
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
}
