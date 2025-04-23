<?php
/**
 * Emergency Password Reset Script
 * 
 * This script directly updates the password for a specific user in the database.
 * IMPORTANT: Delete this file after use as it poses a security risk if left on the server.
 */

// Include configuration
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Database.php';

echo "<pre>";
echo "Emergency Password Reset\n";
echo "========================\n\n";

// Target username to update
$target_username = 'Abela';
$new_password = '123456';

// Connect to database
$db = new Database();

// Create a proper hash for the password
$new_hash = password_hash($new_password, PASSWORD_DEFAULT);

echo "Username: {$target_username}\n";
echo "New Password: {$new_password}\n";
echo "Generated Hash: {$new_hash}\n\n";

// Update the user in the database
$sql = "UPDATE users SET password = ? WHERE username = ?";
$result = $db->execute($sql, [$new_hash, $target_username]);

if ($result) {
    $affected_rows = $db->affectedRows();
    if ($affected_rows > 0) {
        echo "SUCCESS: Password updated successfully! {$affected_rows} row(s) affected.\n";
        echo "You can now login with:\n";
        echo "Username: {$target_username}\n";
        echo "Password: {$new_password}\n\n";
    } else {
        echo "ERROR: User '{$target_username}' not found in database.\n";
    }
} else {
    echo "ERROR: Database update failed. Check database connection and permissions.\n";
}

echo "SECURITY WARNING: Delete this file (fix_password.php) immediately after use!\n";
echo "</pre>";
?> 