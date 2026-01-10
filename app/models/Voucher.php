<?php
class Voucher
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function getVouchersByCompanyId($company_id)
    {
        $this->db->query('SELECT v.*, u.name as created_by_name 
                          FROM vouchers v
                          JOIN users u ON v.created_by = u.id
                          WHERE v.company_id = :company_id
                          ORDER BY v.voucher_date DESC, v.id DESC');
        $this->db->bind(':company_id', $company_id);
        return $this->db->resultSet();
    }

    public function createVoucher($data, $entries)
    {
        // Start Transaction
        $this->db->query('START TRANSACTION');
        $this->db->execute();

        try {
            // 1. Insert Voucher Head
            $this->db->query('INSERT INTO vouchers (company_id, financial_year_id, voucher_type, voucher_number, voucher_date, narration, total_amount, created_by) 
                              VALUES(:company_id, :financial_year_id, :voucher_type, :voucher_number, :voucher_date, :narration, :total_amount, :created_by)');

            $this->db->bind(':company_id', $data['company_id']);
            $this->db->bind(':financial_year_id', $data['financial_year_id']); // Need to fetch active FY
            $this->db->bind(':voucher_type', $data['voucher_type']);
            $this->db->bind(':voucher_number', $data['voucher_number']);
            $this->db->bind(':voucher_date', $data['voucher_date']);
            $this->db->bind(':narration', $data['narration']);
            $this->db->bind(':total_amount', $data['total_amount']);
            $this->db->bind(':created_by', $data['created_by']);

            if (!$this->db->execute()) {
                throw new Exception("Failed to insert voucher head.");
            }

            $voucher_id = $this->db->lastInsertId();

            // 2. Insert Entries
            foreach ($entries as $entry) {
                $this->db->query('INSERT INTO voucher_entries (voucher_id, ledger_id, debit, credit, description) 
                                  VALUES(:voucher_id, :ledger_id, :debit, :credit, :description)');
                $this->db->bind(':voucher_id', $voucher_id);
                $this->db->bind(':ledger_id', $entry['ledger_id']);
                $this->db->bind(':debit', $entry['debit']);
                $this->db->bind(':credit', $entry['credit']);
                $this->db->bind(':description', $entry['description'] ?? '');

                if (!$this->db->execute()) {
                    throw new Exception("Failed to insert voucher entry.");
                }

                // 3. Update Ledger Current Balance (Naive approach, ideally trigger or calculated on fly)
                // For performance, we update the cached balance.
                // If Debit entry, Add to balance (Assuming Asset/Exp normal Dr). 
                // Wait, accounting equation is complex.
                // Simple View: Dr increases Dr balance, Cr decreases Dr balance.
                // If ledger is Cr nature (Liability), Dr decreases it.
                // Easier: Just store balance as net Dr - Cr.

                $this->updateLedgerBalance($entry['ledger_id'], $entry['debit'], $entry['credit']);
            }

            // Commit
            $this->db->query('COMMIT');
            $this->db->execute();
            return $voucher_id;

        } catch (Exception $e) {
            $this->db->query('ROLLBACK');
            $this->db->execute();
            // Log error
            error_log("Voucher Create Error: " . $e->getMessage());
            return false;
        }
    }

    // Helper to update ledger balance
    private function updateLedgerBalance($ledger_id, $dr, $cr)
    {
        // We get current balance
        $this->db->query('SELECT current_balance, opening_balance_type FROM ledgers WHERE id = :id');
        $this->db->bind(':id', $ledger_id);
        $ledger = $this->db->single();

        // This is a simplified "running balance". 
        // Real systems sum up all voucher_entries.
        // Let's do the sum up approach for accuracy if volume is low, or delta update.
        // Delta update:
        // New Balance = Old + Dr - Cr (If we treat Balance as Dr positive)

        $net_change = $dr - $cr;

        $this->db->query('UPDATE ledgers SET current_balance = current_balance + :change WHERE id = :id');
        $this->db->bind(':change', $net_change);
        $this->db->bind(':id', $ledger_id);
        $this->db->execute();
    }

    public function generateVoucherNumber($company_id, $type)
    {
        // Format: TYPE-YYYY-SEQ e.g., PAY-2024-0001
        // Get last voucher of this type
        $this->db->query('SELECT voucher_number FROM vouchers WHERE company_id = :cid AND voucher_type = :type ORDER BY id DESC LIMIT 1');
        $this->db->bind(':cid', $company_id);
        $this->db->bind(':type', $type);
        $row = $this->db->single();

        $prefix = strtoupper(substr($type, 0, 3)); // PAY, REC, JOU
        $year = date('Y');

        if ($row) {
            // Extract number
            $parts = explode('-', $row->voucher_number);
            if (count($parts) >= 3) {
                $seq = intval($parts[2]) + 1;
                return sprintf("%s-%s-%04d", $prefix, $year, $seq);
            }
        }
        return sprintf("%s-%s-0001", $prefix, $year);
    }

    public function getActiveFinancialYear($company_id)
    {
        $this->db->query('SELECT * FROM financial_years WHERE company_id = :id AND is_active = 1 LIMIT 1');
        $this->db->bind(':id', $company_id);
        $fy = $this->db->single();

        if (!$fy) {
            // Create default if not exists
            // This logic belongs in Company creation, but fail-safe here
            // Return false, controller handles
            return false;
        }
        return $fy;
    }

    public function getVoucherById($id)
    {
        $this->db->query('SELECT v.*, u.name as created_by_name 
                          FROM vouchers v
                          JOIN users u ON v.created_by = u.id
                          WHERE v.id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getVoucherEntries($voucher_id)
    {
        $this->db->query('SELECT ve.*, l.name as ledger_name, l.code as ledger_code
                          FROM voucher_entries ve
                          JOIN ledgers l ON ve.ledger_id = l.id
                          WHERE ve.voucher_id = :vid
                          ORDER BY ve.id ASC');
        $this->db->bind(':vid', $voucher_id);
        return $this->db->resultSet();
    }

    // Auto Create FY if needed (Quick Fix Helper)
    public function checkAndCreateFY($company_id)
    {
        $fy = $this->getActiveFinancialYear($company_id);
        if ($fy)
            return $fy->id;

        // Create Default FY current year
        $start = date('Y-04-01');
        if (date('m') < 4)
            $start = date('Y-04-01', strtotime('-1 year'));
        $end = date('Y-03-31', strtotime($start . ' + 1 year'));
        $name = date('Y', strtotime($start)) . '-' . date('Y', strtotime($end));

        $this->db->query('INSERT INTO financial_years (company_id, name, start_date, end_date, is_active) VALUES (:cid, :name, :start, :end, 1)');
        $this->db->bind(':cid', $company_id);
        $this->db->bind(':name', $name);
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);

        if ($this->db->execute())
            return $this->db->lastInsertId();
        return 0;
    }
}
