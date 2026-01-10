<?php
class RolesController extends Controller
{
    private $roleModel;

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth/login');
        }
        $this->roleModel = $this->model('Role');
    }

    public function index()
    {
        $roles = $this->roleModel->getRoles($_SESSION['company_id']);
        $data = [
            'roles' => $roles,
            'nav' => 'settings' // Keep it under settings or create new nav
        ];
        $this->view('roles/index', $data);
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            verify_csrf();

            // Process permissions
            $permissions = [];
            if (isset($_POST['permissions'])) {
                foreach ($_POST['permissions'] as $perm) {
                    $permissions[$perm] = true;
                }
            }

            $data = [
                'company_id' => $_SESSION['company_id'],
                'name' => trim($_POST['name']),
                'permissions' => json_encode($permissions),
                'error' => ''
            ];

            if (empty($data['name'])) {
                $data['error'] = 'Please enter role name';
                $this->view('roles/create', $data);
            } else {
                if ($this->roleModel->addRole($data)) {
                    $this->redirect('roles/index');
                } else {
                    die('Something went wrong');
                }
            }
        } else {
            $data = [
                'name' => '',
                'permissions' => [],
                'all_permissions' => $this->getAvailablePermissions()
            ];
            $this->view('roles/create', $data);
        }
    }

    public function edit($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            verify_csrf();

            // Process permissions
            $permissions = [];
            if (isset($_POST['permissions'])) {
                foreach ($_POST['permissions'] as $perm) {
                    $permissions[$perm] = true;
                }
            }

            $data = [
                'id' => $id,
                'name' => trim($_POST['name']),
                'permissions' => json_encode($permissions),
                'error' => ''
            ];

            if (empty($data['name'])) {
                $data['error'] = 'Please enter role name';
                $this->view('roles/edit', $data);
            } else {
                if ($this->roleModel->updateRole($data)) {
                    $this->redirect('roles/index');
                } else {
                    die('Something went wrong');
                }
            }
        } else {
            $role = $this->roleModel->getRoleById($id);

            // Check if role exists
            if (!$role) {
                $this->redirect('roles/index');
            }

            $data = [
                'id' => $id,
                'name' => $role->name,
                'permissions' => json_decode($role->permissions, true) ?? [],
                'all_permissions' => $this->getAvailablePermissions()
            ];
            $this->view('roles/edit', $data);
        }
    }

    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            verify_csrf();
            // Prevent deleting Super Admin (assuming ID 1)
            if ($id == 1) {
                // Flash message ideal here
                $this->redirect('roles/index');
                return;
            }

            if ($this->roleModel->deleteRole($id)) {
                $this->redirect('roles/index');
            } else {
                die('Something went wrong');
            }
        } else {
            $this->redirect('roles/index');
        }
    }

    private function getAvailablePermissions()
    {
        return [
            // Company & User Management
            'manage_company' => 'Manage Company Settings',
            'manage_users' => 'Manage Users',
            'manage_roles' => 'Manage Roles & Permissions',

            // Masters - Account Groups (NEW)
            'view_account_groups' => 'View Account Groups',
            'manage_account_groups' => 'Create/Edit/Delete Account Groups',

            // Masters - Ledgers
            'view_ledgers' => 'View All Accounts (Ledgers)',
            'manage_ledgers' => 'Create/Edit/Delete Ledgers',

            // Masters - Items/Products
            'view_items' => 'View Products/Services',
            'manage_items' => 'Create/Edit/Delete Products/Services',

            // Sales Module
            'view_sales' => 'View Sales Invoices',
            'create_sales' => 'Create Sales Invoices',
            'edit_sales' => 'Edit Sales Invoices',
            'delete_sales' => 'Delete Sales Invoices',

            // Purchase Module
            'view_purchases' => 'View Purchase Bills',
            'create_purchases' => 'Create Purchase Bills',
            'edit_purchases' => 'Edit Purchase Bills',
            'delete_purchases' => 'Delete Purchase Bills',

            // Estimates/Quotations (NEW)
            'view_estimates' => 'View Estimates/Quotations',
            'create_estimates' => 'Create Estimates/Quotations',
            'edit_estimates' => 'Edit Estimates/Quotations',
            'delete_estimates' => 'Delete Estimates/Quotations',

            // Returns (NEW)
            'view_returns' => 'View Sales/Purchase Returns',
            'create_returns' => 'Create Sales/Purchase Returns',
            'edit_returns' => 'Edit Sales/Purchase Returns',
            'delete_returns' => 'Delete Sales/Purchase Returns',

            // Vouchers/Transactions
            'view_vouchers' => 'View All Vouchers',
            'create_vouchers' => 'Create Vouchers',
            'edit_vouchers' => 'Edit Vouchers',
            'delete_vouchers' => 'Delete Vouchers',

            // Receipts & Payments
            'create_receipts' => 'Record Receipts (Money In)',
            'create_payments' => 'Record Payments (Money Out)',

            // Reports & Analytics
            'view_reports' => 'View All Reports',
            'view_dashboard' => 'View Dashboard',
            'export_reports' => 'Export Reports (PDF/Excel)',

            // Full Access Options
            'accounting' => 'Full Accounting Access (All Modules)',
            'accounting_view' => 'View-Only Accounting Access',
            'entry' => 'Data Entry Only (No Delete)'
        ];
    }
}
