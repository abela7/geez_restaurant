<?php
/**
 * Logout Page
 * 
 * Handles user logout for the Geez Restaurant application
 */

// Include configuration
require_once dirname(dirname(dirname(__FILE__))) . '/config/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/config/database.php';
require_once INCLUDE_PATH . '/functions.php';

// Start the session *before* trying to destroy it
if (session_status() == PHP_SESSION_NONE) {
    session_name(SESSION_NAME); // Use the defined session name
    session_start();
}

// Initialize database connection
require_once CLASS_PATH . '/Database.php';
$db = new Database();

// Initialize user
require_once CLASS_PATH . '/User.php';
$user = new User($db);

// Logout user
$user->logout();

// Redirect to login page
setFlashMessage('You have been successfully logged out.', 'success');
redirect(BASE_URL . '/modules/auth/login.php');
