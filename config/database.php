<?php
/**
 * Database Configuration
 * 
 * This file contains database connection parameters for the Geez Restaurant application.
 */

// Database connection parameters
define('DB_HOST', 'localhost');
define('DB_NAME', 'geez_restaurant');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// PDO options
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
