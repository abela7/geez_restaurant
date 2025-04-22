<?php
/**
 * Helper Functions
 * 
 * Common utility functions for the Geez Restaurant application
 */

/**
 * Redirect to a URL
 * 
 * @param string $url URL to redirect to
 */
function redirect($url) {
    // Clean all output buffers to prevent "headers already sent" errors
    while (ob_get_level()) {
        ob_end_clean();
    }
    header("Location: $url");
    exit;
}

/**
 * Display a flash message
 * 
 * @param string $message Message to display
 * @param string $type Message type (success, danger, warning, info)
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Get and clear flash message
 * 
 * @return array|null Flash message or null if none exists
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Check if user is logged in, redirect if not
 * 
 * @param string $redirect_url URL to redirect to if not logged in
 */
function requireLogin($redirect_url = '/modules/auth/login.php') {
    if (!isset($_SESSION['user_id'])) {
        setFlashMessage('Please log in to access this page', 'warning');
        redirect(BASE_URL . $redirect_url);
    }
}

/**
 * Check if user has required role, redirect if not
 * 
 * @param string|array $required_roles Required role(s)
 * @param string $redirect_url URL to redirect to if not authorized
 */
function requireRole($required_roles, $redirect_url = '/dashboard.php') {
    requireLogin();
    
    if (!is_array($required_roles)) {
        $required_roles = [$required_roles];
    }
    
    if (!in_array($_SESSION['role'], $required_roles)) {
        setFlashMessage('You do not have permission to access this page', 'danger');
        redirect(BASE_URL . $redirect_url);
    }
}

/**
 * Sanitize input data
 * 
 * @param string $data Data to sanitize
 * @return string Sanitized data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Format date for display
 * 
 * @param string $date Date string
 * @param string $format Date format
 * @return string Formatted date
 */
function formatDate($date, $format = 'Y-m-d') {
    $datetime = new DateTime($date);
    return $datetime->format($format);
}

/**
 * Format datetime for display
 * 
 * @param string $datetime Datetime string
 * @param string $format Datetime format
 * @return string Formatted datetime
 */
function formatDateTime($datetime, $format = 'Y-m-d H:i:s') {
    $dt = new DateTime($datetime);
    return $dt->format($format);
}

/**
 * Get current date in MySQL format
 * 
 * @return string Current date
 */
function getCurrentDate() {
    return date('Y-m-d');
}

/**
 * Get current datetime in MySQL format
 * 
 * @return string Current datetime
 */
function getCurrentDateTime() {
    return date('Y-m-d H:i:s');
}

/**
 * Check if a string is a valid date
 * 
 * @param string $date Date string
 * @param string $format Date format
 * @return bool True if valid date, false otherwise
 */
function isValidDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Generate a CSRF token
 * 
 * @return string CSRF token
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * 
 * @param string $token Token to verify
 * @return bool True if token is valid, false otherwise
 */
function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token input field
 * 
 * @return string HTML input field with CSRF token
 */
function getCsrfTokenField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Check if request is POST
 * 
 * @return bool True if request is POST, false otherwise
 */
function isPostRequest() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Get current user's role
 * 
 * @return string|null User role or null if not logged in
 */
function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

/**
 * Check if user has a specific role
 * 
 * @param string|array $roles Role(s) to check
 * @return bool True if user has the role, false otherwise
 */
function hasRole($roles) {
    if (!isset($_SESSION['role'])) {
        return false;
    }
    
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    
    return in_array($_SESSION['role'], $roles);
}

/**
 * Format currency for display
 * 
 * @param float $amount Amount to format
 * @param string $symbol Currency symbol
 * @return string Formatted currency
 */
function formatCurrency($amount, $symbol = '$') {
    if (empty($amount)) return $symbol . '0.00';
    return $symbol . number_format((float)$amount, 2);
}
