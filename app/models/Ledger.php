<?php
class Ledger
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function getLedgersByCompanyId($company_id)
    {
        $this->db->query('SELECT DISTINCT l.id, l.name, l.code, l.type, l.opening_balance, l.opening_balance_type, l.current_balance, 
                          l.contact_person, l.email, l.phone, l.address, l.gstin, l.pan_no, 
                          g.name as group_name 
                          FROM ledgers l
                          JOIN account_groups g ON l.group_id = g.id
                          WHERE l.company_id = :company_id
                          ORDER BY l.name ASC');
        $this->db->bind(':company_id', $company_id);
        return $this->db->resultSet();
    }

    public function getGroupsByCompanyId($company_id)
    {
        $this->db->query('SELECT * FROM account_groups WHERE company_id = :company_id ORDER BY name ASC');
        $this->db->bind(':company_id', $company_id);
        return $this->db->resultSet();
    }

    public function addLedger($data)
    {
        $this->db->query('INSERT INTO ledgers (company_id, group_id, name, code, opening_balance, opening_balance_type, type, contact_person, email, phone, address, gstin, pan_no) 
                          VALUES(:company_id, :group_id, :name, :code, :opening_balance, :opening_balance_type, :type, :contact_person, :email, :phone, :address, :gstin, :pan_no)');

        $this->db->bind(':company_id', $data['company_id']);
        $this->db->bind(':group_id', $data['group_id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':code', $data['code']);
        $this->db->bind(':opening_balance', $data['opening_balance']);
        $this->db->bind(':opening_balance_type', $data['opening_balance_type']); // Dr or Cr
        $this->db->bind(':type', $data['type']); // Customer, Supplier, etc.
        $this->db->bind(':contact_person', $data['contact_person']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':gstin', $data['gstin']);
        $this->db->bind(':pan_no', $data['pan_no']);

        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function getLedgerById($id)
    {
        $this->db->query('SELECT * FROM ledgers WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getLedgersByType($company_id, $type)
    {
        $this->db->query('SELECT * FROM ledgers WHERE company_id = :cid AND type = :type ORDER BY name ASC');
        $this->db->bind(':cid', $company_id);
        $this->db->bind(':type', $type);
        return $this->db->resultSet();
    }
}
