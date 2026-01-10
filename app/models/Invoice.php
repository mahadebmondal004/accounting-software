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
                (voucher_id, item_id, item_name, quantity, rate, amount, tax_percent, tax_amount, total)
                VALUES (:vid, :iid, :name, :qty, :rate, :amt, :tax_p, :tax_a, :total)');

            $this->db->bind(':vid', $data['voucher_id']);
            $this->db->bind(':iid', $item['item_id']); // Can be null
            $this->db->bind(':name', $item['name']);
            $this->db->bind(':qty', $item['quantity']);
            $this->db->bind(':rate', $item['rate']);
            $this->db->bind(':amt', $item['amount']); // Basic
            $this->db->bind(':tax_p', $item['tax_percent']);
            $this->db->bind(':tax_a', $item['tax_amount']);
            $this->db->bind(':total', $item['total']);

            $this->db->execute();
        }

        return true;
    }
}
