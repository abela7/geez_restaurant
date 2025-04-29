<?php
/**
 * Delete Temperature Check
 * 
 * Handles the deletion of temperature check records
 */

// Include configuration and common functions
require_once dirname(dirname(dirname(__FILE__))) . '/config/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/config/database.php';
require_once INCLUDE_PATH . '/functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Initialize database connection
require_once CLASS_PATH . '/Database.php';
$db = new Database();

// Initialize user
require_once CLASS_PATH . '/User.php';
$user = new User($db);

// Require login
requireLogin();

// Require admin or manager role
requireRole(['admin', 'manager', 'user']);

// Initialize TempCheck class
require_once CLASS_PATH . '/TempCheck.php';
$temp_check = new TempCheck($db);

// Get check ID
$check_id = null;
if (isset($_GET['id'])) {
    // For GET requests (direct URL with ID)
    $check_id = (int)$_GET['id'];
    
    // Verify CSRF token
    if (!verifyCsrfToken($_GET['csrf_token'] ?? '')) {
        setFlashMessage('Invalid security token. Please try again.', 'danger');
        redirect(BASE_URL . '/modules/temperature/view.php');
    }
} elseif (isset($_POST['check_id'])) {
    // For POST requests (form submission)
    $check_id = (int)$_POST['check_id'];
    
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('Invalid form submission. Please try again.', 'danger');
        redirect(BASE_URL . '/modules/temperature/view.php');
    }
}

// If no check ID provided, redirect
if (!$check_id) {
    setFlashMessage('Invalid temperature check ID.', 'danger');
    redirect(BASE_URL . '/modules/temperature/view.php');
}

// Get check details to verify it exists
$check_details = $temp_check->getById($check_id);
if (!$check_details) {
    setFlashMessage('Temperature check not found.', 'danger');
    redirect(BASE_URL . '/modules/temperature/view.php');
}

// Delete the check
if ($temp_check->delete($check_id)) {
    setFlashMessage('Temperature check deleted successfully.', 'success');
} else {
    setFlashMessage('Failed to delete temperature check. Please try again.', 'danger');
}

// Redirect to temperature logs list
redirect(BASE_URL . '/modules/temperature/view.php');
?> 