<?php
class Role
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    // Get all roles for a company
    public function getRoles($company_id)
    {
        $this->db->query("SELECT * FROM roles WHERE company_id = :company_id ORDER BY name ASC");
        $this->db->bind(':company_id', $company_id);
        return $this->db->resultSet();
    }

    // Get role by ID
    public function getRoleById($id)
    {
        $this->db->query("SELECT * FROM roles WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Add new role
    public function addRole($data)
    {
        $this->db->query("INSERT INTO roles (company_id, name, permissions) VALUES (:company_id, :name, :permissions)");
        $this->db->bind(':company_id', $data['company_id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':permissions', $data['permissions']);

        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // Update existing role
    public function updateRole($data)
    {
        $this->db->query("UPDATE roles SET name = :name, permissions = :permissions WHERE id = :id");
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':permissions', $data['permissions']);

        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // Delete role
    public function deleteRole($id)
    {
        $this->db->query("DELETE FROM roles WHERE id = :id");
        $this->db->bind(':id', $id);

        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }


    public function getPermissionsByUserIdAndCompanyId($user_id, $company_id)
    {
        $this->db->query("SELECT r.permissions 
                          FROM roles r 
                          JOIN user_companies uc ON r.id = uc.role_id 
                          WHERE uc.user_id = :user_id AND uc.company_id = :company_id");
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':company_id', $company_id);

        $row = $this->db->single();
        if ($row) {
            return json_decode($row->permissions, true) ?? [];
        }
        return [];
    }
}
