<?php
class GuideController extends Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        if (!isset($_SESSION['user_id']))
            $this->redirect('auth/login');
    }

    public function index()
    {
        $isSuperAdmin = isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'] == 1;
        $hasCompany = isset($_SESSION['company_id']);

        $data = [
            'nav' => 'guide',
            'is_super_admin' => $isSuperAdmin,
            'has_company' => $hasCompany,
            'user_name' => $_SESSION['user_name'] ?? 'User',
            'company_name' => $_SESSION['company_name'] ?? 'No Company Selected'
        ];

        $this->view('guide/index', $data);
    }
}
?>