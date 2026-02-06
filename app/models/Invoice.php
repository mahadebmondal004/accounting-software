<?php
class Invoice
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function createInvoice($data, $items)
    {
        // Invoice creation is wrapped similarly to Voucher but extends it
        // We assume the Controller has already created the VOUCHER via VoucherModel and obtained voucher_id
        // This model handles the Invoice specific tables: invoice_details, invoice_items

        $this->db->query('INSERT INTO invoice_details 
            (voucher_id, due_date, terms_conditions, po_number, shipping_address, transport_mode, vehicle_number, eway_bill_no) 
            VALUES (:vid, :due, :terms, :po, :ship, :mode, :veh, :eway)');

        $this->db->bind(':vid', $data['voucher_id']);
        $this->db->bind(':due', $data['due_date']);
        $this->db->bind(':terms', $data['terms_conditions']);
        $this->db->bind(':po', $data['po_number']);
        $this->db->bind(':ship', $data['shipping_address']);
        $this->db->bind(':mode', $data['transport_mode']);
        $this->db->bind(':veh', $data['vehicle_number']);
        $this->db->bind(':eway', $data['eway_bill_no']);

        $this->db->execute();

        foreach ($items as $item) {
            $this->db->query('INSERT INTO invoice_items 
                (voucher_id, item_id, item_name, quantity, rate, amount, tax_percent, tax_amount, cgst_amount, sgst_amount, igst_amount, total)
                VALUES (:vid, :iid, :name, :qty, :rate, :amt, :tax_p, :tax_a, :cgst, :sgst, :igst, :total)');

            $this->db->bind(':vid', $data['voucher_id']);
            // Handle item_id - if not provided or 0, set to NULL
            $item_id = isset($item['item_id']) && $item['item_id'] > 0 ? $item['item_id'] : null;
            $this->db->bind(':iid', $item_id);
            $this->db->bind(':name', $item['name']);
            $this->db->bind(':qty', $item['quantity']);
            $this->db->bind(':rate', $item['rate']);
            $this->db->bind(':amt', $item['amount']); // Basic
            $this->db->bind(':tax_p', $item['tax_percent']);
            $this->db->bind(':tax_a', $item['tax_amount']);
            $this->db->bind(':cgst', $item['cgst_amount'] ?? 0);
            $this->db->bind(':sgst', $item['sgst_amount'] ?? 0);
            $this->db->bind(':igst', $item['igst_amount'] ?? 0);
            $this->db->bind(':total', $item['total']);

            $this->db->execute();
        }

        return true;
    }

    public function getInvoiceDetails($voucher_id)
    {
        $this->db->query("SELECT * FROM invoice_details WHERE voucher_id = :vid");
        $this->db->bind(':vid', $voucher_id);
        return $this->db->single();
    }

    public function getInvoiceItems($voucher_id)
    {
        $this->db->query("SELECT * FROM invoice_items WHERE voucher_id = :vid");
        $this->db->bind(':vid', $voucher_id);
        return $this->db->resultSet();
    }
}
