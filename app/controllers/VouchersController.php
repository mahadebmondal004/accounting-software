<?php
class VouchersController extends Controller
{
    private $voucherModel;
    private $ledgerModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth/login');
        }
        if (!isset($_SESSION['company_id'])) {
            $this->redirect('companies/index');
        }

        $this->voucherModel = $this->model('Voucher');
        $this->ledgerModel = $this->model('Ledger');
    }

    public function index()
    {
        $vouchers = $this->voucherModel->getVouchersByCompanyId($_SESSION['company_id']);
        $data = [
            'vouchers' => $vouchers,
            'nav' => 'vouchers'
        ];
        $this->view('vouchers/index', $data);
    }

    public function create($type = 'Journal')
    {
        // Ensure FY exists
        $fy_id = $this->voucherModel->checkAndCreateFY($_SESSION['company_id']);
        if (!$fy_id)
            die("Unable to set Financial Year");

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            verify_csrf();
            // Complex handling of arrays
            // $_POST['entries_ledger_id'] = [1, 2]
            // $_POST['entries_debit'] = [100, 0]
            // $_POST['entries_credit'] = [0, 100]

            $input = filter_input_array(INPUT_POST, FILTER_DEFAULT); // We need arrays

            $voucherData = [
                'company_id' => $_SESSION['company_id'],
                'financial_year_id' => $fy_id,
                'voucher_type' => $input['voucher_type'],
                'voucher_number' => $input['voucher_number'],
                'voucher_date' => $input['voucher_date'],
                'narration' => trim($input['narration']),
                'total_amount' => 0, // Calculated
                'created_by' => $_SESSION['user_id']
            ];

            $entries = [];
            $totalDebit = 0;
            $totalCredit = 0;

            if (isset($input['ledger_id']) && is_array($input['ledger_id'])) {
                for ($i = 0; $i < count($input['ledger_id']); $i++) {
                    if (empty($input['ledger_id'][$i]))
                        continue; // Skip empty rows

                    $dr = floatval($input['debit'][$i]);
                    $cr = floatval($input['credit'][$i]);

                    if ($dr == 0 && $cr == 0)
                        continue;

                    $entries[] = [
                        'ledger_id' => $input['ledger_id'][$i],
                        'debit' => $dr,
                        'credit' => $cr,
                        'description' => '' // Line narration if needed
                    ];

                    $totalDebit += $dr;
                    $totalCredit += $cr;
                }
            }

            // Validation
            // 1. Double Entry Rule
            if (abs($totalDebit - $totalCredit) > 0.01) { // Floating point tolerance
                $data = $this->getCreateData($type);
                $data['error'] = "Total Debit ($totalDebit) does not match Total Credit ($totalCredit).";
                $data['input'] = $input;
                $this->view('vouchers/create', $data);
                return;
            }

            if (count($entries) < 2) {
                $data = $this->getCreateData($type);
                $data['error'] = "At least two entries are required.";
                $data['input'] = $input;
                $this->view('vouchers/create', $data);
                return;
            }

            $voucherData['total_amount'] = $totalDebit;

            if ($this->voucherModel->createVoucher($voucherData, $entries)) {
                $this->redirect('vouchers/index');
            } else {
                die("Failed to save voucher");
            }

        } else {
            $data = $this->getCreateData($type);
            // Auto Generate Number
            $data['voucher_number'] = $this->voucherModel->generateVoucherNumber($_SESSION['company_id'], $type);
            $this->view('vouchers/create', $data);
        }
    }

    public function show($id)
    {
        $voucher = $this->voucherModel->getVoucherById($id);

        if (!$voucher || $voucher->company_id != $_SESSION['company_id']) {
            $this->redirect('vouchers/index');
            return;
        }

        $entries = $this->voucherModel->getVoucherEntries($id);

        $data = [
            'voucher' => $voucher,
            'entries' => $entries,
            'nav' => 'vouchers'
        ];
        $this->view('vouchers/view', $data);
    }

    public function print($id)
    {
        $voucher = $this->voucherModel->getVoucherById($id);

        if (!$voucher || $voucher->company_id != $_SESSION['company_id']) {
            $this->redirect('vouchers/index');
            return;
        }

        $entries = $this->voucherModel->getVoucherEntries($id);
        $company = $this->model('Company')->getCompanyById($_SESSION['company_id']);

        $data = [
            'voucher' => $voucher,
            'entries' => $entries,
            'company' => $company
        ];
        $this->view('vouchers/print', $data);
    }

    private function getCreateData($type)
    {
        $ledgers = $this->ledgerModel->getLedgersByCompanyId($_SESSION['company_id']);
        return [
            'type' => $type,
            'ledgers' => $ledgers,
            'voucher_date' => date('Y-m-d'),
            'voucher_number' => '',
            'narration' => '',
            'error' => '',
            'input' => []
        ];
    }
}
