<?php
class Report
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function getTrialBalance($company_id, $as_of_date)
    {
        // Complex query to get balance as of today
        // We take Opening Balance + Sum of entries up to date
        // Note: Opening Balance in DB is typically start of FY.
        // If we strictly follow accounting, we just sum everything if Opening Balance is migrated.

        $sql = "
            SELECT 
                l.id, l.name, l.code, g.name as group_name, g.nature,
                l.opening_balance, l.opening_balance_type,
                COALESCE(SUM(ve.debit), 0) as total_debit,
                COALESCE(SUM(ve.credit), 0) as total_credit
            FROM ledgers l
            JOIN account_groups g ON l.group_id = g.id
            LEFT JOIN voucher_entries ve ON l.id = ve.ledger_id 
            LEFT JOIN vouchers v ON ve.voucher_id = v.id AND v.voucher_date <= :as_of_date
            WHERE l.company_id = :company_id
            GROUP BY l.id
            ORDER BY g.nature, g.name, l.name
        ";

        $this->db->query($sql);
        $this->db->bind(':company_id', $company_id);
        $this->db->bind(':as_of_date', $as_of_date);

        return $this->db->resultSet();
    }

    public function getLedgerStatement($ledger_id, $start_date, $end_date)
    {
        $sql = "
            SELECT v.voucher_date, v.voucher_number, v.voucher_type, v.narration,
                   ve.debit, ve.credit, ve.description
            FROM voucher_entries ve
            JOIN vouchers v ON ve.voucher_id = v.id
            WHERE ve.ledger_id = :ledger_id
            AND v.voucher_date BETWEEN :start_date AND :end_date
            ORDER BY v.voucher_date ASC, v.id ASC
        ";

        $this->db->query($sql);
        $this->db->bind(':ledger_id', $ledger_id);
        $this->db->bind(':start_date', $start_date);
        $this->db->bind(':end_date', $end_date);
        return $this->db->resultSet();
    }

    // Helper to get opening balance before a date for statement
    public function getOpeningBalanceForStatement($ledger_id, $start_date)
    {
        // Get Ledger Initial Opening
        $this->db->query('SELECT opening_balance, opening_balance_type FROM ledgers WHERE id = :id');
        $this->db->bind(':id', $ledger_id);
        $ledger = $this->db->single();

        $initial = ($ledger->opening_balance_type == 'Dr') ? $ledger->opening_balance : -$ledger->opening_balance;

        // Sum moves before start_date
        $sql = "
            SELECT SUM(ve.debit) as dr, SUM(ve.credit) as cr
            FROM voucher_entries ve
            JOIN vouchers v ON ve.voucher_id = v.id
            WHERE ve.ledger_id = :ledger_id
            AND v.voucher_date < :start_date
        ";
        $this->db->query($sql);
        $this->db->bind(':ledger_id', $ledger_id);
        $this->db->bind(':start_date', $start_date);
        $moves = $this->db->single();

        $net = $initial + ($moves->dr - $moves->cr);
        return $net;
    }
    // Helper to get group balances by nature
    public function getAccountGroupsSummary($company_id, $nature, $as_of_date)
    {
        $sql = "
            SELECT 
                g.id, g.name,
                COALESCE(SUM(
                    CASE 
                        WHEN l.opening_balance_type = 'Dr' THEN l.opening_balance 
                        ELSE -l.opening_balance 
                    END
                ), 0) + 
                COALESCE(SUM(ve.debit), 0) - COALESCE(SUM(ve.credit), 0) as net_balance
            FROM account_groups g
            JOIN ledgers l ON l.group_id = g.id
            LEFT JOIN voucher_entries ve ON l.id = ve.ledger_id
            LEFT JOIN vouchers v ON ve.voucher_id = v.id AND v.voucher_date <= :as_of_date
            WHERE g.company_id = :company_id AND g.nature = :nature
            GROUP BY g.id, g.name
            HAVING ABS(net_balance) > 0
        ";

        $this->db->query($sql);
        $this->db->bind(':company_id', $company_id);
        $this->db->bind(':nature', $nature);
        $this->db->bind(':as_of_date', $as_of_date);
        return $this->db->resultSet();
    }

    public function getNetProfit($company_id, $start_date, $end_date)
    {
        // Income (Cr) - Expense (Dr)
        // Net Profit = Total Income (positive magnitude) - Total Expense (positive magnitude)

        $sql = "
            SELECT SUMMARY.nature, SUM(SUMMARY.net_change) as total
            FROM (
                SELECT g.nature, 
                       (COALESCE(SUM(ve.debit), 0) - COALESCE(SUM(ve.credit), 0)) as net_change
                FROM voucher_entries ve
                JOIN vouchers v ON ve.voucher_id = v.id
                JOIN ledgers l ON ve.ledger_id = l.id
                JOIN account_groups g ON l.group_id = g.id
                WHERE v.company_id = :company_id 
                AND v.voucher_date BETWEEN :start_date AND :end_date
                AND g.nature IN ('Income', 'Expenses')
                GROUP BY g.nature
            ) as SUMMARY
            GROUP BY SUMMARY.nature
        ";

        $this->db->query($sql);
        $this->db->bind(':company_id', $company_id);
        $this->db->bind(':start_date', $start_date);
        $this->db->bind(':end_date', $end_date);
        $rows = $this->db->resultSet();

        $income = 0;
        $expense = 0;

        foreach ($rows as $r) {
            if ($r->nature == 'Income')
                $income = abs($r->total); // Income is Cr, so negative sum
            if ($r->nature == 'Expenses')
                $expense = abs($r->total); // Expense is Dr, so positive sum
        }

        return $income - $expense;
    }
    public function getDayBook($company_id, $date)
    {
        $sql = "
            SELECT v.*, u.name as creator_name,
                   GROUP_CONCAT(l.name SEPARATOR ', ') as involved_ledgers
            FROM vouchers v
            JOIN users u ON v.created_by = u.id
            JOIN voucher_entries ve ON v.id = ve.voucher_id
            JOIN ledgers l ON ve.ledger_id = l.id
            WHERE v.company_id = :company_id AND v.voucher_date = :date
            GROUP BY v.id
            ORDER BY v.id DESC
        ";
        $this->db->query($sql);
        $this->db->bind(':company_id', $company_id);
        $this->db->bind(':date', $date);
        return $this->db->resultSet();
    }

    public function getGSTSummary($company_id, $start_date, $end_date)
    {
        $sql = "
            SELECT l.name, g.nature, 
                   SUM(ve.debit) as total_debit, 
                   SUM(ve.credit) as total_credit
            FROM voucher_entries ve
            JOIN vouchers v ON ve.voucher_id = v.id
            JOIN ledgers l ON ve.ledger_id = l.id
            JOIN account_groups g ON l.group_id = g.id
            WHERE v.company_id = :cid 
            AND v.voucher_date BETWEEN :start AND :end
            AND (g.name LIKE '%Tax%' OR g.name LIKE '%Duties%' OR l.name LIKE '%GST%')
            GROUP BY l.id, l.name, g.nature
        ";
        $this->db->query($sql);
        $this->db->bind(':cid', $company_id);
        $this->db->bind(':start', $start_date);
        $this->db->bind(':end', $end_date);
        return $this->db->resultSet();
    }
}
