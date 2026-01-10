<?php
class AccountGroupsController extends Controller
{
    private $groupModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        if (!isset($_SESSION['user_id']))
            $this->redirect('auth/login');
        if (!isset($_SESSION['company_id']))
            $this->redirect('companies/index');

        $this->groupModel = $this->model('AccountGroup');
    }

    public function index()
    {
        $groups = $this->groupModel->getGroupsByCompanyId($_SESSION['company_id']);

        $data = [
            'nav' => 'account_groups',
            'groups' => $groups
        ];
        $this->view('account_groups/index', $data);
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            verify_csrf();

            $data = [
                'company_id' => $_SESSION['company_id'],
                'parent_id' => !empty($_POST['parent_id']) ? $_POST['parent_id'] : null,
                'name' => trim($_POST['name']),
                'code' => trim($_POST['code'] ?? ''),
                'nature' => $_POST['nature'],
                'description' => trim($_POST['description'] ?? '')
            ];

            // Validation
            if (empty($data['name'])) {
                die('Error: Group name is required');
            }
            if (empty($data['nature'])) {
                die('Error: Nature is required');
            }

            try {
                if ($this->groupModel->createGroup($data)) {
                    $this->redirect('account_groups/index');
                } else {
                    die('Failed to create group - Database error');
                }
            } catch (Exception $e) {
                die('Error: ' . $e->getMessage());
            }
        } else {
            $parentGroups = $this->groupModel->getGroupsForDropdown($_SESSION['company_id']);

            $data = [
                'nav' => 'account_groups',
                'parent_groups' => $parentGroups
            ];
            $this->view('account_groups/create', $data);
        }
    }

    public function edit($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            verify_csrf();

            $data = [
                'id' => $id,
                'parent_id' => $_POST['parent_id'] ?? null,
                'name' => trim($_POST['name']),
                'code' => trim($_POST['code']),
                'nature' => $_POST['nature'],
                'description' => trim($_POST['description'])
            ];

            if ($this->groupModel->updateGroup($data)) {
                $this->redirect('account_groups/index');
            } else {
                die('Failed to update group');
            }
        } else {
            $group = $this->groupModel->getGroupById($id);
            if (!$group || $group->company_id != $_SESSION['company_id']) {
                $this->redirect('account_groups/index');
                return;
            }

            $parentGroups = $this->groupModel->getGroupsForDropdown($_SESSION['company_id']);

            $data = [
                'nav' => 'account_groups',
                'group' => $group,
                'parent_groups' => $parentGroups
            ];
            $this->view('account_groups/edit', $data);
        }
    }

    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            verify_csrf();

            $group = $this->groupModel->getGroupById($id);
            if (!$group || $group->company_id != $_SESSION['company_id']) {
                $this->redirect('account_groups/index');
                return;
            }

            if ($this->groupModel->deleteGroup($id)) {
                $this->redirect('account_groups/index');
            } else {
                die('Cannot delete group: It has sub-groups or ledgers');
            }
        }
    }
}
?>