<?php
class TransfersController extends Controller
{
    private $voucherModel;
    private $ledgerModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        if (!isset($_SESSION['user_id']))
            $this->redirect('auth/login');
        if (!isset($_SESSION['company_id']))
            $this->redirect('companies/index');

        $this->voucherModel = $this->model('Voucher');
        $this->ledgerModel = $this->model('Ledger');
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
                'voucher_type' => 'Contra',
                'voucher_number' => $input['voucher_number'],
                'voucher_date' => $input['voucher_date'],
                'narration' => trim($input['narration']),
                'total_amount' => $input['amount'],
                'created_by' => $_SESSION['user_id']
            ];

            // Contra: Moving Money from A to B.
            // Source (Giver) = Credit. Destination (Receiver) = Debit.

            $entries = [];

            // Entry 1: Credit Source (From)
            $entries[] = [
                'ledger_id' => $input['from_ledger_id'],
                'debit' => 0,
                'credit' => $input['amount'],
                'description' => 'Transfer to ' . $input['to_ledger_name']
            ];

            // Entry 2: Debit Destination (To)
            $entries[] = [
                'ledger_id' => $input['to_ledger_id'],
                'debit' => $input['amount'],
                'credit' => 0,
                'description' => 'Transfer from ' . $input['from_ledger_name']
            ];

            if ($this->voucherModel->createVoucher($voucherData, $entries)) {
                $this->redirect('vouchers/index');
            } else {
                die("Failed to create transfer");
            }

        } else {
            // Get Bank/Cash Accounts
            $all = $this->ledgerModel->getLedgersByCompanyId($_SESSION['company_id']);
            $accounts = [];

            foreach ($all as $l) {
                // Check if Cash or Bank
                if (stripos($l->group_name, 'Cash') !== false || stripos($l->group_name, 'Bank') !== false) {
                    $accounts[] = $l;
                }
            }

            $data = [
                'nav' => 'vouchers',
                'type' => 'Contra',
                'voucher_number' => $this->voucherModel->generateVoucherNumber($_SESSION['company_id'], 'Contra'),
                'voucher_date' => date('Y-m-d'),
                'accounts' => $accounts
            ];
            $this->view('transfers/create', $data);
        }
    }
}
