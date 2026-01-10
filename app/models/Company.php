<?php
class Company
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function getCompaniesByUserId($user_id)
    {
        $this->db->query('SELECT c.*, 
                                 (SELECT start_date FROM financial_years fy WHERE fy.company_id = c.id AND fy.is_active = 1 LIMIT 1) as financial_year_start 
                          FROM companies c 
                          JOIN user_companies uc ON c.id = uc.company_id 
                          WHERE uc.user_id = :user_id');
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }

    public function getAllCompanies()
    {
        $this->db->query('SELECT c.*, 
                                 (SELECT start_date FROM financial_years fy WHERE fy.company_id = c.id AND fy.is_active = 1 LIMIT 1) as financial_year_start 
                          FROM companies c 
                          ORDER BY c.created_at DESC');
        return $this->db->resultSet();
    }

    public function addCompany($data)
    {
        $this->db->query('INSERT INTO companies (name, email, phone, address, gstin) 
                          VALUES(:name, :email, :phone, :address, :gstin)');

        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':gstin', $data['gstin']);

        if ($this->db->execute()) {
            $companyId = $this->db->lastInsertId();

            // Create Financial Year
            $start_date = $data['financial_year_start'] ?? date('Y-04-01');
            $year = date('Y', strtotime($start_date));
            $nextYear = $year + 1;
            $end_date = $nextYear . '-03-31';
            $fyName = $year . '-' . $nextYear;

            $this->db->query('INSERT INTO financial_years (company_id, name, start_date, end_date, is_active) VALUES (:company_id, :name, :start_date, :end_date, 1)');
            $this->db->bind(':company_id', $companyId);
            $this->db->bind(':name', $fyName);
            $this->db->bind(':start_date', $start_date);
            $this->db->bind(':end_date', $end_date);
            $this->db->execute();

            $this->createDefaultGroups($companyId);
            $this->createDefaultRoles($companyId);
            return $companyId;
        } else {
            return false;
        }
    }

    private function createDefaultGroups($company_id)
    {
        // Define default groups with natures
        $groups = [
            ['name' => 'Assets', 'nature' => 'Assets'],
            ['name' => 'Liabilities', 'nature' => 'Liabilities'],
            ['name' => 'Equity', 'nature' => 'Equity'],
            ['name' => 'Income', 'nature' => 'Income'],
            ['name' => 'Expenses', 'nature' => 'Expenses'],
        ];

        $map = [];

        // Insert Main Groups
        foreach ($groups as $g) {
            $this->db->query('INSERT INTO account_groups (company_id, name, nature, parent_id) VALUES (:company_id, :name, :nature, NULL)');
            $this->db->bind(':company_id', $company_id);
            $this->db->bind(':name', $g['name']);
            $this->db->bind(':nature', $g['nature']);
            $this->db->execute();
            $map[$g['name']] = $this->db->lastInsertId();
        }

        // Sub groups definition
        $subGroups = [
            ['name' => 'Current Assets', 'parent' => 'Assets', 'nature' => 'Assets'],
            ['name' => 'Fixed Assets', 'parent' => 'Assets', 'nature' => 'Assets'],
            ['name' => 'Current Liabilities', 'parent' => 'Liabilities', 'nature' => 'Liabilities'],
            ['name' => 'Sales Account', 'parent' => 'Income', 'nature' => 'Income'],
            ['name' => 'Purchase Account', 'parent' => 'Expenses', 'nature' => 'Expenses'],
            ['name' => 'Indirect Expenses', 'parent' => 'Expenses', 'nature' => 'Expenses'],
            ['name' => 'Direct Expenses', 'parent' => 'Expenses', 'nature' => 'Expenses'],
            ['name' => 'Duties & Taxes', 'parent' => 'Liabilities', 'nature' => 'Liabilities'],
            ['name' => 'Bank Accounts', 'parent' => 'Assets', 'nature' => 'Assets'],
            ['name' => 'Cash-in-hand', 'parent' => 'Assets', 'nature' => 'Assets'],
            ['name' => 'Sundry Debtors', 'parent' => 'Assets', 'nature' => 'Assets'],
            ['name' => 'Sundry Creditors', 'parent' => 'Liabilities', 'nature' => 'Liabilities'],
        ];

        foreach ($subGroups as $sg) {
            if (isset($map[$sg['parent']])) {
                $parentId = $map[$sg['parent']];
                $this->db->query('INSERT INTO account_groups (company_id, name, nature, parent_id) VALUES (:company_id, :name, :nature, :parent_id)');
                $this->db->bind(':company_id', $company_id);
                $this->db->bind(':name', $sg['name']);
                $this->db->bind(':nature', $sg['nature']);
                $this->db->bind(':parent_id', $parentId);
                $this->db->execute();
            }
        }
    }

    private function createDefaultRoles($company_id)
    {
        // Define default roles
        $roles = [
            ['name' => 'Company Admin', 'permissions' => '{"manage_company": true, "manage_users": true, "accounting": true, "reports": true, "manage_roles": true}'],
            ['name' => 'Accountant', 'permissions' => '{"accounting": true, "reports": true}'],
            ['name' => 'Data Entry', 'permissions' => '{"entry": true}'],
            ['name' => 'Viewer', 'permissions' => '{"view_reports": true}']
        ];

        foreach ($roles as $role) {
            $this->db->query("INSERT INTO roles (company_id, name, permissions) VALUES (:company_id, :name, :permissions)");
            $this->db->bind(':company_id', $company_id);
            $this->db->bind(':name', $role['name']);
            $this->db->bind(':permissions', $role['permissions']);
            $this->db->execute();
        }
    }

    public function assignUserToCompany($user_id, $company_id, $role_id)
    {
        $this->db->query('INSERT INTO user_companies (user_id, company_id, role_id) VALUES(:user_id, :company_id, :role_id)');
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':company_id', $company_id);
        $this->db->bind(':role_id', $role_id);
        return $this->db->execute();
    }

    public function getCompanyById($id)
    {
        $this->db->query('SELECT * FROM companies WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function updateCompany($data)
    {
        $query = 'UPDATE companies SET 
                            name = :name, 
                            email = :email, 
                            phone = :phone, 
                            address = :address, 
                            gstin = :gstin,
                            city = :city,
                            state = :state,
                            pincode = :pincode,
                            country = :country,
                            pan_no = :pan_no,
                            website = :website,
                            currency_symbol = :currency_symbol';

        if (!empty($data['logo_path'])) {
            $query .= ', logo_path = :logo_path';
        }

        $query .= ' WHERE id = :id';

        $this->db->query($query);

        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':gstin', $data['gstin']);
        $this->db->bind(':city', $data['city']);
        $this->db->bind(':state', $data['state']);
        $this->db->bind(':pincode', $data['pincode']);
        $this->db->bind(':country', $data['country']);
        $this->db->bind(':pan_no', $data['pan_no']);
        $this->db->bind(':website', $data['website']);
        $this->db->bind(':currency_symbol', $data['currency_symbol']);
        $this->db->bind(':id', $data['id']);

        if (!empty($data['logo_path'])) {
            $this->db->bind(':logo_path', $data['logo_path']);
        }

        return $this->db->execute();
    }
}
