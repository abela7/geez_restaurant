<?php
/**
 * Emergency Password Reset Script
 * 
 * This script directly updates the password for a specific user in the database
 * bypassing the User class to troubleshoot login issues.
 */

// Include configuration
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Database.php';

// Output as plain text for debugging
header('Content-Type: text/plain');

// The user to reset
$username = 'Abela';
$password = '123456';

// Connect to database
try {
    $db = new Database();
    echo "Database connection successful.\n";
} catch (Exception $e) {
    die("ERROR: Could not connect to database: " . $e->getMessage() . "\n");
}

// Verify user exists
$user = $db->fetchRow("SELECT * FROM users WHERE username = ?", [$username]);
if (!$user) {
    die("ERROR: User '{$username}' not found in the database.\n");
}

echo "User found: \n";
echo "- User ID: " . $user['user_id'] . "\n";
echo "- Username: " . $user['username'] . "\n";
echo "- Full Name: " . $user['full_name'] . "\n";
echo "- Current status: " . ($user['is_active'] ? "ACTIVE" : "INACTIVE") . "\n";
echo "- Current hash: " . $user['password'] . "\n";

// Generate a new password hash
$new_hash = password_hash($password, PASSWORD_DEFAULT);
echo "\nGenerated new hash for password '{$password}':\n";
echo $new_hash . "\n";

// Update directly in the database
try {
    // Method 1: Use Database class update method
    $affected = $db->update('users', 
        ['password' => $new_hash], 
        'user_id = ?', 
        [$user['user_id']]
    );
    
    echo "\nPassword updated successfully via Database::update().\n";
    echo "Affected rows: {$affected}\n";
    
    // Method 2: Direct query for extra certainty (if method 1 fails)
    $sql = "UPDATE users SET password = ? WHERE user_id = ?";
    $stmt = $db->query($sql, [$new_hash, $user['user_id']]);
    
    echo "Password updated successfully via direct query.\n";
    echo "Affected rows: " . $stmt->rowCount() . "\n";
    
    // Also ensure user is active
    $db->update('users', ['is_active' => 1], 'user_id = ?', [$user['user_id']]);
    echo "User account set to ACTIVE.\n";
    
    // Verify the update
    $updated_user = $db->fetchRow("SELECT password FROM users WHERE user_id = ?", [$user['user_id']]);
    echo "\nVerification:\n";
    echo "- New stored hash: " . $updated_user['password'] . "\n";
    
    // Test password verification
    $verify = password_verify($password, $updated_user['password']);
    echo "- Verification test: " . ($verify ? "SUCCESS" : "FAILED") . "\n";
    
    if ($verify) {
        echo "\nEverything looks good. You should now be able to log in with:\n";
        echo "Username: {$username}\n";
        echo "Password: {$password}\n";
    } else {
        echo "\nWARNING: Something is still wrong with password verification.\n";
    }
    
} catch (Exception $e) {
    echo "ERROR updating password: " . $e->getMessage() . "\n";
}

echo "\n----------------------------------------\n";
echo "For security, delete this file after use!\n";
echo "----------------------------------------\n";
?> 