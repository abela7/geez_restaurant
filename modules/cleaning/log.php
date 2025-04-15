<?php
/**
 * Daily Cleaning Log Page
 * 
 * Allows users to record cleaning task completion
 */

// Set page title
$page_title = 'Daily Cleaning Log';

// Include header
require_once dirname(dirname(dirname(__FILE__))) . '/includes/header.php';

// Require login
requireLogin();

// Initialize CleaningLocation, CleaningTask, and CleaningLog classes
require_once CLASS_PATH . '/CleaningLocation.php';
require_once CLASS_PATH . '/CleaningTask.php';
require_once CLASS_PATH . '/CleaningLog.php';
$location = new CleaningLocation($db);
$task = new CleaningTask($db);
$cleaning_log = new CleaningLog($db);

// Get all active locations
$all_locations = $location->getAllActive();

// Get parameters
$location_id = isset($_GET['location_id']) ? (int)$_GET['location_id'] : (isset($_POST['location_id']) ? (int)$_POST['location_id'] : null);
$completed_date = isset($_GET['date']) ? $_GET['date'] : (isset($_POST['date']) ? $_POST['date'] : getCurrentDate());

// Validate date
if (!isValidDate($completed_date)) {
    $completed_date = getCurrentDate();
}

// If no location selected and locations exist, use the first one
if (!$location_id && !empty($all_locations)) {
    $location_id = $all_locations[0]['location_id'];
}

// Get selected location details
$selected_location = null;
if ($location_id) {
    $selected_location = $location->getById($location_id);
}

// Get all active tasks
$all_tasks = $task->getAllActive();

// Get existing logs for this location and date
$existing_logs = [];
if ($location_id) {
    $existing_logs = $cleaning_log->getByDateAndLocation($completed_date, $location_id);
}

// Process form submission
$success = false;
$error = '';

if (isPostRequest()) {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        $task_statuses = $_POST['task_status'] ?? [];
        $task_notes = $_POST['task_notes'] ?? [];
        $user_id = $user->getCurrentUserId();
        
        try {
            // Start transaction
            $db->beginTransaction();
            
            // Process each task
            foreach ($task_statuses as $task_id => $status) {
                $is_completed = ($status == 1);
                $notes = $task_notes[$task_id] ?? null;
                
                // Check if log entry exists
                $log_entry = null;
                foreach ($existing_logs as $log) {
                    if ($log['task_id'] == $task_id) {
                        $log_entry = $log;
                        break;
                    }
                }
                
                if ($log_entry) {
                    // Update existing log
                    $cleaning_log->toggleCompletion($log_entry['log_id'], $is_completed, $notes, $user_id);
                } else {
                    // Create new log entry
                    $data = [
                        'location_id' => $location_id,
                        'task_id' => $task_id,
                        'completed_date' => $completed_date,
                        'is_completed' => $is_completed ? 1 : 0,
                        'completed_by_user_id' => $user_id,
                        'notes' => $notes
                    ];
                    $cleaning_log->create($data);
                }
            }
            
            // Commit transaction
            $db->commit();
            
            $success = true;
            setFlashMessage('Cleaning log updated successfully.', 'success');
            
            // Refresh existing logs
            $existing_logs = $cleaning_log->getByDateAndLocation($completed_date, $location_id);
            
        } catch (Exception $e) {
            // Rollback transaction
            $db->rollback();
            $error = 'Failed to update cleaning log: ' . $e->getMessage();
        }
    }
}

// Page actions
$page_actions = '
<a href="' . BASE_URL . '/modules/cleaning/report.php" class="btn btn-outline-secondary">
    <i class="bi bi-file-earmark-text"></i> Reports
</a>';

// Group tasks by frequency
$daily_tasks = [];
$weekly_tasks = [];
$other_tasks = [];

