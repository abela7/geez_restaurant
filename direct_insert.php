<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Direct Database Connection Test</h1>";

// Database connection parameters
// IMPORTANT: You'll need to edit these values to match your server configuration
$dbHost = 'localhost';
$dbName = 'admingeez_db'; // Likely your database name
$dbUser = 'admingeez_user'; // Your database username
$dbPass = 'your_password_here'; // Your database password

echo "<p>Attempting to connect to database with:</p>";
echo "<ul>";
echo "<li>Host: $dbHost</li>";
echo "<li>Database: $dbName</li>";
echo "<li>Username: $dbUser</li>";
echo "<li>Password: [hidden]</li>";
echo "</ul>";

try {
    // Connect to the database directly
    $conn = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color:green'>Database connection successful!</p>";
    
    // Now try to query the cleaning_task table
    $stmt = $conn->query("SELECT COUNT(*) FROM cleaning_task WHERE location_id = 12");
    $taskCount = $stmt->fetchColumn();
    
    echo "<p>Found $taskCount tasks with location_id = 12</p>";
    
    // Try to insert a test record
    echo "<h3>Testing Log Insertion</h3>";
    
    $insertTest = $conn->prepare("
        INSERT INTO cleaning_log 
        (task_id, location_id, completed_date, completed_time, completed_by_user_id, 
         notes, is_verified, verified_by_user_id, verified_at, created_at, updated_at)
        VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    // Sample values
    $taskId = 1; // Make sure this exists in your cleaning_task table
    $locationId = 12; // Kitchen
    $completedDate = date('Y-m-d');
    $completedTime = date('H:i:s');
    $completedById = 1; // Make sure this user exists
    $notes = "Test entry";
    $isVerified = 1;
    $verifiedById = 1;
    $verifiedAt = date('Y-m-d H:i:s');
    $createdAt = date('Y-m-d H:i:s');
    $updatedAt = date('Y-m-d H:i:s');
    
    $insertTest->execute([
        $taskId, $locationId, $completedDate, $completedTime, $completedById,
        $notes, $isVerified, $verifiedById, $verifiedAt, $createdAt, $updatedAt
    ]);
    
    $lastId = $conn->lastInsertId();
    echo "<p style='color:green'>Test insertion successful! Inserted log_id: $lastId</p>";
    
    // Ask if user wants to generate all logs
    echo "<div style='margin: 20px 0; padding: 10px; background-color: #f0f0f0;'>";
    echo "<h3>Generate Complete Dataset</h3>";
    echo "<p>All connection tests passed! You can now generate cleaning logs from August 12, 2023 to today.</p>";
    echo "<form method='post'>";
    echo "<input type='hidden' name='confirm' value='yes'>";
    echo "<input type='submit' value='Generate All Cleaning Logs'>";
    echo "</form>";
    echo "</div>";
    
    // Clean up test entry if not continuing
    if (!isset($_POST['confirm'])) {
        $conn->exec("DELETE FROM cleaning_log WHERE log_id = $lastId");
        echo "<p>Test entry deleted</p>";
    }
    
    // Handle full generation
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        // Start and end dates
        $startDate = new DateTime('2023-08-12');
        $endDate = new DateTime(); // Today
        $currentDate = clone $startDate;
        
        echo "<h3>Generating logs from " . $startDate->format('Y-m-d') . " to " . $endDate->format('Y-m-d') . "</h3>";
        
        // Get tasks
        $taskStmt = $conn->prepare("SELECT task_id, description, frequency FROM cleaning_task WHERE location_id = 12 AND is_active = 1");
        $taskStmt->execute();
        $tasks = $taskStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get users
        $userStmt = $conn->prepare("SELECT user_id FROM users WHERE is_active = 1");
        $userStmt->execute();
        $users = $userStmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tasks) || empty($users)) {
            die("<p style='color:red'>Error: No active tasks or users found</p>");
        }
        
        echo "<p>Found " . count($tasks) . " tasks and " . count($users) . " users</p>";
        
        // Begin transaction
        $conn->beginTransaction();
        $totalInserted = 0;
        
        try {
            // Prepare statement
            $insertStmt = $conn->prepare("
                INSERT INTO cleaning_log 
                (task_id, location_id, completed_date, completed_time, completed_by_user_id, 
                 notes, is_verified, verified_by_user_id, verified_at, created_at, updated_at)
                VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            // Loop through dates
            while ($currentDate <= $endDate) {
                $dateString = $currentDate->format('Y-m-d');
                
                foreach ($tasks as $task) {
                    // Determine if task should be done based on frequency
                    $frequency = strtolower($task['frequency']);
                    $shouldComplete = false;
                    
                    if ($frequency === 'daily') {
                        $shouldComplete = true;
                    } elseif ($frequency === 'weekly') {
                        $shouldComplete = $currentDate->format('N') === '1'; // Monday
                    } elseif ($frequency === 'monthly') {
                        $shouldComplete = $currentDate->format('j') === '1'; // 1st of month
                    }
                    
                    if ($shouldComplete && rand(1, 100) <= 80) { // 80% chance of completion
                        // Generate random time (morning 6-10am or evening 7-11pm)
                        $hour = (rand(0, 1) === 0) ? rand(6, 10) : rand(19, 23);
                        $minute = rand(0, 59);
                        $completedTime = sprintf('%02d:%02d:00', $hour, $minute);
                        
                        // Randomly assign user
                        $completedById = $users[array_rand($users)];
                        
                        // 70% chance of verification
                        $isVerified = rand(1, 100) <= 70 ? 1 : 0;
                        $verifiedById = $isVerified ? $users[array_rand($users)] : null;
                        $verifiedAt = $isVerified ? $dateString . ' ' . sprintf('%02d:%02d:00', rand(6, 23), rand(0, 59)) : null;
                        
                        // Created and updated timestamps
                        $createdAt = $dateString . ' ' . $completedTime;
                        $updatedAt = $createdAt;
                        
                        // Insert record
                        $insertStmt->execute([
                            $task['task_id'], 12, $dateString, $completedTime, $completedById,
                            null, $isVerified, $verifiedById, $verifiedAt, $createdAt, $updatedAt
                        ]);
                        
                        $totalInserted++;
                        
                        // Output progress periodically
                        if ($totalInserted % 100 === 0) {
                            echo "<p>Inserted $totalInserted records so far...</p>";
                            flush();
                            ob_flush();
                        }
                    }
                }
                
                // Move to next day
                $currentDate->modify('+1 day');
            }
            
            // Commit the transaction
            $conn->commit();
            echo "<h2 style='color:green'>Success!</h2>";
            echo "<p>Successfully inserted $totalInserted cleaning log records.</p>";
            
        } catch (Exception $e) {
            $conn->rollBack();
            echo "<h2 style='color:red'>Error</h2>";
            echo "<p>Error during insertion: " . $e->getMessage() . "</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Database connection failed: " . $e->getMessage() . "</p>";
    
    // Provide some common solutions
    echo "<h3>Possible solutions:</h3>";
    echo "<ul>";
    echo "<li>Check that the database name, username and password are correct</li>";
    echo "<li>Make sure the database server is running</li>";
    echo "<li>Check if the user has permissions to access the database</li>";
    echo "<li>Verify that the database contains the expected tables</li>";
    echo "</ul>";
}
?> 