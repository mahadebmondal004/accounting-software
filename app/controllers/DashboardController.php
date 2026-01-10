<?php
class DashboardController extends Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth/login');
        }
    }

    public function index()
    {
        // Handle company selection
        if (isset($_GET['company_id'])) {
            $requestedCompanyId = $_GET['company_id'];

            // SECURITY CHECK: Verify user has access to this company
            $companyModel = $this->model('Company');

            // Check if user is super admin
            $isSuperAdmin = isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'] == 1;

            if ($isSuperAdmin) {
                // Super admin can access any company
                $company = $companyModel->getCompanyById($requestedCompanyId);
            } else {
                // Regular users can only access their assigned companies
                $userCompanies = $companyModel->getCompaniesByUserId($_SESSION['user_id']);
                $hasAccess = false;
                $company = null;

                foreach ($userCompanies as $uc) {
                    if ($uc->id == $requestedCompanyId) {
                        $hasAccess = true;
                        $company = $uc;
                        break;
                    }
                }

                if (!$hasAccess) {
                    // User doesn't have access to this company
                    die('Access Denied: You do not have permission to access this company.');
                }
            }

            if ($company) {
                $_SESSION['company_id'] = $requestedCompanyId;
                $_SESSION['company_name'] = $company->name;
                $_SESSION['company_logo'] = $company->logo_path ?? null;

                // Load Permissions
                $roleModel = $this->model('Role');
                if ($isSuperAdmin) {
                    // Super admin has all permissions
                    $_SESSION['permissions'] = ['all' => true];
                } else {
                    $permissions = $roleModel->getPermissionsByUserIdAndCompanyId($_SESSION['user_id'], $_SESSION['company_id']);
                    $_SESSION['permissions'] = $permissions;
                }
            }
        }

        // If no company selected, redirect to companies list
        if (!isset($_SESSION['company_id'])) {
            $this->redirect('companies/index');
        }

        // Get period filter (default: month)
        $period = $_GET['period'] ?? 'month';
        $dateRange = $this->getDateRange($period);

        // Load Dashboard Model
        $dashboardModel = $this->model('Dashboard');
        $companyId = $_SESSION['company_id'];

        // Get real company-wise data with date filter
        $income = $dashboardModel->getTotalIncome($companyId, $dateRange['start'], $dateRange['end']);
        $expenses = $dashboardModel->getTotalExpenses($companyId, $dateRange['start'], $dateRange['end']);
        $profit = $income - $expenses;
        $receivables = $dashboardModel->getTotalReceivables($companyId);
        $payables = $dashboardModel->getTotalPayables($companyId);
        $cashBank = $dashboardModel->getCashBankBalance($companyId);

        // Get counts
        $totalCustomers = $dashboardModel->getTotalCustomers($companyId);
        $totalSuppliers = $dashboardModel->getTotalSuppliers($companyId);
        $totalItems = $dashboardModel->getTotalItems($companyId);

        // Get recent transactions (last 3)
        $recentInvoices = $dashboardModel->getRecentInvoices($companyId, 3);

        // Period labels
        $periodLabels = [
            'today' => 'Today',
            'week' => 'This Week',
            'month' => 'This Month',
            'year' => 'This Year',
            'lifetime' => 'Lifetime'
        ];

        $data = [
            'title' => 'Dashboard',
            'nav' => 'dashboard',
            'income' => $income,
            'expenses' => $expenses,
            'profit' => $profit,
            'receivables' => $receivables,
            'payables' => $payables,
            'cash_bank' => $cashBank,
            'total_customers' => $totalCustomers,
            'total_suppliers' => $totalSuppliers,
            'total_items' => $totalItems,
            'recent_invoices' => $recentInvoices,
            'period' => $period,
            'period_label' => $periodLabels[$period] ?? 'This Month'
        ];

        $this->view('dashboard/index', $data);
    }

    private function getDateRange($period)
    {
        $today = date('Y-m-d');
        $ranges = [
            'today' => [
                'start' => $today,
                'end' => $today
            ],
            'week' => [
                'start' => date('Y-m-d', strtotime('monday this week')),
                'end' => date('Y-m-d', strtotime('sunday this week'))
            ],
            'month' => [
                'start' => date('Y-m-01'),
                'end' => date('Y-m-t')
            ],
            'year' => [
                'start' => date('Y-01-01'),
                'end' => date('Y-12-31')
            ],
            'lifetime' => [
                'start' => '2000-01-01',
                'end' => '2099-12-31'
            ]
        ];

        return $ranges[$period] ?? $ranges['month'];
    }
}
