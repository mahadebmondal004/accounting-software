<?php
class ItemsController extends Controller
{
    private $itemModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        if (!isset($_SESSION['user_id']))
            $this->redirect('auth/login');
        if (!isset($_SESSION['company_id']))
            $this->redirect('companies/index');

        $this->itemModel = $this->model('Item');
    }

    public function index()
    {
        $items = $this->itemModel->getItemsByCompanyId($_SESSION['company_id']);
        $data = [
            'items' => $items,
            'nav' => 'items'
        ];
        $this->view('items/index', $data);
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            verify_csrf();
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'company_id' => $_SESSION['company_id'],
                'name' => trim($_POST['name']),
                'type' => trim($_POST['type']),
                'sku' => trim($_POST['sku']),
                'hsn_sac' => trim($_POST['hsn_sac']),
                'unit' => trim($_POST['unit']),
                'sale_price' => trim($_POST['sale_price']),
                'purchase_price' => trim($_POST['purchase_price']),
                'tax_rate' => trim($_POST['tax_rate']),
                'description' => trim($_POST['description']),
                'opening_stock' => trim($_POST['opening_stock']),
            ];

            if ($this->itemModel->addItem($data)) {
                $this->redirect('items/index');
            } else {
                die('Something went wrong');
            }
        } else {
            $data = [
                'nav' => 'items',
                'name' => '',
                'type' => 'Product',
                'sku' => '',
                'hsn_sac' => '',
                'unit' => 'Pcs',
                'sale_price' => '0.00',
                'purchase_price' => '0.00',
                'tax_rate' => '18.00',
                'description' => '',
                'opening_stock' => '0'
            ];
            $this->view('items/create', $data);
        }
    }

    public function show($id)
    {
        $item = $this->itemModel->getItemById($id);

        if (!$item) {
            $this->redirect('items/index');
            return;
        }

        $data = [
            'item' => $item,
            'nav' => 'items'
        ];
        $this->view('items/view', $data);
    }

    public function edit($id)
    {
        $item = $this->itemModel->getItemById($id);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            verify_csrf();
            $data = [
                'id' => $id,
                'name' => trim($_POST['name']),
                'type' => trim($_POST['type']),
                'sku' => trim($_POST['sku']),
                'hsn_sac' => trim($_POST['hsn_sac']),
                'unit' => trim($_POST['unit']),
                'sale_price' => trim($_POST['sale_price']),
                'purchase_price' => trim($_POST['purchase_price']),
                'tax_rate' => trim($_POST['tax_rate']),
                'description' => trim($_POST['description']),
            ];

            if ($this->itemModel->updateItem($data)) {
                $this->redirect('items/index');
            } else {
                die('Something went wrong');
            }
        } else {
            $data = [
                'nav' => 'items',
                'id' => $id,
                'name' => $item->name,
                'type' => $item->type,
                'sku' => $item->sku,
                'hsn_sac' => $item->hsn_sac,
                'unit' => $item->unit,
                'sale_price' => $item->sale_price,
                'purchase_price' => $item->purchase_price,
                'tax_rate' => $item->tax_rate,
                'description' => $item->description
            ];
            $this->view('items/edit', $data);
        }
    }

    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            verify_csrf();
            if ($this->itemModel->deleteItem($id)) {
                $this->redirect('items/index');
            } else {
                die('Something went wrong');
            }
        }
    }

    public function export($format = 'csv')
    {
        $items = $this->itemModel->getItemsByCompanyId($_SESSION['company_id']);

        if ($format == 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="items_export.csv"');

            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Name', 'Type', 'SKU', 'HSN/SAC', 'Unit', 'Sale Price', 'Purchase Price', 'Tax Rate']);

            foreach ($items as $item) {
                fputcsv($output, [
                    $item->id,
                    $item->name,
                    $item->type,
                    $item->sku,
                    $item->hsn_sac,
                    $item->unit,
                    $item->sale_price,
                    $item->purchase_price,
                    $item->tax_rate
                ]);
            }
            fclose($output);
            exit;
        }
    }
}
