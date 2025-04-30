<?php
/**
 * Printable Weekly Cleaning Checklist
 */

// Include configuration and common functions
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/database.php';
require_once INCLUDE_PATH . '/functions.php';

// Set timezone (Consider making this a config setting later)
date_default_timezone_set('Europe/London');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

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

// Require login
requireLogin();
// Optionally require specific roles if needed
// requireRole(['manager', 'admin']); 

// --- Get Parameters --- 
$location_id = isset($_GET['location_id']) ? (int)$_GET['location_id'] : null;
// Default to current week if not specified
$week_str = isset($_GET['week']) ? $_GET['week'] : date('Y-\\WW'); // Format YYYY-Www
$approver_id = isset($_GET['approver_id']) ? (int)$_GET['approver_id'] : null;

// --- Data Fetching & Preparation ---
$all_locations = $location_model->getAllActive();
$managers_admins = $user_model->getByRoles(['manager', 'admin']); // Assuming such a method exists or needs creation

$selected_location = null;
$approver_details = null;
$tasks = [];
$log_data = [];
$week_start_date = null;
$week_end_date = null;
$week_dates = [];

// Calculate week start/end dates from $week_str (YYYY-Www)
if (preg_match('/^(\d{4})-W(\d{2})$/', $week_str, $matches)) {
    $year = (int)$matches[1];
    $week_num = (int)$matches[2];
    $date = new DateTime();
    $date->setISODate($year, $week_num, 1); // 1 = Monday
    $week_start_date = $date->format('Y-m-d');
    $week_dates['Mon'] = $date->format('Y-m-d');
    $date->modify('+1 day'); $week_dates['Tue'] = $date->format('Y-m-d');
    $date->modify('+1 day'); $week_dates['Wed'] = $date->format('Y-m-d');
    $date->modify('+1 day'); $week_dates['Thu'] = $date->format('Y-m-d');
    $date->modify('+1 day'); $week_dates['Fri'] = $date->format('Y-m-d');
    $date->modify('+1 day'); $week_dates['Sat'] = $date->format('Y-m-d');
    $date->modify('+1 day'); $week_dates['Sun'] = $date->format('Y-m-d');
    $week_end_date = $date->format('Y-m-d');
} else {
    // Handle invalid week format - maybe default to current week?
    $week_str = date('Y-\\WW'); // Reset to current week on error
    // Recalculate dates if needed, similar logic as above
}

if ($location_id) {
    $selected_location = $location_model->getById($location_id);
    if ($selected_location && $week_start_date && $week_end_date) {
        // Fetch tasks for the location (assuming tasks are linked to locations or fetch all)
        // This might need adjustment based on actual schema
        $tasks = $task_model->getByLocation($location_id); // ASSUMING getByLocation exists
        if (!$tasks) {
            // If no tasks linked to location, maybe fetch all tasks?
            // $tasks = $task_model->getAllActive(); // Alternative
        }
        
        // Fetch log entries for this location and week
        $logs = $log_model->getAll($week_start_date, $week_end_date, $location_id);
        
        // Process logs into a lookup array: $log_data[task_id][date] = user_id
        foreach ($logs as $log) {
            if (!isset($log_data[$log['task_id']])) {
                $log_data[$log['task_id']] = [];
            }
            $log_data[$log['task_id']][$log['completed_date']] = $log['completed_by_user_id'];
        }
    } else {
        $location_id = null; // Reset if location not found
    }
}

if ($approver_id) {
    $approver_details = $user_model->getById($approver_id); // Assuming User model has getById
}

// Function to get user initials (copy from temp checklist or redefine)
// Ensure this function exists and works with your User/Database classes
function getCleaningUserInitials($user_id, $db) {
    if (!$user_id) return ''; // Return empty for blank cells
    $user_data = $db->fetchRow("SELECT full_name FROM users WHERE user_id = ?", [$user_id]);
    if ($user_data && isset($user_data['full_name']) && !empty($user_data['full_name'])) {
        $parts = explode(' ', trim($user_data['full_name']));
        $initials = '';
        if (isset($parts[0][0])) $initials .= strtoupper($parts[0][0]);
        if (count($parts) > 1 && isset($parts[count($parts)-1][0])) $initials .= strtoupper($parts[count($parts)-1][0]);
        return $initials ?: 'N/A'; // Or just $initials
    }
    return 'N/A'; // Fallback
}

