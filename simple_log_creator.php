<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set higher time limit and memory limit
set_time_limit(300); // 5 minutes
ini_set('memory_limit', '256M');

echo "<h1>Simple Cleaning Log Generator</h1>";

// Direct database connection - ADJUST THESE VALUES TO MATCH YOUR DATABASE
$dbHost = 'localhost';
$dbName = 'admi_geez_db'; // Your database name
$dbUser = 'admi_geez_user'; // Your database username
$dbPass = 'PASSWORD_HERE'; // Your database password

echo "<p>Attempting direct database connection...</p>";

try {
    // Connect directly to database
    $conn = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color:green'>Database connection successful!</p>";
    
    // Set start and end dates
    $startDate = new DateTime('2023-08-12');
    $endDate = new DateTime(); // Today
    $currentDate = clone $startDate;
    
    echo "<p>Date range: " . $startDate->format('Y-m-d') . " to " . $endDate->format('Y-m-d') . "</p>";
    
    // Get all tasks for Kitchen (location_id = 12)
    $taskQuery = "SELECT task_id, description, frequency, location_id FROM cleaning_task 
                  WHERE location_id = 12 AND is_active = 1 
                  ORDER BY task_id";
    $taskStmt = $conn->prepare($taskQuery);
    $taskStmt->execute();
    $tasks = $taskStmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Found " . count($tasks) . " tasks with location_id=12</p>";
    
    // Get active users
    $userQuery = "SELECT user_id FROM users WHERE is_active = 1";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->execute();
    $users = $userStmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Found " . count($users) . " active users</p>";
    
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
    
    // Ask user to confirm if they want to proceed with full insertion
    echo "<div style='margin-top:20px; padding:15px; background-color:#f0f0f0;'>";
    echo "<h3>Generate Cleaning Logs</h3>";
    echo "<p>Ready to create cleaning logs from August 12, 2023 to today.</p>";
    echo "<p>This will create approx. " . (count($tasks) * 0.8 * ceil(($endDate->diff($startDate)->days))) . " log entries.</p>";
    
    // Form with options
    echo "<form method='post'>";
    echo "<p><input type='checkbox' name='clear_logs' value='1' checked> Clear existing cleaning logs before generating new ones</p>";
    echo "<input type='hidden' name='confirm' value='yes'>";
    echo "<input type='submit' value='Generate All Cleaning Logs' style='background-color: #4CAF50; color: white; padding: 10px 15px; border: none; cursor: pointer;'>";
    echo "</form>";
    echo "</div>";
    
    // Only proceed if confirmed
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        
        // Check if we should clear existing logs
        if (isset($_POST['clear_logs']) && $_POST['clear_logs'] == '1') {
            echo "<p>Clearing existing cleaning logs...</p>";
            $conn->exec("DELETE FROM cleaning_log");
            $conn->exec("ALTER TABLE cleaning_log AUTO_INCREMENT = 1");
            echo "<p style='color:green'>Existing logs cleared.</p>";
        }
        
        // Begin transaction for faster inserts
        $conn->beginTransaction();
        
        $totalInserted = 0;
        
        try {
            echo "<p>Starting log generation...</p>";
            
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
} catch (PDOException $e) {
    echo "<p style='color:red'>Database connection failed: " . $e->getMessage() . "</p>";
}
?> 