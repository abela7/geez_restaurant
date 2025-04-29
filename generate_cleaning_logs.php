<?php
// Enable detailed error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set higher time limit and memory limit
set_time_limit(300); // 5 minutes
ini_set('memory_limit', '256M');

echo "<h2>Cleaning Log Generator</h2>";
echo "<p>Starting process...</p>";

// Try to determine the base path
$basePath = __DIR__;
echo "<p>Base path: {$basePath}</p>";

// Try different ways to include required files
$includesPath = $basePath . '/includes/config.php';
$classesPath = $basePath . '/classes/Database.php';

echo "<p>Looking for: {$includesPath}</p>";
echo "<p>Looking for: {$classesPath}</p>";

// Check if files exist
if (!file_exists($includesPath)) {
    echo "<p style='color:red'>ERROR: Cannot find config.php file</p>";
    
    // Try alternative path
    $includesPath = $basePath . '/../includes/config.php';
    echo "<p>Trying alternative: {$includesPath}</p>";
    
    if (!file_exists($includesPath)) {
        die("<p style='color:red'>ERROR: Cannot find config.php in alternative location either</p>");
    }
}

if (!file_exists($classesPath)) {
    echo "<p style='color:red'>ERROR: Cannot find Database.php file</p>";
    
    // Try alternative path
    $classesPath = $basePath . '/../classes/Database.php';
    echo "<p>Trying alternative: {$classesPath}</p>";
    
    if (!file_exists($classesPath)) {
        die("<p style='color:red'>ERROR: Cannot find Database.php in alternative location either</p>");
    }
}

// Now try to include the files
try {
    require_once $includesPath;
    echo "<p style='color:green'>Successfully included config.php</p>";
} catch (Exception $e) {
    die("<p style='color:red'>Error including config.php: " . $e->getMessage() . "</p>");
}

try {
    require_once $classesPath;
    echo "<p style='color:green'>Successfully included Database.php</p>";
} catch (Exception $e) {
    die("<p style='color:red'>Error including Database.php: " . $e->getMessage() . "</p>");
}

// Try database connection
try {
    echo "<p>Attempting database connection...</p>";
    $db = new Database();
    $conn = $db->getConnection();
    echo "<p style='color:green'>Database connection successful!</p>";
} catch (Exception $e) {
    die("<p style='color:red'>Database connection failed: " . $e->getMessage() . "</p>");
}

// Set start and end dates
$startDate = new DateTime('2023-08-12');
$endDate = new DateTime(); // Today
$currentDate = clone $startDate;

echo "<p>Date range: " . $startDate->format('Y-m-d') . " to " . $endDate->format('Y-m-d') . "</p>";

// Get all tasks for Kitchen (location_id = 12)
try {
    echo "<p>Fetching tasks...</p>";
    $taskQuery = "SELECT task_id, description, frequency, location_id FROM cleaning_task 
                  WHERE location_id = 12 AND is_active = 1 
                  ORDER BY task_id";
    $taskStmt = $conn->prepare($taskQuery);
    $taskStmt->execute();
    $tasks = $taskStmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p style='color:green'>Found " . count($tasks) . " tasks</p>";
    
    if (count($tasks) > 0) {
        echo "<p>First task: ID=" . $tasks[0]['task_id'] . ", Description=" . $tasks[0]['description'] . "</p>";
    }
} catch (Exception $e) {
    die("<p style='color:red'>Error fetching tasks: " . $e->getMessage() . "</p>");
}

// Get active users
try {
    echo "<p>Fetching users...</p>";
    $userQuery = "SELECT user_id FROM users WHERE is_active = 1";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->execute();
    $users = $userStmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p style='color:green'>Found " . count($users) . " users</p>";
    
    if (count($users) > 0) {
        echo "<p>First user ID: " . $users[0] . "</p>";
    }
} catch (Exception $e) {
    die("<p style='color:red'>Error fetching users: " . $e->getMessage() . "</p>");
}

// Check if we have users and tasks
if (empty($users)) {
    die("<p style='color:red'>No active users found in the database.</p>");
}

if (empty($tasks)) {
    die("<p style='color:red'>No active tasks found for the Kitchen location.</p>");
}

// Function to check if task should be completed on this date based on frequency
function shouldCompleteTask($task, $date) {
    $frequency = strtolower($task['frequency']);
    
    if ($frequency === 'daily') {
        return true;
    } elseif ($frequency === 'weekly') {
        // Complete on the same day of week as the start date (e.g., every Monday)
        return $date->format('N') === '1'; // Monday
    } elseif ($frequency === 'monthly') {
        // Complete on the 1st day of each month
        return $date->format('j') === '1';
    }
    
    return false;
}

// Function to generate a realistic time for cleaning
function getRandomTime() {
    // Restaurant cleaning typically happens in morning (6-10am) or evening (7-11pm)
    $hour = (rand(0, 1) === 0) 
        ? rand(6, 10)  // Morning hours
        : rand(19, 23); // Evening hours
    
    $minute = rand(0, 59);
    
    return sprintf('%02d:%02d:00', $hour, $minute);
}

