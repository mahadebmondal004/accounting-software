<?php
class ReportsController extends Controller
{
    private $reportModel;
    private $ledgerModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        if (!isset($_SESSION['user_id']))
            $this->redirect('auth/login');
        if (!isset($_SESSION['company_id']))
            $this->redirect('companies/index');

        $this->reportModel = $this->model('Report');
        $this->ledgerModel = $this->model('Ledger');
    }

    public function index()
    {
        $data = ['nav' => 'reports'];
        $this->view('reports/index', $data);
    }

    public function trial_balance()
    {
        $date = $_GET['date'] ?? date('Y-m-d');

        $records = $this->reportModel->getTrialBalance($_SESSION['company_id'], $date);

        // Process records to calculate Closing Balances
        $processed = [];
        $totalDr = 0;
        $totalCr = 0;

        foreach ($records as $r) {
            $initial = ($r->opening_balance_type == 'Dr') ? $r->opening_balance : -$r->opening_balance;
            $current = $initial + ($r->total_debit - $r->total_credit);

            if (abs($current) < 0.01)
                continue; // Skip zero balance

            $processed[] = [
                'name' => $r->name,
                'group' => $r->group_name,
                'debit' => ($current > 0) ? $current : 0,
                'credit' => ($current < 0) ? abs($current) : 0
            ];

            if ($current > 0)
                $totalDr += $current;
            else
                $totalCr += abs($current);
        }

        $data = [
            'nav' => 'reports',
            'date' => $date,
            'records' => $processed,
            'totalDr' => $totalDr,
            'totalCr' => $totalCr
        ];

        $this->view('reports/trial_balance', $data);
    }

    public function ledger_statement()
    {
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-d');
        $ledger_id = $_GET['ledger_id'] ?? 0;

        $ledgers = $this->ledgerModel->getLedgersByCompanyId($_SESSION['company_id']);
        $transactions = [];
        $opening_balance = 0;

        if ($ledger_id) {
            $opening_balance = $this->reportModel->getOpeningBalanceForStatement($ledger_id, $start_date);
            $transactions = $this->reportModel->getLedgerStatement($ledger_id, $start_date, $end_date);
        }

        $data = [
            'nav' => 'reports',
            'ledgers' => $ledgers,
            'ledger_id' => $ledger_id,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'opening_balance' => $opening_balance,
            'transactions' => $transactions
        ];

        $this->view('reports/ledger_statement', $data);

    }
    public function profit_loss()
    {
        $from_date = $_GET['from_date'] ?? date('Y-04-01'); // Assume Financial Year Start
        if (date('m') < 4)
            $from_date = date('Y-04-01', strtotime('-1 year'));

        $to_date = $_GET['to_date'] ?? date('Y-m-d');

        $income_groups = $this->reportModel->getAccountGroupsSummary($_SESSION['company_id'], 'Income', $to_date);
        $expense_groups = $this->reportModel->getAccountGroupsSummary($_SESSION['company_id'], 'Expenses', $to_date);

        // Calculate Totals - For P&L we want movement within period usually, or closing balance?
        // Standard P&L is for a period. 
        // My Model getAccountGroupsSummary calculates Balance as of date (Opening + Net). 
        // For Nominal accounts (Inc/Exp), Opening Balance *should* be zero at start of FY. 
        // So Net Balance as of date is effectively YTD P&L.

        $total_income = 0;
        foreach ($income_groups as $g) {
            // Income is Credit nature, so net_balance is usually negative. We report as positive magnitude.
            $g->amount = abs($g->net_balance);
            $total_income += $g->amount;
        }

        $total_expense = 0;
        foreach ($expense_groups as $g) {
            // Expense is Debit nature, usually positive.
            $g->amount = abs($g->net_balance);
            $total_expense += $g->amount;
        }

        $net_profit = $total_income - $total_expense;

        $data = [
            'nav' => 'reports',
            'from_date' => $from_date,
            'to_date' => $to_date,
            'income_groups' => $income_groups,
            'expense_groups' => $expense_groups,
            'total_income' => $total_income,
            'total_expense' => $total_expense,
            'net_profit' => $net_profit
        ];

        $this->view('reports/profit_loss', $data);
    }

    public function balance_sheet()
    {
        $as_of_date = $_GET['date'] ?? date('Y-m-d');

        // 1. Get Assets
        $assets = $this->reportModel->getAccountGroupsSummary($_SESSION['company_id'], 'Assets', $as_of_date);

        // 2. Get Liabilities
        $liabilities = $this->reportModel->getAccountGroupsSummary($_SESSION['company_id'], 'Liabilities', $as_of_date);

        // 3. Get Equity
        $equity = $this->reportModel->getAccountGroupsSummary($_SESSION['company_id'], 'Equity', $as_of_date);

        // 4. Calculate Net Profit (Retained Earnings) to balance
        // We need Profit from Start of Time (or last Closed FY) up to As Of Date.
        // Assuming single FY or continuous running for now.
        // We use a start date far back or start of active FY depending on how we handle closing.
        // For simplicity: Net Profit of *Current FY* + Retained Earnings (Equity).
        // Let's rely on getNetProfit for *All Time* effectively if we want strict balance ?
        // Or assume Opening Balances of Assets/Liabs account for previous years.
        // We need P&L for *current period* that hasn't been closed into Equity yet.

        // Determine start of FY for the selected date
        $fy_start = date('Y-04-01', strtotime($as_of_date));
        if (date('m', strtotime($as_of_date)) < 4)
            $fy_start = date('Y-04-01', strtotime($as_of_date . ' - 1 year'));

        $current_profit = $this->reportModel->getNetProfit($_SESSION['company_id'], $fy_start, $as_of_date);

        $total_assets = 0;
        foreach ($assets as $a)
            $total_assets += $a->net_balance;

        $total_liabilities = 0;
        foreach ($liabilities as $l)
            $total_liabilities += abs($l->net_balance); // Credit nature

        $total_equity = 0;
        foreach ($equity as $e)
            $total_equity += abs($e->net_balance); // Credit nature

        $data = [
            'nav' => 'reports',
            'date' => $as_of_date,
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'current_profit' => $current_profit,
            'total_assets' => $total_assets,
            'total_liabilities' => $total_liabilities,
            'total_equity' => $total_equity
        ];

        $this->view('reports/balance_sheet', $data);
    }
    public function day_book()
    {
        $date = $_GET['date'] ?? date('Y-m-d');
        $vouchers = $this->reportModel->getDayBook($_SESSION['company_id'], $date);

        $data = [
            'nav' => 'reports',
            'date' => $date,
            'vouchers' => $vouchers
        ];
        $this->view('reports/day_book', $data);
    }

    public function book_report($type)
    {
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-d');
        $ledgers = $this->ledgerModel->getLedgersByType($_SESSION['company_id'], $type); // e.g. 'Cash' or 'Bank'

        // Prevent undefined offset if no ledgers
        $selected_ledger = $_GET['ledger_id'] ?? ($ledgers[0]->id ?? 0);

        $transactions = [];
        $opening = 0;

        if ($selected_ledger) {
            $opening = $this->reportModel->getOpeningBalanceForStatement($selected_ledger, $start_date);
            $transactions = $this->reportModel->getLedgerStatement($selected_ledger, $start_date, $end_date);
        }

        $data = [
            'nav' => 'reports',
            'type' => $type,
            'ledgers' => $ledgers,
            'selected_ledger' => $selected_ledger,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'opening' => $opening,
            'transactions' => $transactions
        ];

        $this->view('reports/book_view', $data);
    }

    public function gst_report()
    {
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-d');
        $tax_rows = $this->reportModel->getGSTSummary($_SESSION['company_id'], $start_date, $end_date);

        $data = [
            'nav' => 'reports',
            'start_date' => $start_date,
            'end_date' => $end_date,
            'tax_rows' => $tax_rows
        ];

        $this->view('reports/gst_report', $data);
    }
}
