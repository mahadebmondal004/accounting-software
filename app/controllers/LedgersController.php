<?php
class LedgersController extends Controller
{
    private $ledgerModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth/login');
        }
        // Ideally check if company is selected in session
        if (!isset($_SESSION['company_id']) && isset($_GET['company_id'])) {
            $_SESSION['company_id'] = $_GET['company_id'];
        }
        // If still no company, redirect to selection
        /* 
        // Commented out for now to allow simple flow testing, ideally should enforce:
        if (!isset($_SESSION['company_id'])) {
             $this->redirect('companies/index');
        }
        */

        $this->ledgerModel = $this->model('Ledger');
    }

    public function index()
    {
        // Fallback for demo if no company selected
        if (!isset($_SESSION['company_id'])) {
            // In a real scenario, force selection. For now, let's grab the first one user has access to or error out.
            // We'll just redirect to companies for safety.
            $this->redirect('companies/index');
            return;
        }

        $ledgers = $this->ledgerModel->getLedgersByCompanyId($_SESSION['company_id']);

        $data = [
            'ledgers' => $ledgers,
            'nav' => 'ledgers'
        ];

        $this->view('ledgers/index', $data);
    }

    public function create()
    {
        if (!isset($_SESSION['company_id'])) {
            $this->redirect('companies/index');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            verify_csrf();
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'company_id' => $_SESSION['company_id'],
                'name' => trim($_POST['name']),
                'group_id' => trim($_POST['group_id']),
                'code' => trim($_POST['code']),
                'opening_balance' => trim($_POST['opening_balance']),
                'opening_balance_type' => trim($_POST['opening_balance_type']),
                'type' => trim($_POST['type']),
                'contact_person' => trim($_POST['contact_person']),
                'email' => trim($_POST['email']),
                'phone' => trim($_POST['phone']),
                'address' => trim($_POST['address']),
                'gstin' => trim($_POST['gstin']),
                'pan_no' => trim($_POST['pan_no']),
                'name_err' => '',
                'group_err' => ''
            ];

            // Validate Name
            if (empty($data['name'])) {
                $data['name_err'] = 'Please enter ledger name';
            }
            if (empty($data['group_id'])) {
                $data['group_err'] = 'Please select account group';
            }

            if (empty($data['name_err']) && empty($data['group_err'])) {
                if ($this->ledgerModel->addLedger($data)) {
                    $this->redirect('ledgers/index');
                } else {
                    die('Something went wrong');
                }
            } else {
                $data['groups'] = $this->ledgerModel->getGroupsByCompanyId($_SESSION['company_id']);
                $this->view('ledgers/create', $data);
            }

        } else {
            $groups = $this->ledgerModel->getGroupsByCompanyId($_SESSION['company_id']);
            $data = [
                'name' => '',
                'group_id' => '',
                'code' => '',
                'opening_balance' => '0.00',
                'opening_balance_type' => 'Dr',
                'type' => 'General',
                'contact_person' => '',
                'email' => '',
                'phone' => '',
                'address' => '',
                'gstin' => '',
                'pan_no' => '',
                'groups' => $groups
            ];

            $this->view('ledgers/create', $data);
        }
    }
}