foreach ($all_tasks as $t) {
    if ($t['frequency'] == 'Daily') {
        $daily_tasks[] = $t;
    } elseif ($t['frequency'] == 'Weekly') {
        $weekly_tasks[] = $t;
    } else {
        $other_tasks[] = $t;
    }
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Select Location and Date</h5>
            </div>
            <div class="card-body">
                <form method="get" action="" class="row g-3">
                    <div class="col-md-6">
                        <label for="location_id" class="form-label">Location</label>
                        <select class="form-select" id="location_id" name="location_id">
                            <?php foreach ($all_locations as $loc): ?>
                            <option value="<?php echo $loc['location_id']; ?>" <?php echo $location_id == $loc['location_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($loc['name']); ?>
                                <?php if (!empty($loc['establishment'])): ?>
                                (<?php echo htmlspecialchars($loc['establishment']); ?>)
                                <?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" value="<?php echo $completed_date; ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-filter"></i> Select
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if ($selected_location): ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    Cleaning Log: <?php echo htmlspecialchars($selected_location['name']); ?> - <?php echo formatDate($completed_date, 'l, d M Y'); ?>
                </h5>
                <div>
                    <a href="<?php echo BASE_URL; ?>/modules/cleaning/print.php?location_id=<?php echo $location_id; ?>&date=<?php echo $completed_date; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="bi bi-printer"></i> Print Log
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    Cleaning log updated successfully.
                </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <?php if (empty($all_tasks)): ?>
                <p class="text-muted">No cleaning tasks found. Please add tasks in the admin section.</p>
                <?php if (hasRole(['admin', 'manager'])): ?>
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>/modules/cleaning/tasks.php" class="btn btn-primary">
                        <i class="bi bi-gear"></i> Manage Tasks
                    </a>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <form method="post" action="" id="cleaning-log-form">
                    <?php echo getCsrfTokenField(); ?>
                    <input type="hidden" name="location_id" value="<?php echo $location_id; ?>">
                    <input type="hidden" name="date" value="<?php echo $completed_date; ?>">
                    
                    <?php if (!empty($daily_tasks)): ?>
                    <h5 class="mb-3">Daily Tasks</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 50%;">Task</th>
                                    <th style="width: 20%;">Status</th>
                                    <th style="width: 30%;">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($daily_tasks as $t): ?>
                                <?php
                                // Find existing log for this task
                                $log_entry = null;
                                foreach ($existing_logs as $log) {
                                    if ($log['task_id'] == $t['task_id']) {
                                        $log_entry = $log;
                                        break;
                                    }
                                }
                                $is_completed = isset($log_entry['is_completed']) ? $log_entry['is_completed'] : false;
                                $notes = $log_entry ? $log_entry['notes'] : '';
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($t['description']); ?></td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" 
                                                id="task_<?php echo $t['task_id']; ?>" 
                                                name="task_status[<?php echo $t['task_id']; ?>]" 
                                                value="1" 
                                                <?php echo $is_completed ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="task_<?php echo $t['task_id']; ?>">
                                                <?php echo $is_completed ? 'Completed' : 'Mark as completed'; ?>
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" 
                                            name="task_notes[<?php echo $t['task_id']; ?>]" 
                                            value="<?php echo htmlspecialchars($notes ?? ''); ?>" 
                                            placeholder="Optional notes">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($weekly_tasks)): ?>
                    <h5 class="mb-3">Weekly Tasks</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 50%;">Task</th>
                                    <th style="width: 20%;">Status</th>
                                    <th style="width: 30%;">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($weekly_tasks as $t): ?>
                                <?php
                                // Find existing log for this task
                                $log_entry = null;
                                foreach ($existing_logs as $log) {
                                    if ($log['task_id'] == $t['task_id']) {
                                        $log_entry = $log;
                                        break;
                                    }
                                }
                                $is_completed = isset($log_entry['is_completed']) ? $log_entry['is_completed'] : false;
                                $notes = $log_entry ? $log_entry['notes'] : '';
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($t['description']); ?></td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" 
                                                id="task_<?php echo $t['task_id']; ?>" 
                                                name="task_status[<?php echo $t['task_id']; ?>]" 
                                                value="1" 
                                                <?php echo $is_completed ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="task_<?php echo $t['task_id']; ?>">
                                                <?php echo $is_completed ? 'Completed' : 'Mark as completed'; ?>
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" 
                                            name="task_notes[<?php echo $t['task_id']; ?>]" 
                                            value="<?php echo htmlspecialchars($notes ?? ''); ?>" 
                                            placeholder="Optional notes">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($other_tasks)): ?>
                    <h5 class="mb-3">Other Tasks</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 50%;">Task</th>
                                    <th style="width: 20%;">Status</th>
                                    <th style="width: 30%;">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($other_tasks as $t): ?>
                                <?php
                                // Find existing log for this task
                                $log_entry = null;
                                foreach ($existing_logs as $log) {
                                    if ($log['task_id'] == $t['task_id']) {
                                        $log_entry = $log;
                                        break;
                                    }
                                }
                                $is_completed = isset($log_entry['is_completed']) ? $log_entry['is_completed'] : false;
                                $notes = $log_entry ? $log_entry['notes'] : '';
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($t['description']); ?></td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" 
                                                id="task_<?php echo $t['task_id']; ?>" 
                                                name="task_status[<?php echo $t['task_id']; ?>]" 
                                                value="1" 
                                                <?php echo $is_completed ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="task_<?php echo $t['task_id']; ?>">
                                                <?php echo $is_completed ? 'Completed' : 'Mark as completed'; ?>
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" 
                                            name="task_notes[<?php echo $t['task_id']; ?>]" 
                                            value="<?php echo htmlspecialchars($notes ?? ''); ?>" 
                                            placeholder="Optional notes">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Cleaning Log
                        </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="alert alert-warning" role="alert">
    No cleaning locations found. Please add locations in the admin section before recording cleaning logs.
</div>
<?php if (hasRole(['admin', 'manager'])): ?>
<div class="d-grid gap-2">
    <a href="<?php echo BASE_URL; ?>/modules/cleaning/locations.php" class="btn btn-primary">
        <i class="bi bi-gear"></i> Manage Locations
    </a>
</div>
<?php endif; ?>
<?php endif; ?>

<?php
// Include footer
require_once INCLUDE_PATH . '/footer.php';
?>
