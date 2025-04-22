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
    private $error_message = null; // Added for detailed login errors
    
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
        // First check if user exists at all, regardless of active status
        $sql = "SELECT * FROM users WHERE username = ?";
        $user = $this->db->fetchRow($sql, [$username]);
        
        if (!$user) {
            error_log("[DEBUG] Authentication failed: User not found: " . $username);
            $this->error_message = "[Debug] User not found.";
            return false;
        }
        
        // Now check if user is active
        if ($user['is_active'] != 1) {
            error_log("[DEBUG] Authentication failed: User is inactive: " . $username);
            $this->error_message = "[Debug] User account is inactive. Please contact an administrator.";
            return false;
        }
        
        // Ensure password is handled consistently with how it was stored
        $trimmed_password = trim($password);
        $trimmed_hash = trim($user['password']); 
        
        // Explicit debug for comparing
        error_log("[DEBUG] Password verification attempt: Length of password=" . strlen($trimmed_password) . 
                  ", Length of hash=" . strlen($trimmed_hash) . 
                  ", Hash prefix=" . substr($trimmed_hash, 0, 7));
        
        // First try standard verification
        $result = password_verify($trimmed_password, $trimmed_hash);
        
        if ($result) {
            // Standard verification worked
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
            
            $this->error_message = null; // Clear error on success
            return true;
        } else {
            // Check if hash needs rehashing (older algorithm or different cost)
            if (substr($trimmed_hash, 0, 4) === '$2y$') {
                error_log("[DEBUG] Authentication failed: Using correct bcrypt format but password doesn't match. Username: " . $username);
                $this->error_message = "[Debug] Incorrect password.";
            } else {
                error_log("[DEBUG] Authentication failed: Hash doesn't appear to be in correct bcrypt format: " . substr($trimmed_hash, 0, 10) . "...");
                $this->error_message = "[Debug] Password hash format appears invalid.";
            }
            return false;
        }
        
        // This part should theoretically not be reached, but added for completeness
        error_log("[DEBUG] Authentication failed: Unknown reason for username: " . $username);
        $this->error_message = "[Debug] An unexpected error occurred during login.";
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
        // Hash password with error handling
        if (!isset($data['password']) || empty($data['password'])) {
            error_log("[DEBUG] Create user failed: Password is empty or not provided");
            return false;
        }
        
        // Log what we're about to hash for debugging
        error_log("[DEBUG] User::create - Hashing password. Raw password length: " . strlen($data['password']));
        
        // Hash the password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Verify the hash was created correctly
        if (!$data['password'] || substr($data['password'], 0, 4) !== '$2y$') {
            error_log("[DEBUG] Create user failed: Password hashing failed or returned invalid format");
            return false;
        }
        
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
            // --- Add thorough logging ---
            error_log("[DEBUG] User::update - About to hash password for user_id: {$user_id}. Raw password length: " . strlen($data['password']));
            
            // Hash the password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Verify hash was created properly
            if (!$data['password'] || substr($data['password'], 0, 4) !== '$2y$') {
                error_log("[DEBUG] Update user failed: Password hashing failed or returned invalid format");
                return false;
            }
            
            // Log the generated hash prefix
            error_log("[DEBUG] User::update - Password hashed successfully. Hash prefix: " . substr($data['password'], 0, 12) . "...");
        } else {
            // If password is not provided or empty in the form, remove it from $data
            unset($data['password']); 
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
    
    /**
     * Get the last authentication error message
     *
     * @return string|null Error message or null if no error
     */
    public function getErrorMessage() {
        return $this->error_message;
    }
    
    /**
     * Toggle user active status
     * 
     * @param int $user_id User ID
     * @param bool $is_active New active status
     * @return int Number of affected rows
     */
    public function toggleActive($user_id, $is_active) {
        $data = ['is_active' => $is_active ? 1 : 0];
        return $this->db->update('users', $data, 'user_id = ?', [$user_id]);
    }
}
