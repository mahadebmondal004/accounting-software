<?php
class PaymentsController extends Controller
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
                'voucher_type' => 'Payment',
                'voucher_number' => $input['voucher_number'],
                'voucher_date' => $input['voucher_date'],
                'narration' => trim($input['narration']),
                'total_amount' => $input['amount'],
                'created_by' => $_SESSION['user_id']
            ];

            $entries = [];

            // Entry 1: Debit Vendor/Expense (Receiver)
            $entries[] = [
                'ledger_id' => $input['party_ledger_id'],
                'debit' => $input['amount'],
                'credit' => 0,
                'description' => 'Paid to ' . $input['party_name']
            ];

            // Entry 2: Credit Cash/Bank (Money Out)
            $entries[] = [
                'ledger_id' => $input['bank_ledger_id'],
                'debit' => 0,
                'credit' => $input['amount'],
                'description' => 'Payment Made'
            ];

            if ($this->voucherModel->createVoucher($voucherData, $entries)) {
                $this->redirect('vouchers/index');
            } else {
                die("Failed to create payment");
            }

        } else {
            // Get Bank/Cash Accounts
            $all = $this->ledgerModel->getLedgersByCompanyId($_SESSION['company_id']);
            $banks = [];
            $parties = [];

            foreach ($all as $l) {
                // Check if Cash or Bank
                if (stripos($l->group_name, 'Cash') !== false || stripos($l->group_name, 'Bank') !== false) {
                    $banks[] = $l;
                } else {
                    $parties[] = $l; // All others (Vendors, Expenses, etc)
                }
            }

            $data = [
                'nav' => 'vouchers',
                'type' => 'Payment',
                'voucher_number' => $this->voucherModel->generateVoucherNumber($_SESSION['company_id'], 'Payment'),
                'voucher_date' => date('Y-m-d'),
                'banks' => $banks,
                'parties' => $parties
            ];
            $this->view('payments/create', $data);
        }
    }
}
