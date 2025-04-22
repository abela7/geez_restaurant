<?php
/**
 * User Class
 * 
 * Handles user authentication and management
 */
class User {
    private $db;
    private $user_id;
    private $username;
    private $full_name;
    private $initials;
    private $role;
    private $is_active;
    
    /**
     * Constructor
     * 
     * @param Database $db Database instance
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Authenticate a user
     * 
     * @param string $username Username
     * @param string $password Plain text password
     * @return bool True if authentication successful, false otherwise
     */
    public function authenticate($username, $password) {
        $sql = "SELECT * FROM users WHERE username = ? AND is_active = 1";
        $user = $this->db->fetchRow($sql, [$username]);
        
        if ($user && password_verify($password, $user['password'])) {
            $this->user_id = $user['user_id'];
            $this->username = $user['username'];
            $this->full_name = $user['full_name'];
            $this->initials = $user['initials'];
            $this->role = $user['role'];
            $this->is_active = $user['is_active'];
            
            // Set session variables
            $_SESSION['user_id'] = $this->user_id;
            $_SESSION['username'] = $this->username;
            $_SESSION['full_name'] = $this->full_name;
            $_SESSION['initials'] = $this->initials;
            $_SESSION['role'] = $this->role;
            
            // Update last login timestamp
            $this->db->update('users', 
                ['last_login' => date('Y-m-d H:i:s')], 
                'user_id = ?', 
                [$this->user_id]
            );
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if user is logged in
     * 
     * @return bool True if user is logged in, false otherwise
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Check if user has a specific role
     * 
     * @param string|array $roles Role(s) to check
     * @return bool True if user has the role, false otherwise
     */
    public function hasRole($roles) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        return in_array($_SESSION['role'], $roles);
    }
    
    /**
     * Get user by ID
     * 
     * @param int $user_id User ID
     * @return array|false User data or false if not found
     */
    public function getById($user_id) {
        $sql = "SELECT * FROM users WHERE user_id = ?";
        return $this->db->fetchRow($sql, [$user_id]);
    }
    
    /**
     * Get user by username
     * 
     * @param string $username Username to search for
     * @return array|false User data or false if not found
     */
    public function getUserByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = ?";
        return $this->db->fetchRow($sql, [$username]);
    }
    
    /**
     * Get all users
     * 
     * @return array All users
     */
    public function getAll() {
        $sql = "SELECT user_id, username, full_name, role, is_active, last_login, created_at FROM users ORDER BY full_name";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get users by specific roles
     * 
     * @param array $roles Array of roles to fetch
     * @param bool $only_active Fetch only active users (default: true)
     * @return array Users matching the roles
     */
    public function getByRoles($roles, $only_active = true) {
        if (empty($roles) || !is_array($roles)) {
            return [];
        }
        
        // Create placeholders for roles
        $placeholders = implode(', ', array_fill(0, count($roles), '?'));
        $params = $roles;
        
        $sql = "SELECT user_id, username, full_name, role, is_active 
                FROM users 
                WHERE role IN ({$placeholders})";
                
        if ($only_active) {
            $sql .= " AND is_active = 1";
        }
        
        $sql .= " ORDER BY full_name";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Create a new user
     * 
     * @param array $data User data
     * @return int|false New user ID or false on failure
     */
    public function create($data) {
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Set default last_login to NULL
        $data['last_login'] = null;
        
        // Set created_at timestamp
        $data['created_at'] = date('Y-m-d H:i:s');
        
        return $this->db->insert('users', $data);
    }
    
    /**
     * Update a user
     * 
     * @param int $user_id User ID
     * @param array $data User data
     * @return int Number of affected rows
     */
    public function update($user_id, $data) {
        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        return $this->db->update('users', $data, 'user_id = ?', [$user_id]);
    }
    
    /**
     * Delete a user
     * 
     * @param int $user_id User ID
     * @return int Number of affected rows
     */
    public function delete($user_id) {
        return $this->db->delete('users', 'user_id = ?', [$user_id]);
    }
    
    /**
     * Logout the current user
     */
    public function logout() {
        // Unset all session variables
        $_SESSION = [];
        
        // Destroy the session
        session_destroy();
    }
    
    /**
     * Get current user ID
     * 
     * @return int|null Current user ID or null if not logged in
     */
    public function getCurrentUserId() {
        return $this->isLoggedIn() ? $_SESSION['user_id'] : null;
    }
    
    /**
     * Get current user role
     * 
     * @return string|null Current user role or null if not logged in
     */
    public function getCurrentUserRole() {
        return $this->isLoggedIn() ? $_SESSION['role'] : null;
    }
    
    /**
     * Get current user full name
     * 
     * @return string|null Current user full name or null if not logged in
     */
    public function getCurrentUserFullName() {
        return $this->isLoggedIn() ? $_SESSION['full_name'] : null;
    }
}
