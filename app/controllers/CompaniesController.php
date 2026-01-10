<?php
class CompaniesController extends Controller
{
    private $companyModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth/login');
        }
        $this->companyModel = $this->model('Company');
    }

    public function index()
    {
        // Check if user is super admin
        $isSuperAdmin = isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'] == 1;

        if ($isSuperAdmin) {
            // Super admin can see all companies
            $companies = $this->companyModel->getAllCompanies();
        } else {
            // Regular users can only see their assigned companies
            $companies = $this->companyModel->getCompaniesByUserId($_SESSION['user_id']);
        }

        $data = [
            'companies' => $companies,
            'nav' => 'companies',
            'is_super_admin' => $isSuperAdmin
        ];

        $this->view('companies/index', $data);
    }

    public function create()
    {
        // SECURITY: Only Super Admin can create companies
        if (!isset($_SESSION['is_super_admin']) || $_SESSION['is_super_admin'] != 1) {
            die('Access Denied: Only Super Admin can create new companies.');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            verify_csrf();
            // Sanitize
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'phone' => trim($_POST['phone']),
                'address' => trim($_POST['address']),
                'gstin' => trim($_POST['gstin']),
                'financial_year_start' => trim($_POST['financial_year_start']),
                'user_id' => $_SESSION['user_id']
            ];

            if (!empty($data['name'])) {
                $newCompanyId = $this->companyModel->addCompany($data);
                if ($newCompanyId) {
                    // Fetch the Company Admin role for this new company
                    // Since roles are cloned, we need to find the one named 'Company Admin' for this company
                    $roleModel = $this->model('Role');
                    $roles = $roleModel->getRoles($newCompanyId); // This method now filters by company
                    $adminRoleId = null;
                    foreach ($roles as $r) {
                        if ($r->name == 'Company Admin') {
                            $adminRoleId = $r->id;
                            break;
                        }
                    }
                    if (!$adminRoleId && !empty($roles))
                        $adminRoleId = $roles[0]->id; // Fallback

                    $this->companyModel->assignUserToCompany($_SESSION['user_id'], $newCompanyId, $adminRoleId);
                    $this->redirect('companies/index');
                } else {
                    die('Something went wrong');
                }
            } else {
                $this->view('companies/create', $data);
            }
        } else {
            $data = [
                'name' => '',
                'email' => '',
                'phone' => '',
                'address' => '',
                'gstin' => '',
                'financial_year_start' => date('Y-04-01')
            ];
            $this->view('companies/create', $data);
        }
    }
}