// Try a single insertion first to verify it works
try {
    echo "<p>Testing a single insertion...</p>";
    
    $taskId = $tasks[0]['task_id'];
    $locationId = $tasks[0]['location_id'];
    $completedDate = date('Y-m-d');
    $completedTime = getRandomTime();
    $completedById = $users[0];
    $notes = "Test entry";
    $isVerified = 1;
    $verifiedById = $users[0];
    $verifiedAt = date('Y-m-d H:i:s');
    $createdAt = date('Y-m-d H:i:s');
    $updatedAt = date('Y-m-d H:i:s');
    
    $testInsertStmt = $conn->prepare("
        INSERT INTO cleaning_log 
        (task_id, location_id, completed_date, completed_time, completed_by_user_id, 
         notes, is_verified, verified_by_user_id, verified_at, created_at, updated_at)
        VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $testInsertStmt->execute([
        $taskId, $locationId, $completedDate, $completedTime, $completedById,
        $notes, $isVerified, $verifiedById, $verifiedAt, $createdAt, $updatedAt
    ]);
    
    echo "<p style='color:green'>Test insertion successful!</p>";
    
    // Delete test entry
    $lastId = $conn->lastInsertId();
    $deleteStmt = $conn->prepare("DELETE FROM cleaning_log WHERE log_id = ?");
    $deleteStmt->execute([$lastId]);
    echo "<p>Test entry deleted</p>";
    
} catch (Exception $e) {
    die("<p style='color:red'>Test insertion failed: " . $e->getMessage() . "</p>");
}

// Ask user to confirm if they want to proceed with full insertion
echo "<div style='margin-top:20px; padding:15px; background-color:#f0f0f0;'>";
echo "<h3>Ready to Generate Full Dataset</h3>";
echo "<p>All preliminary checks passed. The generator will now create cleaning logs from August 12, 2023 to today.</p>";
echo "<p>This may take some time and will generate many records.</p>";
echo "<a href='?confirm=yes' style='background-color: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; display: inline-block; margin-top: 10px;'>Generate Cleaning Logs</a>";
echo "</div>";

// Only proceed if confirmed
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    // Begin transaction for faster inserts
    $conn->beginTransaction();
    
    $totalInserted = 0;
    $errors = [];
    
    echo "<p>Starting log generation...</p>";
    
    try {
        // Prepare the insert statement
        $insertStmt = $conn->prepare("
            INSERT INTO cleaning_log 
            (task_id, location_id, completed_date, completed_time, completed_by_user_id, 
             notes, is_verified, verified_by_user_id, verified_at, created_at, updated_at)
            VALUES 
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        // Loop through each day from start to end date
        while ($currentDate <= $endDate) {
            $dateString = $currentDate->format('Y-m-d');
            
            // For each task, create log entry if it should be completed on this date
            foreach ($tasks as $task) {
                if (shouldCompleteTask($task, $currentDate)) {
                    // 80% chance of task being completed
                    if (rand(1, 100) <= 80) {
                        $taskId = $task['task_id'];
                        $locationId = $task['location_id'];
                        $completedDate = $dateString;
                        $completedTime = getRandomTime();
                        $completedById = $users[array_rand($users)];
                        $notes = null;
                        
                        // 70% chance of being verified
                        $isVerified = rand(1, 100) <= 70 ? 1 : 0;
                        $verifiedById = $isVerified ? $users[array_rand($users)] : null;
                        $verifiedAt = $isVerified ? $dateString . ' ' . getRandomTime() : null;
                        
                        // Created and updated timestamps
                        $createdAt = $dateString . ' ' . $completedTime;
                        $updatedAt = $createdAt;
                        
                        // Insert the record
                        $insertStmt->execute([
                            $taskId, $locationId, $completedDate, $completedTime, $completedById,
                            $notes, $isVerified, $verifiedById, $verifiedAt, $createdAt, $updatedAt
                        ]);
                        
                        $totalInserted++;
                        
                        // Output progress every 100 insertions
                        if ($totalInserted % 100 === 0) {
                            echo "<p>Inserted {$totalInserted} records so far...</p>";
                            flush();
                            ob_flush();
                        }
                    }
                }
            }
            
            // Move to next day
            $currentDate->modify('+1 day');
        }
        
        // Commit the transaction
        $conn->commit();
        
        echo "<h2 style='color:green'>Success!</h2>";
        echo "<p>Successfully inserted {$totalInserted} cleaning log records from August 12, 2023 to " . $endDate->format('Y-m-d') . "</p>";
        
    } catch (Exception $e) {
        // Roll back the transaction on error
        $conn->rollBack();
        echo "<h2 style='color:red'>Error</h2>";
        echo "<p>An error occurred: " . $e->getMessage() . "</p>";
    }
} 