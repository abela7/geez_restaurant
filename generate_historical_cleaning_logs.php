<?php
/**
 * Generate Historical Cleaning Logs for Weekly and Monthly Tasks
 * This script automatically creates cleaning log entries for all weekly and monthly tasks
 * from August 2023 to the current date
 */

// Include configuration and database connection
require_once 'config/config.php';
require_once 'config/database.php';
require_once INCLUDE_PATH . '/functions.php';

// Set timezone
date_default_timezone_set('Europe/London');

// Initialize database connection
require_once CLASS_PATH . '/Database.php';
$db = new Database();

// Initialize relevant classes
require_once CLASS_PATH . '/User.php';
require_once CLASS_PATH . '/CleaningLocation.php';
require_once CLASS_PATH . '/CleaningTask.php';
require_once CLASS_PATH . '/CleaningLog.php';

$user_model = new User($db);
$location_model = new CleaningLocation($db);
$task_model = new CleaningTask($db);
$log_model = new CleaningLog($db);

// Get all active users who could be assigned cleaning tasks
$users = $user_model->getAll();
$staff_ids = [];
foreach ($users as $user) {
    // Store IDs of regular staff or managers who might do cleaning (not admin)
    if (in_array($user['role'], ['staff', 'manager'])) {
        $staff_ids[] = $user['user_id'];
    }
}

if (empty($staff_ids)) {
    die("No staff members found who could be assigned cleaning tasks.");
}

// Get all active locations
$locations = $location_model->getAllActive();
if (empty($locations)) {
    die("No active locations found.");
}

// Set date range - from August 1, 2023 to current date
$start_date = new DateTime('2023-08-01');
$end_date = new DateTime('now');
$current_date = clone $start_date;

// Track which monthly tasks have been completed each month to avoid duplicates
$monthly_completions = [];

// Helper function to check if log exists
function logExists($db, $task_id, $completed_date) {
    $query = "SELECT COUNT(*) as count FROM cleaning_logs 
              WHERE task_id = ? AND completed_date = ?";
    $result = $db->fetchRow($query, [$task_id, $completed_date]);
    return ($result && $result['count'] > 0);
}

// Process each location
foreach ($locations as $location) {
    $location_id = $location['location_id'];
    echo "Processing location: " . $location['name'] . "...<br>";
    
    // Get tasks for this location
    $tasks = $task_model->getByLocation($location_id);
    if (empty($tasks)) {
        echo "No tasks found for location: " . $location['name'] . "<br>";
        continue;
    }
    
    // Separate weekly and monthly tasks
    $weekly_tasks = [];
    $monthly_tasks = [];
    foreach ($tasks as $task) {
        $frequency = strtolower($task['frequency']);
        if ($frequency === 'weekly') {
            $weekly_tasks[] = $task;
        } elseif ($frequency === 'monthly') {
            $monthly_tasks[] = $task;
        }
    }
    
    // Reset to start date for each location
    $current_date = clone $start_date;
    
    // Loop through each day in the date range
    while ($current_date <= $end_date) {
        $day_of_week = $current_date->format('N'); // 1 (Monday) to 7 (Sunday)
        $date_string = $current_date->format('Y-m-d');
        $year_month = $current_date->format('Y-m');
        
        // Complete weekly tasks once per week (on Monday)
        if ($day_of_week == 1 && !empty($weekly_tasks)) {
            foreach ($weekly_tasks as $task) {
                // Randomly select a staff member
                $user_id = $staff_ids[array_rand($staff_ids)];
                
                // Check if this log already exists using our helper function
                $existing = logExists($db, $task['task_id'], $date_string);
                if (!$existing) {
                    // Insert the cleaning log
                    $log_model->create([
                        'task_id' => $task['task_id'],
                        'completed_date' => $date_string,
                        'completed_by_user_id' => $user_id,
                        'location_id' => $location_id,
                        'notes' => 'Completed weekly'
                    ]);
                }
            }
        }
        
        // Complete monthly tasks once per month (on the 1st)
        if ($current_date->format('j') == 1 && !empty($monthly_tasks)) {
            foreach ($monthly_tasks as $task) {
                // Create a key to track monthly completions
                $completion_key = $location_id . '-' . $task['task_id'] . '-' . $year_month;
                
                if (!isset($monthly_completions[$completion_key])) {
                    // Randomly select a staff member
                    $user_id = $staff_ids[array_rand($staff_ids)];
                    
                    // Check if this log already exists using our helper function
                    $existing = logExists($db, $task['task_id'], $date_string);
                    if (!$existing) {
                        // Insert the cleaning log
                        $log_model->create([
                            'task_id' => $task['task_id'],
                            'completed_date' => $date_string,
                            'completed_by_user_id' => $user_id,
                            'location_id' => $location_id,
                            'notes' => 'Completed monthly'
                        ]);
                    }
                    
                    // Mark as completed for this month
                    $monthly_completions[$completion_key] = true;
                }
            }
        }
        
        // Move to next day
        $current_date->modify('+1 day');
    }
    
    echo "Completed processing for location: " . $location['name'] . "<br>";
}

echo "<hr>";
echo "<h2>Historical Cleaning Logs Generation Complete!</h2>";
echo "<p>All weekly and monthly cleaning tasks have been marked as completed from August 2023 to current date.</p>";
echo "<p><a href='modules/cleaning/print_weekly_checklist.php' class='btn btn-primary'>Go to Weekly Cleaning Checklist</a></p>";
?> 