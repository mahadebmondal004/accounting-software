<?php
class SettingsController extends Controller
{
    private $companyModel;
    private $userModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        if (!isset($_SESSION['user_id']))
            $this->redirect('auth/login');
        if (!isset($_SESSION['company_id']))
            $this->redirect('companies/index');

        $this->companyModel = $this->model('Company');
        $this->userModel = $this->model('User');
    }

    public function index()
    {
        $company = $this->companyModel->getCompanyById($_SESSION['company_id']);

        $data = [
            'nav' => 'settings',
            'company' => $company
        ];
        $this->view('settings/index', $data);
    }

    public function update_profile()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'id' => $_SESSION['company_id'],
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'phone' => trim($_POST['phone']),
                'address' => trim($_POST['address']),
                'gstin' => trim($_POST['gstin']),
                'city' => trim($_POST['city']),
                'state' => trim($_POST['state']),
                'pincode' => trim($_POST['pincode']),
                'country' => trim($_POST['country']),
                'pan_no' => trim($_POST['pan_no']),
                'website' => trim($_POST['website']),
                'currency_symbol' => trim($_POST['currency_symbol'])
            ];

            // Handle Logo Upload
            if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/company_logos/';
                // Create directory if it doesn't exist
                $pubRoot = APP_ROOT . '/../public';
                // Create directory if it doesn't exist
                if (!file_exists(str_replace('/', DIRECTORY_SEPARATOR, $pubRoot . '/' . $uploadDir))) {
                    mkdir(str_replace('/', DIRECTORY_SEPARATOR, $pubRoot . '/' . $uploadDir), 0777, true);
                }

                $fileTmpPath = $_FILES['company_logo']['tmp_name'];
                $fileName = $_FILES['company_logo']['name'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));

                $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');

                if (in_array($fileExtension, $allowedfileExtensions)) {
                    $newFileName = 'logo_' . $_SESSION['company_id'] . '_' . md5(time() . $fileName) . '.' . $fileExtension;

                    if (move_uploaded_file($fileTmpPath, str_replace('/', DIRECTORY_SEPARATOR, $pubRoot . '/' . $uploadDir . $newFileName))) {
                        $data['logo_path'] = $uploadDir . $newFileName;
                        $_SESSION['company_logo'] = $data['logo_path'];
                    }
                }
            }

            if ($this->companyModel->updateCompany($data)) {
                $this->redirect('settings/index');
            } else {
                die('Something went wrong');
            }
        }
    }

    public function users()
    {
        $users = $this->userModel->getUsersByCompanyId($_SESSION['company_id']);
        $roleModel = $this->model('Role');
        $roles = $roleModel->getRoles($_SESSION['company_id']);

        $data = [
            'nav' => 'settings',
            'users' => $users,
            'roles' => $roles
        ];
        $this->view('settings/users', $data);
    }

    public function add_user()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'password' => ($_POST['password']), // Raw here, model might hash but usually controller does. 
                // Wait, User model register expects hashed?
                // Let's check User Model. Register query: '... :password ...'. It does NOT hash inside.
                // So I must hash here.
                'role_id' => $_POST['role_id']
            ];
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

            if ($this->userModel->register($data)) {
                $newUser = $this->userModel->findUserByEmail($data['email']);
                if ($newUser) {
                    $this->companyModel->assignUserToCompany($newUser->id, $_SESSION['company_id'], $data['role_id']);
                    $this->redirect('settings/users');
                }
            } else {
                die("Failed to register user");
            }
        }
    }

    public function backup()
    {
        $dbHost = DB_HOST;
        $dbUser = DB_USER;
        $dbPass = DB_PASS;
        $dbName = DB_NAME;

        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $this->exportDatabase($dbHost, $dbUser, $dbPass, $dbName, $filename);
    }

    private function exportDatabase($host, $user, $pass, $name, $filename)
    {
        $mysqli = new mysqli($host, $user, $pass, $name);
        $mysqli->select_db($name);
        $mysqli->query("SET NAMES 'utf8'");

        $queryTables = $mysqli->query('SHOW TABLES');
        while ($row = $queryTables->fetch_row()) {
            $target_tables[] = $row[0];
        }

        $content = "-- Backup " . date('Y-m-d H:i') . "\n\n";

        foreach ($target_tables as $table) {
            $result = $mysqli->query('SELECT * FROM ' . $table);
            $fields_amount = $result->field_count;
            $rows_num = $mysqli->affected_rows;

            $res = $mysqli->query('SHOW CREATE TABLE ' . $table);
            $TableMLine = $res->fetch_row();
            $content .= "\n\n" . $TableMLine[1] . ";\n\n";

            for ($i = 0, $st_counter = 0; $i < $fields_amount; $i++, $st_counter = 0) {
                while ($row = $result->fetch_row()) {
                    if ($st_counter % 100 == 0 || $st_counter == 0) {
                        $content .= "\nINSERT INTO " . $table . " VALUES";
                    }
                    $content .= "\n(";
                    for ($j = 0; $j < $fields_amount; $j++) {
                        $value = isset($row[$j]) ? $row[$j] : null;
                        if (!is_null($value)) {
                            // Fix deprecated null passing
                            $escaped_value = str_replace("\n", "\\n", addslashes((string) $value));
                            $content .= '"' . $escaped_value . '"';
                        } else {
                            $content .= 'NULL'; // Better SQL handling for null
                        }
                        if ($j < ($fields_amount - 1)) {
                            $content .= ',';
                        }
                    }
                    $content .= ")";
                    if ((($st_counter + 1) % 100 == 0 && $st_counter != 0) || $st_counter + 1 == $rows_num) {
                        $content .= ";";
                    } else {
                        $content .= ",";
                    }
                    $st_counter = $st_counter + 1;
                }
            }
            $content .= "\n\n\n";
        }

        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . $filename . "\"");
        echo $content;
        exit;
    }
}
