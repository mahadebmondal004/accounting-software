<?php
class Item
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function getItemsByCompanyId($company_id)
    {
        $this->db->query('SELECT * FROM items WHERE company_id = :company_id ORDER BY name ASC');
        $this->db->bind(':company_id', $company_id);
        return $this->db->resultSet();
    }

    public function addItem($data)
    {
        $this->db->query('INSERT INTO items (company_id, name, type, sku, hsn_sac, unit, sale_price, purchase_price, tax_rate, description, opening_stock, current_stock) 
                          VALUES(:company_id, :name, :type, :sku, :hsn_sac, :unit, :sale_price, :purchase_price, :tax_rate, :description, :opening_stock, :current_stock)');

        $this->db->bind(':company_id', $data['company_id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':sku', $data['sku']);
        $this->db->bind(':hsn_sac', $data['hsn_sac']);
        $this->db->bind(':unit', $data['unit']);
        $this->db->bind(':sale_price', $data['sale_price']);
        $this->db->bind(':purchase_price', $data['purchase_price']);
        $this->db->bind(':tax_rate', $data['tax_rate']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':opening_stock', $data['opening_stock']);
        $this->db->bind(':current_stock', $data['opening_stock']);

        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function getItemById($id)
    {
        $this->db->query('SELECT * FROM items WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function updateItem($data)
    {
        $this->db->query('UPDATE items SET name = :name, type = :type, sku = :sku, hsn_sac = :hsn_sac, unit = :unit, 
                          sale_price = :sale_price, purchase_price = :purchase_price, tax_rate = :tax_rate, 
                          description = :description WHERE id = :id');

        $this->db->bind(':id', $data['id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':sku', $data['sku']);
        $this->db->bind(':hsn_sac', $data['hsn_sac']);
        $this->db->bind(':unit', $data['unit']);
        $this->db->bind(':sale_price', $data['sale_price']);
        $this->db->bind(':purchase_price', $data['purchase_price']);
        $this->db->bind(':tax_rate', $data['tax_rate']);
        $this->db->bind(':description', $data['description']);

        return $this->db->execute();
    }

    public function deleteItem($id)
    {
        $this->db->query('DELETE FROM items WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function updateStock($id, $qty, $direction = 'out')
    {
        if ($direction == 'out') {
            $sql = 'UPDATE items SET current_stock = current_stock - :qty WHERE id = :id';
        } else {
            $sql = 'UPDATE items SET current_stock = current_stock + :qty WHERE id = :id';
        }
        $this->db->query($sql);
        $this->db->bind(':qty', $qty);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
