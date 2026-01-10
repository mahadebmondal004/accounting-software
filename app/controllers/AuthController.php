<?php
class AuthController extends Controller
{
    private $userModel;
    private $roleModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
        $this->roleModel = $this->model('Role');
    }

    public function login()
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();

        if (isset($_SESSION['user_id'])) {
            $this->redirect('dashboard/index');
        }


        // Check for POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            verify_csrf();
            // Process form
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'email_err' => '',
                'password_err' => '',
            ];

            // Validate Email
            if (empty($data['email'])) {
                $data['email_err'] = 'Please enter email';
            }

            // Validate Password
            if (empty($data['password'])) {
                $data['password_err'] = 'Please enter password';
            }

            // Check for user/email
            if ($this->userModel->findUserByEmail($data['email'])) {
                // User found
            } else {
                $data['email_err'] = 'No user found';
            }

            // Make sure errors are empty
            if (empty($data['email_err']) && empty($data['password_err'])) {
                // Validated
                // Check and set logged in user
                $loggedInUser = $this->userModel->login($data['email'], $data['password']);

                if ($loggedInUser) {
                    // Create Session
                    $this->createUserSession($loggedInUser);
                } else {
                    $data['password_err'] = 'Password incorrect';
                    $this->view('auth/login', $data);
                }
            } else {
                // Load view with errors
                $this->view('auth/login', $data);
            }

        } else {
            // Init data
            $data = [
                'email' => '',
                'password' => '',
                'email_err' => '',
                'password_err' => ''
            ];

            // Load view
            $this->view('auth/login', $data);
        }
    }

    public function createUserSession($user)
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_name'] = $user->name;
        $_SESSION['user_profile_pic'] = $user->profile_pic;
        $_SESSION['is_super_admin'] = $user->is_super_admin ?? 0;

        // Session timeout tracking (30 minutes)
        $_SESSION['last_activity'] = time();
        $_SESSION['login_time'] = time();
        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];

        // System Level Permissions
        $_SESSION['permissions'] = [];
        if (isset($user->is_super_admin) && $user->is_super_admin == 1) {
            $_SESSION['permissions']['all'] = true;
        }

        $this->redirect('dashboard/index');
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        unset($_SESSION['user_id']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_name']);
        unset($_SESSION['role_id']);
        session_destroy();
        $this->redirect('auth/login');
    }
}
