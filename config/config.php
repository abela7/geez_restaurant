<?php
/**
 * Application Configuration
 * 
 * This file contains global configuration settings for the Geez Restaurant application.
 */

// Start output buffering to prevent "headers already sent" errors
ob_start();

// Error reporting (set to 0 in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Application paths
define('BASE_PATH', dirname(__DIR__));
define('INCLUDE_PATH', BASE_PATH . '/includes');
define('CLASS_PATH', BASE_PATH . '/classes');
define('MODULE_PATH', BASE_PATH . '/modules');
define('ASSET_PATH', BASE_PATH . '/assets');

// URL paths (adjust based on your local setup)
define('BASE_URL', '/geez_restaurant');
define('ASSET_URL', BASE_URL . '/assets');

// Session settings
define('SESSION_NAME', 'geez_restaurant_session');
define('SESSION_LIFETIME', 7200); // 2 hours

// Application settings
define('APP_NAME', 'Geez Restaurant Management System');
define('APP_VERSION', '1.0.0');

// Date and time settings
date_default_timezone_set('UTC'); // Change to your timezone
