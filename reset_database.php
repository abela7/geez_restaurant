<?php
/**
 * Database Reset Script
 * 
 * This script cleans up the database by:
 * 1. Keeping only the original Kitchen location
 * 2. Removing all cleaning logs
 * 3. Resetting cleaning tasks to match the original location
 */

// Include configuration and common functions
require_once dirname(__FILE__) . '/config/config.php';
require_once dirname(__FILE__) . '/config/database.php';
require_once CLASS_PATH . '/Database.php';

// Initialize database with error handling
try {
    $db = new Database();
    echo "Database connection successful.\n";
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

// 1. First, display the current state
echo "===== CURRENT DATABASE STATE =====\n";

// Check existing cleaning locations
$locations = $db->fetchAll("SELECT * FROM cleaning_locations ORDER BY location_id");
echo "Found " . count($locations) . " cleaning locations:\n";
foreach ($locations as $location) {
    echo "ID: {$location['location_id']}, Name: {$location['name']}\n";
}

// Check cleaning logs
$log_count = $db->fetchRow("SELECT COUNT(*) as count FROM cleaning_log");
echo "Found {$log_count['count']} cleaning log entries.\n";

// Check cleaning tasks
$tasks = $db->fetchAll("SELECT * FROM cleaning_task ORDER BY task_id");
echo "Found " . count($tasks) . " cleaning tasks.\n";

// 2. Start cleaning up
echo "\n===== CLEANING DATABASE =====\n";

// Begin transaction
$db->beginTransaction();

try {
    // Find Kitchen location ID 
    $kitchen = $db->fetchRow("SELECT * FROM cleaning_locations WHERE name = 'Kitchen'");
    
    if (!$kitchen) {
        echo "Error: Could not find the Kitchen location!\n";
        throw new Exception("Kitchen location not found");
    }
    
    $kitchen_id = $kitchen['location_id'];
    echo "Found Kitchen location with ID: $kitchen_id\n";
    
    // Step 1: Delete all cleaning logs
    $deleted_logs = $db->execute("DELETE FROM cleaning_log");
    echo "Deleted all cleaning logs.\n";
    
    // Step 2: Delete all locations except the main Kitchen
    $deleted_locations = $db->execute("DELETE FROM cleaning_locations WHERE location_id != ?", [$kitchen_id]);
    echo "Deleted " . $deleted_locations . " additional cleaning locations, keeping only Kitchen.\n";
    
    // Step 3: Delete cleaning tasks associated with deleted locations
    $deleted_tasks = $db->execute("DELETE FROM cleaning_task WHERE location_id != ?", [$kitchen_id]);
    echo "Deleted " . $deleted_tasks . " cleaning tasks for other locations.\n";
    
    // Step 4: Reset auto-increment for cleaning_task if needed
    if ($db->getDriver() == 'mysql') {
        // For MySQL
        $max_task_id = $db->fetchRow("SELECT MAX(task_id) as max_id FROM cleaning_task");
        $next_id = ($max_task_id['max_id'] ?? 0) + 1;
        $db->execute("ALTER TABLE cleaning_task AUTO_INCREMENT = ?", [$next_id]);
        echo "Reset cleaning_task AUTO_INCREMENT to " . $next_id . "\n";
    }
    
    // Commit changes
    $db->commit();
    echo "Database changes committed successfully.\n";
    
} catch (Exception $e) {
    // Rollback transaction on error
    $db->rollback();
    echo "Error: " . $e->getMessage() . "\n";
    echo "All changes have been rolled back.\n";
    exit(1);
}

// 3. Display final state
echo "\n===== FINAL DATABASE STATE =====\n";

// Check existing cleaning locations
$locations = $db->fetchAll("SELECT * FROM cleaning_locations ORDER BY location_id");
echo "Now have " . count($locations) . " cleaning locations:\n";
foreach ($locations as $location) {
    echo "ID: {$location['location_id']}, Name: {$location['name']}\n";
}

// Check cleaning logs
$log_count = $db->fetchRow("SELECT COUNT(*) as count FROM cleaning_log");
echo "Now have {$log_count['count']} cleaning log entries.\n";

// Check cleaning tasks
$tasks = $db->fetchAll("SELECT * FROM cleaning_task ORDER BY task_id");
echo "Now have " . count($tasks) . " cleaning tasks.\n";

echo "\nDatabase reset complete!\n";
echo "You can now run the modified insert_cleaning_logs.php script to populate the database correctly.\n";
?> 