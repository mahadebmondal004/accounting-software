<?php
class User
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    // Find user by email
    public function findUserByEmail($email)
    {
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);

        $row = $this->db->single();

        // Check row
        if ($this->db->rowCount() > 0) {
            return $row;
        } else {
            return false;
        }
    }

    // Login User
    public function login($email, $password)
    {
        $row = $this->findUserByEmail($email);

        if ($row == false)
            return false;

        $hashed_password = $row->password;
        if (password_verify($password, $hashed_password)) {
            return $row;
        } else {
            return false;
        }
    }

    // Register User (Useful for initial setup or admin creating users)
    public function register($data)
    {
        $this->db->query('INSERT INTO users (name, email, password, role_id) VALUES(:name, :email, :password, :role_id)');
        // Bind values
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', $data['password']);
        $this->db->bind(':role_id', $data['role_id']);

        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
    // Find user by ID
    public function findUserById($id)
    {
        $this->db->query('SELECT * FROM users WHERE id = :id');
        $this->db->bind(':id', $id);

        $row = $this->db->single();
        return $row;
    }

    // Get all users associated with a company
    public function getUsersByCompanyId($company_id)
    {
        $this->db->query('SELECT u.*, r.name as role_name 
                          FROM users u 
                          JOIN user_companies uc ON u.id = uc.user_id 
                          JOIN roles r ON uc.role_id = r.id
                          WHERE uc.company_id = :company_id');
        $this->db->bind(':company_id', $company_id);
        return $this->db->resultSet();
    }
    // Update User Profile
    public function updateUser($data)
    {
        // Check if password needs update
        // Check if password needs update
        $query = 'UPDATE users SET name = :name, phone = :phone';

        if (!empty($data['password'])) {
            $query .= ', password = :password';
        }
        if (!empty($data['profile_pic'])) {
            $query .= ', profile_pic = :profile_pic';
        }

        $query .= ' WHERE id = :id';

        $this->db->query($query);

        if (!empty($data['password'])) {
            $this->db->bind(':password', $data['password']);
        }
        if (!empty($data['profile_pic'])) {
            $this->db->bind(':profile_pic', $data['profile_pic']);
        }

        $this->db->bind(':name', $data['name']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':id', $data['id']);

        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
}
