<?php
class Dashboard
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    // Get total income (Sales) for company - from vouchers
    public function getTotalIncome($company_id, $start_date = null, $end_date = null)
    {
        $sql = "SELECT COALESCE(SUM(ve.credit), 0) as total 
                FROM vouchers v
                JOIN voucher_entries ve ON v.id = ve.voucher_id
                JOIN ledgers l ON ve.ledger_id = l.id
                WHERE v.company_id = :company_id 
                AND v.voucher_type = 'Sales'
                AND l.type IN ('General', 'Revenue')";

        if ($start_date && $end_date) {
            $sql .= " AND v.voucher_date BETWEEN :start_date AND :end_date";
        }

        $this->db->query($sql);
        $this->db->bind(':company_id', $company_id);

        if ($start_date && $end_date) {
            $this->db->bind(':start_date', $start_date);
            $this->db->bind(':end_date', $end_date);
        }

        $result = $this->db->single();
        return $result->total ?? 0;
    }

    // Get total expenses (Purchases)
    public function getTotalExpenses($company_id, $start_date = null, $end_date = null)
    {
        $sql = "SELECT COALESCE(SUM(ve.debit), 0) as total 
                FROM vouchers v
                JOIN voucher_entries ve ON v.id = ve.voucher_id
                JOIN ledgers l ON ve.ledger_id = l.id
                WHERE v.company_id = :company_id 
                AND v.voucher_type = 'Purchase'
                AND l.type IN ('General', 'Expense')";

        if ($start_date && $end_date) {
            $sql .= " AND v.voucher_date BETWEEN :start_date AND :end_date";
        }

        $this->db->query($sql);
        $this->db->bind(':company_id', $company_id);

        if ($start_date && $end_date) {
            $this->db->bind(':start_date', $start_date);
            $this->db->bind(':end_date', $end_date);
        }

        $result = $this->db->single();
        return $result->total ?? 0;
    }

    // Get receivables (Customer balances - Debtors)
    public function getTotalReceivables($company_id)
    {
        $this->db->query("SELECT COALESCE(SUM(current_balance), 0) as total 
                          FROM ledgers 
                          WHERE company_id = :company_id 
                          AND type = 'Customer'
                          AND current_balance > 0");
        $this->db->bind(':company_id', $company_id);
        $result = $this->db->single();
        return $result->total ?? 0;
    }

    // Get payables (Supplier balances - Creditors)
    public function getTotalPayables($company_id)
    {
        $this->db->query("SELECT COALESCE(SUM(ABS(current_balance)), 0) as total 
                          FROM ledgers 
                          WHERE company_id = :company_id 
                          AND type = 'Supplier'
                          AND current_balance < 0");
        $this->db->bind(':company_id', $company_id);
        $result = $this->db->single();
        return $result->total ?? 0;
    }

    // Get total customers
    public function getTotalCustomers($company_id)
    {
        $this->db->query("SELECT COUNT(*) as count 
                          FROM ledgers 
                          WHERE company_id = :company_id 
                          AND type = 'Customer'");
        $this->db->bind(':company_id', $company_id);
        $result = $this->db->single();
        return $result->count ?? 0;
    }

    // Get total suppliers
    public function getTotalSuppliers($company_id)
    {
        $this->db->query("SELECT COUNT(*) as count 
                          FROM ledgers 
                          WHERE company_id = :company_id 
                          AND type = 'Supplier'");
        $this->db->bind(':company_id', $company_id);
        $result = $this->db->single();
        return $result->count ?? 0;
    }

    // Get total items/products
    public function getTotalItems($company_id)
    {
        $this->db->query("SELECT COUNT(*) as count 
                          FROM items 
                          WHERE company_id = :company_id");
        $this->db->bind(':company_id', $company_id);
        $result = $this->db->single();
        return $result->count ?? 0;
    }

    // Get recent transactions
    public function getRecentInvoices($company_id, $limit = 5)
    {
        $this->db->query("SELECT v.*, 
                                 (SELECT SUM(debit) FROM voucher_entries WHERE voucher_id = v.id) as total_debit,
                                 (SELECT SUM(credit) FROM voucher_entries WHERE voucher_id = v.id) as total_credit
                          FROM vouchers v
                          WHERE v.company_id = :company_id
                          AND v.voucher_type IN ('Sales', 'Purchase')
                          ORDER BY v.voucher_date DESC
                          LIMIT :limit");
        $this->db->bind(':company_id', $company_id);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    // Get cash/bank balance
    public function getCashBankBalance($company_id)
    {
        $this->db->query("SELECT COALESCE(SUM(current_balance), 0) as total 
                          FROM ledgers 
                          WHERE company_id = :company_id 
                          AND (type = 'Cash' OR type = 'Bank' OR type = 'Bank_Account')");
        $this->db->bind(':company_id', $company_id);
        $result = $this->db->single();
        return $result->total ?? 0;
    }

    // Get monthly sales trend (last 6 months)
    public function getMonthlySalesTrend($company_id)
    {
        $this->db->query("SELECT 
                            DATE_FORMAT(v.voucher_date, '%Y-%m') as month,
                            SUM(ve.credit) as total
                          FROM vouchers v
                          JOIN voucher_entries ve ON v.id = ve.voucher_id
                          WHERE v.company_id = :company_id
                          AND v.voucher_type = 'Sales'
                          AND v.voucher_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                          GROUP BY DATE_FORMAT(v.voucher_date, '%Y-%m')
                          ORDER BY month ASC");
        $this->db->bind(':company_id', $company_id);
        return $this->db->resultSet();
    }
}
?>