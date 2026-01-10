<?php
class Estimate
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function getEstimatesByCompanyId($company_id)
    {
        $this->db->query('SELECT e.*, l.name as customer_name 
                          FROM estimates e
                          JOIN ledgers l ON e.customer_ledger_id = l.id
                          WHERE e.company_id = :company_id
                          ORDER BY e.estimate_date DESC, e.id DESC');
        $this->db->bind(':company_id', $company_id);
        return $this->db->resultSet();
    }

    public function getEstimateById($id)
    {
        $this->db->query('SELECT e.*, l.name as customer_name, l.address as customer_address, l.email as customer_email
                          FROM estimates e
                          JOIN ledgers l ON e.customer_ledger_id = l.id
                          WHERE e.id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getEstimateItems($estimate_id)
    {
        $this->db->query('SELECT * FROM estimate_items WHERE estimate_id = :id');
        $this->db->bind(':id', $estimate_id);
        return $this->db->resultSet();
    }

    public function createEstimate($data, $items)
    {
        try {
            $this->db->query('INSERT INTO estimates (company_id, estimate_number, estimate_date, expiry_date, customer_ledger_id, total_amount, status, notes, created_by) 
                              VALUES(:company_id, :estimate_number, :estimate_date, :expiry_date, :customer_ledger_id, :total_amount, :status, :notes, :created_by)');

            $this->db->bind(':company_id', $data['company_id']);
            $this->db->bind(':estimate_number', $data['estimate_number']);
            $this->db->bind(':estimate_date', $data['estimate_date']);
            $this->db->bind(':expiry_date', $data['expiry_date']);
            $this->db->bind(':customer_ledger_id', $data['customer_ledger_id']);
            $this->db->bind(':total_amount', $data['total_amount']);
            $this->db->bind(':status', 'Draft');
            $this->db->bind(':notes', $data['notes']);
            $this->db->bind(':created_by', $data['created_by']);

            if ($this->db->execute()) {
                $estimate_id = $this->db->lastInsertId();

                foreach ($items as $item) {
                    $this->db->query('INSERT INTO estimate_items (estimate_id, item_id, item_name, quantity, rate, amount, tax_percent, tax_amount, total) 
                                      VALUES(:estimate_id, :item_id, :item_name, :quantity, :rate, :amount, :tax_percent, :tax_amount, :total)');
                    $this->db->bind(':estimate_id', $estimate_id);
                    $this->db->bind(':item_id', $item['item_id']);
                    $this->db->bind(':item_name', $item['name']);
                    $this->db->bind(':quantity', $item['quantity']);
                    $this->db->bind(':rate', $item['rate']);
                    $this->db->bind(':amount', $item['amount']);
                    $this->db->bind(':tax_percent', $item['tax_percent']);
                    $this->db->bind(':tax_amount', $item['tax_amount']);
                    $this->db->bind(':total', $item['total']);
                    $this->db->execute();
                }
                return $estimate_id;
            }
        } catch (Exception $e) {
            return false;
        }
        return false;
    }

    public function generateEstimateNumber($company_id)
    {
        $this->db->query('SELECT estimate_number FROM estimates WHERE company_id = :cid ORDER BY id DESC LIMIT 1');
        $this->db->bind(':cid', $company_id);
        $row = $this->db->single();

        $prefix = "EST";
        $year = date('Y');

        if ($row) {
            $parts = explode('-', $row->estimate_number);
            if (count($parts) >= 3) {
                $seq = intval($parts[2]) + 1;
                return sprintf("%s-%s-%04d", $prefix, $year, $seq);
            }
        }
        return sprintf("%s-%s-0001", $prefix, $year);
    }

    public function updateStatus($id, $status)
    {
        $this->db->query('UPDATE estimates SET status = :status WHERE id = :id');
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
