<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set higher time limit and memory limit
set_time_limit(300); // 5 minutes
ini_set('memory_limit', '256M');

echo "<h1>Improved Cleaning Log Generator</h1>";

// Use the existing database configuration
require_once 'config/database.php';

echo "<p>Using database configuration from config/database.php</p>";

try {
    // Connect directly to database using the existing configuration
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color:green'>Database connection successful!</p>";
    
    // Set start and end dates
    $startDate = new DateTime('2023-08-12');
    $endDate = new DateTime(); // Today
    $currentDate = clone $startDate;
    
    echo "<p>Date range: " . $startDate->format('Y-m-d') . " to " . $endDate->format('Y-m-d') . "</p>";
    
    // ONLY get tasks for Kitchen (location_id = 12)
    $taskQuery = "SELECT task_id, description, frequency, location_id FROM cleaning_task 
                  WHERE location_id = 12 AND is_active = 1 
                  ORDER BY task_id";
    $taskStmt = $conn->prepare($taskQuery);
    $taskStmt->execute();
    $tasks = $taskStmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Found " . count($tasks) . " tasks for the Kitchen</p>";
    
    // ONLY get specified users (5, 6, and 8)
    $allowedUsers = [5, 6, 8];
    $placeholders = implode(',', array_fill(0, count($allowedUsers), '?'));
    $userQuery = "SELECT user_id FROM users WHERE user_id IN ($placeholders) AND is_active = 1";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->execute($allowedUsers);
    $users = $userStmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Found " . count($users) . " allowed users (IDs: " . implode(', ', $users) . ")</p>";
    
    // Check if we have users and tasks
    if (empty($users)) {
        die("<p style='color:red'>No specified users found in the database.</p>");
    }
    
    if (empty($tasks)) {
        die("<p style='color:red'>No active tasks found for the Kitchen location.</p>");
    }
    
    // Group tasks by frequency for better control
    $tasksByFrequency = [
        'daily' => [],
        'weekly' => [],
        'monthly' => []
    ];
    
    foreach ($tasks as $task) {
        $frequency = strtolower($task['frequency']);
        if (isset($tasksByFrequency[$frequency])) {
            $tasksByFrequency[$frequency][] = $task;
        } else {
            $tasksByFrequency['daily'][] = $task; // Default to daily if frequency is unknown
        }
    }
    
    echo "<p>Task breakdown by frequency:</p>";
    echo "<ul>";
    echo "<li>Daily tasks: " . count($tasksByFrequency['daily']) . "</li>";
    echo "<li>Weekly tasks: " . count($tasksByFrequency['weekly']) . "</li>";
    echo "<li>Monthly tasks: " . count($tasksByFrequency['monthly']) . "</li>";
    echo "</ul>";
    
    // Function to check if task should be completed on this date based on frequency
    function shouldCompleteTask($task, $date) {
        $frequency = strtolower($task['frequency']);
        
        if ($frequency === 'daily') {
            return true;
        } elseif ($frequency === 'weekly') {
            // Complete on Monday
            return $date->format('N') === '1';
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
    
    // Form with options for confirmation
    echo "<div style='margin-top:20px; padding:15px; background-color:#f0f0f0;'>";
    echo "<h3>Generate Improved Cleaning Logs</h3>";
    echo "<p><strong>Important Changes:</strong></p>";
    echo "<ul>";
    echo "<li>Only users 5, 6, and 8 will be assigned to cleaning tasks</li>";
    echo "<li>Completion rate will be set to approximately 90%</li>";
    echo "<li>All basic daily kitchen tasks will be completed</li>";
    echo "</ul>";
    echo "<p>This will create cleaning logs from August 12, 2023 to today.</p>";
    
    // Form with options
    echo "<form method='post'>";
    echo "<input type='hidden' name='confirm' value='yes'>";
    echo "<input type='submit' value='Generate Improved Cleaning Logs' style='background-color: #4CAF50; color: white; padding: 10px 15px; border: none; cursor: pointer; margin-top:10px;'>";
    echo "</form>";
    echo "</div>";
    
    // Only proceed if confirmed
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        // Clear ALL existing cleaning logs first
        echo "<p>Clearing ALL existing cleaning logs...</p>";
        $conn->exec("DELETE FROM cleaning_log");
        $conn->exec("ALTER TABLE cleaning_log AUTO_INCREMENT = 1");
        echo "<p style='color:green'>All existing cleaning logs cleared.</p>";
        
        // Begin transaction for faster inserts
        $conn->beginTransaction();
        
        $totalInserted = 0;
        $dailyTasksCompleted = 0;
        $totalDailyTasksExpected = 0;
        
        try {
            echo "<p>Starting log generation with improved completion rates...</p>";
            
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
                
                // DAILY TASKS: Complete 98% of daily tasks
                foreach ($tasksByFrequency['daily'] as $task) {
                    $totalDailyTasksExpected++;
                    // 98% chance of completion for daily tasks
                    if (rand(1, 100) <= 98) {
                        $taskId = $task['task_id'];
                        $locationId = $task['location_id'];
                        $completedDate = $dateString;
                        $completedTime = getRandomTime();
                        $completedById = $users[array_rand($users)]; // Randomly select from our allowed users
                        $notes = null;
                        
                        // 95% chance of being verified
                        $isVerified = rand(1, 100) <= 95 ? 1 : 0;
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
                        $dailyTasksCompleted++;
                    }
                }
                
                // WEEKLY TASKS: Complete 85% of weekly tasks on appropriate days
                foreach ($tasksByFrequency['weekly'] as $task) {
                    if (shouldCompleteTask($task, $currentDate)) {
                        // 85% chance of task being completed
                        if (rand(1, 100) <= 85) {
                            $taskId = $task['task_id'];
                            $locationId = $task['location_id'];
                            $completedDate = $dateString;
                            $completedTime = getRandomTime();
                            $completedById = $users[array_rand($users)];
                            $notes = null;
                            
                            // 85% chance of being verified
                            $isVerified = rand(1, 100) <= 85 ? 1 : 0;
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
                        }
                    }
                }
                
                // MONTHLY TASKS: Complete 80% of monthly tasks on appropriate days
                foreach ($tasksByFrequency['monthly'] as $task) {
                    if (shouldCompleteTask($task, $currentDate)) {
                        // 80% chance of task being completed
                        if (rand(1, 100) <= 80) {
                            $taskId = $task['task_id'];
                            $locationId = $task['location_id'];
                            $completedDate = $dateString;
                            $completedTime = getRandomTime();
                            $completedById = $users[array_rand($users)];
                            $notes = null;
                            
                            // 80% chance of being verified
                            $isVerified = rand(1, 100) <= 80 ? 1 : 0;
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
                        }
                    }
                }
                
                // Move to next day
                $currentDate->modify('+1 day');
                
                // Output progress every 30 days
                $daysPassed = $startDate->diff($currentDate)->days;
                if ($daysPassed % 30 === 0) {
                    $percentComplete = ($currentDate->getTimestamp() - $startDate->getTimestamp()) / 
                                    ($endDate->getTimestamp() - $startDate->getTimestamp()) * 100;
                    echo "<p>Processed " . $daysPassed . " days (" . 
                         number_format($percentComplete, 1) . "% complete)...</p>";
                    flush();
                    ob_flush();
                }
            }
            
            // Commit the transaction
            $conn->commit();
            
            // Calculate daily tasks completion rate
            $dailyCompletionRate = ($dailyTasksCompleted / $totalDailyTasksExpected) * 100;
            
            echo "<h2 style='color:green'>Success!</h2>";
            echo "<p>Successfully inserted {$totalInserted} cleaning log records.</p>";
            echo "<p>Daily tasks completion rate: " . number_format($dailyCompletionRate, 1) . "%</p>";
            echo "<p>This should result in an overall completion rate of approximately 90%.</p>";
            echo "<p><a href='modules/cleaning/report.php' class='btn' style='background-color:#007bff; color:white; padding:5px 10px; text-decoration:none;'>View Cleaning Reports</a></p>";
            
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