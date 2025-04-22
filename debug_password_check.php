<?php

echo "<pre>"; // Use preformatted text for clear output

// --- Configuration ---
$debug_username = 'Abela';
$debug_password = '123456';

// Attempt to include the main configuration/bootstrap file
// Adjust the path if your main config file is different
// $bootstrap_path = __DIR__ . '/config/bootstrap.php';
$config_path = __DIR__ . '/config/config.php'; // Changed to config.php

echo "Attempting to load configuration from: " . $config_path . "\n";

if (!file_exists($config_path)) {
    echo "ERROR: Configuration file not found at '{$config_path}'. Please check the path.\n";
    echo "Script cannot continue without database connection.\n";
    echo "</pre>";
    exit;
}

// require_once $bootstrap_path;
require_once $config_path; // Changed to config.php

// Manually include database config and class, then instantiate DB connection
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Database.php';
$db = new Database(); 

echo "Configuration and Database connection initialized.\n";

// Check if the $db object (or your database connection variable) is available
// global $db; // No longer need global as we are instantiating here
if (!isset($db) || !is_object($db) || !method_exists($db, 'fetchRow')) { 
    echo "ERROR: Database connection object ($db) failed to initialize correctly or doesn't have expected 'fetchRow' method.\n";
    echo "</pre>";
    exit;
}

echo "Database connection object found.\n";

// --- Database Check ---
echo "\nFetching user data for username: '{$debug_username}'...\n";

$sql = "SELECT user_id, username, password FROM users WHERE username = ? LIMIT 1";
$user_data = $db->fetchRow($sql, [$debug_username]);

if (!$user_data) {
    echo "ERROR: User '{$debug_username}' not found in the database.\n";
} else {
    echo "User found. User ID: " . $user_data['user_id'] . "\n";
    $stored_hash = $user_data['password'];
    echo "Stored Hash: " . htmlspecialchars($stored_hash) . "\n";
    echo "Password to check: '{$debug_password}'\n";

    // --- Verification ---
    echo "\nVerifying password against stored hash...\n";

    if (password_verify($debug_password, $stored_hash)) {
        echo "\n------------------------\n";
        echo "SUCCESS: Password verification successful!\n";
        echo "------------------------\n";
        echo "This means the password '{$debug_password}' matches the hash stored in the database.\n";
        echo "The login issue might be related to the User::authenticate method logic (e.g., is_active check, session handling) or form processing.\n";
    } else {
        echo "\n------------------------\n";
        echo "FAILURE: Password verification failed!\n";
        echo "------------------------\n";
        echo "This means the password '{$debug_password}' DOES NOT match the hash '" . htmlspecialchars($stored_hash) . "' using password_verify.\n";
        echo "Possible reasons:\n";
        echo "  - The hash in the database is incorrect/corrupted.\n";
        echo "  - The password '{$debug_password}' is not the one used to generate this hash.\n";
        echo "  - Potential subtle issues with character encoding (less likely with bcrypt).\n";
        echo "  - PHP version compatibility issue (unlikely but possible).\n";

        // Optional: Check hash info
        $hash_info = password_get_info($stored_hash);
        echo "\nHash Info:\n";
        print_r($hash_info);

    }
}

echo "</pre>";

?> 