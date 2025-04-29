<?php
// Script to generate cleaning logs from August 12, 2023 to current date
require_once 'includes/config.php';
require_once 'classes/Database.php';

// Connect to database
$db = new Database();
$conn = $db->getConnection();

// Set start and end dates
$startDate = new DateTime('2023-08-12');
$endDate = new DateTime(); // Today
$currentDate = clone $startDate;

// Get all active tasks for Kitchen (location_id = 12)
$taskQuery = "SELECT task_id, description, frequency, location_id FROM cleaning_task 
              WHERE location_id = 12 AND is_active = 1 
              ORDER BY task_id";
$taskStmt = $conn->prepare($taskQuery);
$taskStmt->execute();
$tasks = $taskStmt->fetchAll(PDO::FETCH_ASSOC);

// Get active users who can complete tasks
$userQuery = "SELECT user_id FROM users WHERE is_active = 1";
$userStmt = $conn->prepare($userQuery);
$userStmt->execute();
$users = $userStmt->fetchAll(PDO::FETCH_COLUMN);

// Check if we have users and tasks
if (empty($users)) {
    die("No active users found in the database.");
}

if (empty($tasks)) {
    die("No active tasks found for the Kitchen location.");
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

// Begin transaction for faster inserts
$conn->beginTransaction();

$totalInserted = 0;
$errors = [];

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
                }
            }
        }
        
        // Move to next day
        $currentDate->modify('+1 day');
    }
    
    // Commit the transaction
    $conn->commit();
    
    echo "<h2>Success!</h2>";
    echo "<p>Successfully inserted {$totalInserted} cleaning log records from August 12, 2023 to " . $endDate->format('Y-m-d') . "</p>";
    
} catch (Exception $e) {
    // Roll back the transaction on error
    $conn->rollBack();
    echo "<h2>Error</h2>";
    echo "<p>An error occurred: " . $e->getMessage() . "</p>";
} 