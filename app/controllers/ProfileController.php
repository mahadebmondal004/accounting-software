<?php
class ProfileController extends Controller
{
    private $userModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth/login');
        }

        $this->userModel = $this->model('User');
    }

    public function index()
    {
        $user = $this->userModel->findUserById($_SESSION['user_id']);

        $data = [
            'user' => $user,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'name_err' => '',
            'password_err' => '',
            'confirm_password_err' => ''
        ];

        $this->view('profile/index', $data);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'id' => $_SESSION['user_id'],
                'name' => trim($_POST['name']),
                'phone' => trim($_POST['phone']),
                'password' => trim($_POST['password']),
                'confirm_password' => trim($_POST['confirm_password']),
                'name_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ];

            // Validate Name
            if (empty($data['name'])) {
                $data['name_err'] = 'Please enter name';
            }

            // Validate Password if entered
            if (!empty($data['password'])) {
                if (strlen($data['password']) < 6) {
                    $data['password_err'] = 'Password must be at least 6 characters';
                }
                if ($data['password'] != $data['confirm_password']) {
                    $data['confirm_password_err'] = 'Passwords do not match';
                }
            }

            // Handle Profile Picture Upload
            if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['profile_pic']['name'];
                $fileType = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $fileSize = $_FILES['profile_pic']['size'];

                if (in_array($fileType, $allowed)) {
                    if ($fileSize < 5000000) { // 5MB
                        // Use absolute path relative to project root
                        // APP_ROOT is .../app, so dirname(APP_ROOT) is .../Accounting Software
                        $publicDir = dirname(APP_ROOT) . '/public';
                        $uploadDir = $publicDir . '/uploads/profile_pics/';

                        // Debugging: Ensure directory exists
                        if (!file_exists($uploadDir)) {
                            // Recursive creation
                            mkdir($uploadDir, 0777, true);
                        }

                        $newFilename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $fileType;
                        $uploadPath = $uploadDir . $newFilename;

                        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadPath)) {
                            $data['profile_pic'] = 'uploads/profile_pics/' . $newFilename;
                        } else {
                            // Optional: handle move error
                            // die("Failed to move file to: " . $uploadPath);
                        }
                    }
                }
            }

            if (empty($data['name_err']) && empty($data['password_err']) && empty($data['confirm_password_err'])) {
                // Prepare data for update
                $updateData = [
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'password' => !empty($data['password']) ? password_hash($data['password'], PASSWORD_DEFAULT) : '',
                    'profile_pic' => isset($data['profile_pic']) ? $data['profile_pic'] : ''
                ];

                if ($this->userModel->updateUser($updateData)) {
                    // Update session name if changed
                    $_SESSION['user_name'] = $data['name'];
                    if (isset($data['profile_pic'])) {
                        $_SESSION['user_profile_pic'] = $data['profile_pic'];
                    }

                    // Redirect with success (could add flash message logic later)
                    $this->redirect('profile/index');
                } else {
                    die('Something went wrong');
                }
            } else {
                // Report errors
                // Note: user object is needed for view
                $user = $this->userModel->findUserById($_SESSION['user_id']);
                $data['user'] = $user;
                $data['email'] = $user->email; // Email is readonly/persistent

                $this->view('profile/index', $data);
            }

        } else {
            $this->redirect('profile/index');
        }
    }
}
