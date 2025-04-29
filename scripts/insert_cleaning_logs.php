<?php
/**
 * Insert Cleaning Logs Script
 * 
 * Populates cleaning logs from August 12, 2023 to the current date
 * for kitchen areas (locations 5, 6, and
 */

// Include configuration and common functions
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once CLASS_PATH . '/Database.php';

// Initialize database with error handling
try {
    $db = new Database();
    echo "Database connection successful.\n";
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

// Include required classes
require_once CLASS_PATH . '/CleaningLog.php';
require_once CLASS_PATH . '/CleaningTask.php';
require_once CLASS_PATH . '/CleaningLocation.php';
require_once CLASS_PATH . '/User.php';

// Initialize classes
$cleaning_log = new CleaningLog($db);
$cleaning_task = new CleaningTask($db);
$cleaning_location = new CleaningLocation($db);
$user = new User($db);

// ========== CONFIGURATION ==========
// Set script parameters
$start_date = '2023-08-12';
$end_date = date('Y-m-d'); // Current date
$location_ids = [5, 6, 8]; // Kitchen areas
$time_range_start = '22:00:00';
$time_range_end = '23:00:00';

echo "Script will create cleaning logs from $start_date to $end_date\n";
echo "For locations: " . implode(", ", $location_ids) . "\n";
echo "With times between $time_range_start and $time_range_end\n\n";

// ========== CHECK LOCATIONS ==========
// Check if the locations exist
$valid_locations = [];
foreach ($location_ids as $location_id) {
    $location = $cleaning_location->getById($location_id);
    if ($location) {
        $valid_locations[] = $location_id;
        echo "Location ID $location_id: {$location['name']} - Valid\n";
    } else {
        echo "Warning: Location ID $location_id not found in database.\n";
    }
}

if (empty($valid_locations)) {
    // Create kitchen locations if they don't exist
    echo "No valid locations found. Creating kitchen locations...\n";
    
    $kitchen_locations = [
        ['location_id' => 5, 'name' => 'Kitchen - Prep Area', 'description' => 'Food preparation area in kitchen'],
        ['location_id' => 6, 'name' => 'Kitchen - Cooking Area', 'description' => 'Stoves, ovens and cooking equipment'],
        ['location_id' => 8, 'name' => 'Kitchen - Floor Area', 'description' => 'Kitchen floors and surfaces']
    ];
    
    foreach ($kitchen_locations as $loc_data) {
        $loc_id = $loc_data['location_id'];
        $loc_data['is_active'] = 1;
        $loc_data['created_at'] = date('Y-m-d H:i:s');
        
        try {
            // Try direct insert first with location_id
            $sql = "INSERT INTO cleaning_locations (location_id, name, description, is_active, created_at) 
                    VALUES (?, ?, ?, ?, ?)";
            $result = $db->execute($sql, [
                $loc_data['location_id'],
                $loc_data['name'],
                $loc_data['description'],
                $loc_data['is_active'],
                $loc_data['created_at']
            ]);
            
            echo "Created location {$loc_data['name']} with ID {$loc_id}\n";
            $valid_locations[] = $loc_id;
        } catch (Exception $e) {
            // If direct insert fails, try without specifying ID
            unset($loc_data['location_id']);
            try {
                $new_id = $cleaning_location->create($loc_data);
                if ($new_id) {
                    echo "Created location {$loc_data['name']} with auto-generated ID {$new_id}\n";
                    $valid_locations[] = $new_id;
                }
            } catch (Exception $e2) {
                echo "Error creating location: " . $e2->getMessage() . "\n";
            }
        }
    }
}

if (empty($valid_locations)) {
    die("Error: No valid locations available. Cannot continue.\n");
}

// ========== GET STAFF IDS ==========
// Get kitchen staff user IDs
try {
    $staff_sql = "SELECT user_id, full_name FROM users WHERE role = 'staff' AND is_active = 1";
    $kitchen_staff = $db->fetchAll($staff_sql);
    $staff_ids = array_column($kitchen_staff, 'user_id');
    
    echo "\nFound " . count($staff_ids) . " active staff members:\n";
    foreach ($kitchen_staff as $staff) {
        echo "  - ID {$staff['user_id']}: {$staff['full_name']}\n";
    }
} catch (Exception $e) {
    echo "Error fetching staff: " . $e->getMessage() . "\n";
    $staff_ids = [];
}

// If no staff found, use default IDs
if (empty($staff_ids)) {
    $staff_ids = [5, 6, 7]; // Default IDs from database dump
    echo "Using default staff IDs: " . implode(", ", $staff_ids) . "\n";
}

// ========== GET OR CREATE TASKS ==========
// Get tasks by location
$tasks_by_location = [];
$task_count = 0;

foreach ($valid_locations as $location_id) {
    try {
        $tasks = $cleaning_task->getByLocation($location_id);
        if (!empty($tasks)) {
            $tasks_by_location[$location_id] = $tasks;
            $task_count += count($tasks);
        }
    } catch (Exception $e) {
        echo "Error fetching tasks for location $location_id: " . $e->getMessage() . "\n";
    }
}

echo "\nFound $task_count existing cleaning tasks.\n";

// If no tasks found, create sample tasks
if (empty($tasks_by_location) || $task_count == 0) {
    echo "Creating sample cleaning tasks...\n";
    
    // Define sample tasks for kitchen areas
    $sample_tasks = [
        // Daily tasks (basics that should almost always be done)
        ['location_id' => 5, 'description' => 'Clean kitchen counters', 'frequency' => 'daily'],
        ['location_id' => 5, 'description' => 'Sanitize food prep areas', 'frequency' => 'daily'],
        ['location_id' => 5, 'description' => 'Empty kitchen trash bins', 'frequency' => 'daily'],
        ['location_id' => 6, 'description' => 'Clean stove tops', 'frequency' => 'daily'],
        ['location_id' => 6, 'description' => 'Clean kitchen sinks', 'frequency' => 'daily'],
        ['location_id' => 8, 'description' => 'Sweep kitchen floors', 'frequency' => 'daily'],
        ['location_id' => 8, 'description' => 'Mop kitchen floors', 'frequency' => 'daily'],
        
        // Weekly tasks
        ['location_id' => 5, 'description' => 'Deep clean refrigerators', 'frequency' => 'weekly'],
        ['location_id' => 6, 'description' => 'Clean oven interior', 'frequency' => 'weekly'],
        ['location_id' => 6, 'description' => 'Clean kitchen hoods', 'frequency' => 'weekly'],
        ['location_id' => 8, 'description' => 'Clean kitchen walls', 'frequency' => 'weekly'],
        
        // Monthly tasks
        ['location_id' => 5, 'description' => 'Clean behind refrigerators', 'frequency' => 'monthly'],
        ['location_id' => 6, 'description' => 'Descale coffee machines', 'frequency' => 'monthly'],
        ['location_id' => 8, 'description' => 'Deep clean floor drains', 'frequency' => 'monthly'],
    ];
    
    // Map sample tasks to valid location IDs if needed
    if (count($valid_locations) < 3) {
        $loc_map = array_combine([5, 6, 8], array_pad($valid_locations, 3, $valid_locations[0]));
        foreach ($sample_tasks as &$task_data) {
            $task_data['location_id'] = $loc_map[$task_data['location_id']] ?? $valid_locations[0];
        }
    }
    
    // Create the tasks
    $created_count = 0;
    foreach ($sample_tasks as $task_data) {
        try {
            $task_data['is_active'] = 1;
            $task_data['created_at'] = date('Y-m-d H:i:s');
            
            // Ensure location ID is valid
            if (!in_array($task_data['location_id'], $valid_locations)) {
                $task_data['location_id'] = $valid_locations[0];
            }
            
            $task_id = $cleaning_task->create($task_data);
            
            if ($task_id) {
                $location_id = $task_data['location_id'];
                if (!isset($tasks_by_location[$location_id])) {
                    $tasks_by_location[$location_id] = [];
                }
                $task_data['task_id'] = $task_id;
                $tasks_by_location[$location_id][] = $task_data;
                $created_count++;
            }
        } catch (Exception $e) {
            echo "Error creating task '{$task_data['description']}': " . $e->getMessage() . "\n";
        }
    }
    
    echo "Created $created_count new cleaning tasks.\n";
}

// ========== HELPER FUNCTIONS ==========
// Function to get a random time between the specified range
function getRandomTime($start, $end) {
    $start_seconds = strtotime($start);
    $end_seconds = strtotime($end);
    $random_seconds = rand($start_seconds, $end_seconds);
    return date('H:i:s', $random_seconds);
}

// Function to determine if a task should be completed (based on frequency and realism)
function shouldCompleteTask($task, $date, $day_of_week, $day_of_month) {
    $frequency = $task['frequency'] ?? 'daily';
    $random = mt_rand(1, 100);
    
    switch ($frequency) {
        case 'daily':
            // 95% chance of doing basic daily tasks
            return $random <= 95;
        
        case 'weekly':
            // Weekly tasks done mostly on Monday (80% chance) or another day if missed (20% chance)
            if ($day_of_week == 1) { // Monday
                return $random <= 80;
            } else {
                // Small chance to do it on other days (perhaps catch-up if missed Monday)
                return $random <= 20;
            }
            
        case 'monthly':
            // Monthly tasks done mostly at beginning of month (day 1-3)
            if ($day_of_month <= 3) {
                return $random <= 75;
            } else if ($day_of_month <= 7) {
                // Catch-up period if missed in first 3 days
                return $random <= 30;
            } else {
                // Very small chance on other days
                return $random <= 5;
            }
            
        default:
            return $random <= 90; // Default high completion rate
    }
}

// ========== INSERT CLEANING LOGS ==========
// Initialize counters
$total_logs = 0;
$total_days = 0;

echo "\nStarting to insert cleaning logs from {$start_date} to {$end_date}...\n";

// Process each day from start date to end date
$current_date = new DateTime($start_date);
$end_date_obj = new DateTime($end_date);

// Check if there are already entries within this date range
try {
    $exist_sql = "SELECT COUNT(*) as count FROM cleaning_log 
                  WHERE completed_date BETWEEN ? AND ?";
    $existing = $db->fetchRow($exist_sql, [$start_date, $end_date]);
    
    if ($existing && $existing['count'] > 0) {
        echo "Found {$existing['count']} existing cleaning logs in the date range.\n";
        $prompt = "Do you want to continue and possibly create duplicate entries? (y/n): ";
        if (PHP_SAPI !== 'cli') {
            // If not running in command line, assume yes
            echo "Automatically continuing with insertion...\n";
        } else {
            echo $prompt;
            $handle = fopen("php://stdin", "r");
            $line = fgets($handle);
            if (trim(strtolower($line)) != 'y') {
                echo "Aborting operation.\n";
                exit;
            }
            fclose($handle);
        }
    }
} catch (Exception $e) {
    echo "Error checking for existing entries: " . $e->getMessage() . "\n";
}

// Main loop to insert logs
while ($current_date <= $end_date_obj) {
    $date = $current_date->format('Y-m-d');
    $day_of_week = (int)$current_date->format('N'); // 1 (Monday) to 7 (Sunday)
    $day_of_month = (int)$current_date->format('j'); // 1 to 31
    
    // Process each location
    foreach ($tasks_by_location as $location_id => $tasks) {
        // Get a random staff ID for this location on this day
        $staff_id = $staff_ids[array_rand($staff_ids)];
        
        // Process each task for this location
        foreach ($tasks as $task) {
            // Determine if this task should be completed today
            if (shouldCompleteTask($task, $date, $day_of_week, $day_of_month)) {
                // Generate random completion time
                $completion_time = getRandomTime($time_range_start, $time_range_end);
                
                // Create log entry data
                $log_data = [
                    'task_id' => $task['task_id'],
                    'location_id' => $location_id,
                    'completed_date' => $date,
                    'completed_time' => $completion_time,
                    'completed_by_user_id' => $staff_id,
                    'is_verified' => 1, // Task is completed (mapped to is_verified)
                    'notes' => null,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                try {
                    // Insert the log entry
                    $log_id = $cleaning_log->create($log_data);
                    
                    if ($log_id) {
                        $total_logs++;
                    }
                } catch (Exception $e) {
                    // Just count errors but continue processing
                    static $error_count = 0;
                    $error_count++;
                    
                    if ($error_count <= 5) {
                        echo "Error creating log for task {$task['task_id']} on $date: " . $e->getMessage() . "\n";
                    } else if ($error_count == 6) {
                        echo "Additional errors suppressed...\n";
                    }
                }
            }
        }
    }
    
    // Move to the next day
    $current_date->modify('+1 day');
    $total_days++;
    
    // Show progress every 30 days
    if ($total_days % 30 == 0) {
        echo "Processed {$total_days} days, inserted {$total_logs} log entries...\n";
    }
}

echo "Finished! Processed {$total_days} days and inserted {$total_logs} cleaning log entries.\n";

// Return success message for web execution
if (PHP_SAPI !== 'cli') {
    echo "<script>alert('Successfully inserted {$total_logs} cleaning log entries over {$total_days} days!');</script>";
} 