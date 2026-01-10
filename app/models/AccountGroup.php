<?php
class AccountGroup
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    // Get all groups for a company
    public function getGroupsByCompanyId($company_id)
    {
        $this->db->query('SELECT ag.*, 
                                 pg.name as parent_name
                          FROM account_groups ag
                          LEFT JOIN account_groups pg ON ag.parent_id = pg.id
                          WHERE ag.company_id = :company_id
                          ORDER BY ag.nature, ag.name');
        $this->db->bind(':company_id', $company_id);
        return $this->db->resultSet();
    }

    // Get group by ID
    public function getGroupById($id)
    {
        $this->db->query('SELECT * FROM account_groups WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Get parent groups (top level)
    public function getParentGroups($company_id)
    {
        $this->db->query('SELECT * FROM account_groups 
                          WHERE company_id = :company_id 
                          AND parent_id IS NULL
                          ORDER BY name');
        $this->db->bind(':company_id', $company_id);
        return $this->db->resultSet();
    }

    // Get all groups for dropdown (hierarchical)
    public function getGroupsForDropdown($company_id)
    {
        $this->db->query('SELECT ag.*, 
                                 pg.name as parent_name
                          FROM account_groups ag
                          LEFT JOIN account_groups pg ON ag.parent_id = pg.id
                          WHERE ag.company_id = :company_id
                          ORDER BY ag.nature, ag.parent_id, ag.name');
        $this->db->bind(':company_id', $company_id);
        return $this->db->resultSet();
    }

    // Create new group
    public function createGroup($data)
    {
        $this->db->query('INSERT INTO account_groups 
                          (company_id, parent_id, name, code, nature, description) 
                          VALUES 
                          (:company_id, :parent_id, :name, :code, :nature, :description)');

        $this->db->bind(':company_id', $data['company_id']);
        $this->db->bind(':parent_id', $data['parent_id'] ?: null);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':code', $data['code']);
        $this->db->bind(':nature', $data['nature']);
        $this->db->bind(':description', $data['description']);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    // Update group
    public function updateGroup($data)
    {
        $this->db->query('UPDATE account_groups 
                          SET parent_id = :parent_id,
                              name = :name,
                              code = :code,
                              nature = :nature,
                              description = :description
                          WHERE id = :id');

        $this->db->bind(':id', $data['id']);
        $this->db->bind(':parent_id', $data['parent_id'] ?: null);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':code', $data['code']);
        $this->db->bind(':nature', $data['nature']);
        $this->db->bind(':description', $data['description']);

        return $this->db->execute();
    }

    // Delete group
    public function deleteGroup($id)
    {
        // Check if group has children
        $this->db->query('SELECT COUNT(*) as count FROM account_groups WHERE parent_id = :id');
        $this->db->bind(':id', $id);
        $result = $this->db->single();

        if ($result->count > 0) {
            return false; // Cannot delete group with children
        }

        // Check if group has ledgers
        $this->db->query('SELECT COUNT(*) as count FROM ledgers WHERE group_id = :id');
        $this->db->bind(':id', $id);
        $result = $this->db->single();

        if ($result->count > 0) {
            return false; // Cannot delete group with ledgers
        }

        $this->db->query('DELETE FROM account_groups WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Get groups by nature
    public function getGroupsByNature($company_id, $nature)
    {
        $this->db->query('SELECT * FROM account_groups 
                          WHERE company_id = :company_id 
                          AND nature = :nature
                          ORDER BY name');
        $this->db->bind(':company_id', $company_id);
        $this->db->bind(':nature', $nature);
        return $this->db->resultSet();
    }
}
?>