// --- Page Setup --- 
$page_title = 'Weekly Cleaning Checklist';
if ($selected_location) {
    $page_title .= ' - ' . htmlspecialchars($selected_location['name']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS (includes print styles) -->
    <link rel="stylesheet" href="<?php echo ASSET_URL; ?>/css/custom.css">
    
    <style>
        /* Print Styles */
        @media print {
            body { 
                font-size: 9pt; 
                -webkit-print-color-adjust: exact !important; 
                color-adjust: exact !important; 
            }
            @page { margin: 0.5in; }
            .table td, .table th { padding: 0.2rem 0.4rem; vertical-align: middle; }
            .table thead th { background-color: #e9ecef !important; font-weight: bold; text-align: center; }
            .task-col { width: 40%; text-align: left !important; } /* Wider task column, left aligned */
            .day-col { width: 6%; text-align: center; } /* Narrower day columns */
            .no-col { width: 4%; text-align: right; padding-right: 5px !important; } /* Number column */
            .initials-col { width: 10%; text-align: center; } /* If we add a separate initials column */
            h1, h2, h3, h4, h5, h6 { margin-top: 0; margin-bottom: 0.5rem; }
            .container { max-width: 100% !important; width: 100% !important; padding: 0 !important; margin: 0 !important; }
            a[href]:after { content: none !important; }
            .printable-header td { border: none; padding: 2px 5px; font-size: 10pt; }
            
            /* Hide everything except the checklist */
            .no-print, nav, header, footer, .navbar, #sidebar, .selection-form { 
                display: none !important; 
            }
            
            /* Remove extra margins/padding around the printable area */
            .printable-area {
                margin: 0 !important;
                padding: 0 !important;
            }
            
            /* Ensure the checklist starts at the top of the page */
            .container.mt-4 {
                margin-top: 0 !important;
            }
        }
        /* Screen Styles */
        .checklist-table { margin-top: 20px; }
        .checklist-table thead th { text-align: center; vertical-align: middle; }
        .checklist-table tbody td { text-align: center; vertical-align: middle; }
        .task-col { text-align: left !important; }
        .no-col { text-align: right; padding-right: 5px !important; }
        .header-details-table td { border: none; padding: 5px; }
        .signature-line { border-bottom: 1px solid #000; display: inline-block; min-width: 150px; margin-left: 5px; }
    </style>
</head>
<body>

<div class="container mt-4">

    <!-- Selection Form (No Print) -->
    <div class="no-print mb-4 p-3 border rounded bg-light selection-form">
        <h4>Generate Weekly Cleaning Checklist</h4>
        <form method="get" action="" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="location_id" class="form-label">Select Location:</label>
                <select class="form-select" id="location_id" name="location_id" required>
                    <option value="">-- Select Location --</option>
                    <?php foreach ($all_locations as $loc): ?>
                    <option value="<?php echo $loc['location_id']; ?>" <?php echo ($location_id == $loc['location_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($loc['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="week" class="form-label">Select Week:</label>
                <input type="week" class="form-control" id="week" name="week" value="<?php echo $week_str; ?>" required>
            </div>
            <div class="col-md-3">
                <label for="approver_id" class="form-label">Approved By:</label>
                <select class="form-select" id="approver_id" name="approver_id">
                    <option value="">-- Select User --</option>
                     <?php foreach ($managers_admins as $admin): ?>
                    <option value="<?php echo $admin['user_id']; ?>" <?php echo ($approver_id == $admin['user_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($admin['full_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Generate</button>
            </div>
        </form>
        <?php if ($selected_location && $week_start_date): ?>
        <button onclick="window.print()" class="btn btn-success mt-3"><i class="bi bi-printer"></i> Print Checklist</button>
        <?php endif; ?>
    </div>

    <!-- Printable Area -->
    <?php if (!$location_id || !$selected_location || !$week_start_date): ?>
        <div class="alert alert-info no-print">Please select location and week to generate the checklist.</div>
    <?php else: ?>
        
        <div class="printable-area">
            <div class="printable-header mb-3">
                <h3 class="text-center">Daily & Weekly Cleaning Checklist</h3>
                <table class="header-details-table w-100">
                    <tr>
                        <td><strong>Location:</strong> <?php echo htmlspecialchars($selected_location['name']); ?></td>
                        <td><strong>Week Start:</strong> <?php echo formatDate($week_start_date, 'd/m/Y'); ?></td>
                        <td><strong>Approved By:</strong> <?php echo $approver_details ? htmlspecialchars($approver_details['full_name']) : '.......................'; ?></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><strong>Week End:</strong> <?php echo formatDate($week_end_date, 'd/m/Y'); ?></td>
                        <td><strong>Signature:</strong> <span class="signature-line"></span></td>
                    </tr>
                </table>
            </div>

            <table class="table table-bordered table-sm checklist-table">
                <thead>
                    <tr>
                        <th class="no-col">No</th>
                        <th class="task-col">Task</th>
                        <th class="day-col">M</th>
                        <th class="day-col">T</th>
                        <th class="day-col">W</th>
                        <th class="day-col">T</th>
                        <th class="day-col">F</th>
                        <th class="day-col">S</th>
                        <th class="day-col">S</th>
                        <?php /* <th class="initials-col">Cleaned By</th> */ ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tasks)): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted">No cleaning tasks found for this location.</td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $task_num = 1;
                        $dailyTasks = [];
                        $weeklyTasks = [];
                        $monthlyTasks = [];
                        
                        // Sort tasks by frequency
                        foreach ($tasks as $task) {
                            $frequency = strtolower($task['frequency']);
                            if ($frequency === 'daily') {
                                $dailyTasks[] = $task;
                            } elseif ($frequency === 'weekly') {
                                $weeklyTasks[] = $task;
                            } elseif ($frequency === 'monthly') {
                                $monthlyTasks[] = $task;
                            }
                        }
                        
                        // First display daily tasks
                        foreach ($dailyTasks as $task): 
                        ?>
                        <tr>
                            <td class="no-col"><?php echo $task_num++; ?></td>
                            <td class="task-col"><?php echo htmlspecialchars($task['description']); ?></td>
                            <?php foreach ($week_dates as $day => $date): ?>
                                <td class="day-col">
                                    <?php 
                                    $user_id_completed = $log_data[$task['task_id']][$date] ?? null;
                                    echo $user_id_completed ? getCleaningUserInitials($user_id_completed, $db) : '';
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                        
                        <!-- Weekly Tasks Section -->
                        <tr>
                            <td colspan="9" class="bg-light text-center fw-bold">Weekly Tasks</td>
                        </tr>
                        <?php foreach ($weeklyTasks as $task): ?>
                        <tr>
                            <td class="no-col"><?php echo $task_num++; ?></td>
                            <td class="task-col"><?php echo htmlspecialchars($task['description']); ?></td>
                            <?php foreach ($week_dates as $day => $date): ?>
                                <td class="day-col">
                                    <?php 
                                    $user_id_completed = $log_data[$task['task_id']][$date] ?? null;
                                    echo $user_id_completed ? getCleaningUserInitials($user_id_completed, $db) : '';
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                        
                        <!-- Monthly Tasks Section -->
                        <tr>
                            <td colspan="9" class="bg-light text-center fw-bold">Monthly Tasks</td>
                        </tr>
                        <?php foreach ($monthlyTasks as $task): ?>
                        <tr>
                            <td class="no-col"><?php echo $task_num++; ?></td>
                            <td class="task-col"><?php echo htmlspecialchars($task['description']); ?></td>
                            <?php foreach ($week_dates as $day => $date): ?>
                                <td class="day-col">
                                    <?php 
                                    $user_id_completed = $log_data[$task['task_id']][$date] ?? null;
                                    echo $user_id_completed ? getCleaningUserInitials($user_id_completed, $db) : '';
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php 
                     // Add blank rows if needed?
                     $rowCount = count($tasks);
                     $blankRows = max(0, 5 - $rowCount); // Add a few blank rows for notes?
                     for ($i = 0; $i < $blankRows; $i++):
                    ?>
                     <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                         <?php /* <td>&nbsp;</td> */ ?>
                     </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
            
            <!-- This note won't be printed due to no-print class -->
            <div class="mt-4 no-print">
                 <p><strong>Note:</strong> Initials in the date columns indicate completion by that user.</p>
            </div>
        </div> <!-- end of printable-area -->

    <?php endif; // End if location and week selected ?>

</div> <!-- /container -->

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html> 