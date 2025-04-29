<?php
/**
 * Cleaning Reports Page
 * 
 * Allows users to generate and view cleaning log reports
 */

// Set page title
$page_title = 'Cleaning Reports';

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

// Get all active locations for filter
$all_locations = $location->getAllActive();

// Get filter parameters
$location_id = isset($_GET['location_id']) ? (int)$_GET['location_id'] : null;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'location';

// Get cleaning logs based on filters
$cleaning_logs = $cleaning_log->getAll($start_date, $end_date, $location_id);

// Page actions
$page_actions = '
<a href="' . BASE_URL . '/modules/cleaning/log.php" class="btn btn-primary">
    <i class="bi bi-journal-check"></i> Daily Cleaning Log
</a>';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Generate Cleaning Report</h5>
            </div>
            <div class="card-body">
                <form method="get" action="" class="row g-3">
                    <div class="col-md-3">
                        <label for="report_type" class="form-label">Report Type</label>
                        <select class="form-select" id="report_type" name="report_type">
                            <option value="location" <?php echo $report_type == 'location' ? 'selected' : ''; ?>>By Location</option>
                            <option value="date" <?php echo $report_type == 'date' ? 'selected' : ''; ?>>By Date</option>
                            <option value="task" <?php echo $report_type == 'task' ? 'selected' : ''; ?>>By Task</option>
                            <option value="detailed" <?php echo $report_type == 'detailed' ? 'selected' : ''; ?>>Detailed List</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="location_id" class="form-label">Location</label>
                        <select class="form-select" id="location_id" name="location_id">
                            <option value="">All Locations</option>
                            <?php foreach ($all_locations as $loc): ?>
                            <option value="<?php echo $loc['location_id']; ?>" <?php echo $location_id == $loc['location_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($loc['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-filter"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <?php 
                    if ($report_type == 'location') echo 'Cleaning Report by Location';
                    elseif ($report_type == 'date') echo 'Cleaning Report by Date';
                    elseif ($report_type == 'task') echo 'Cleaning Report by Task';
                    else echo 'Detailed Cleaning Report';
                    ?>
                </h5>
                <div>
                    <a href="<?php echo BASE_URL; ?>/modules/cleaning/print.php?report_type=<?php echo $report_type; ?>&location_id=<?php echo $location_id; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="bi bi-printer"></i> Print Report
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($cleaning_logs)): ?>
                <p class="text-muted">No cleaning logs found for the selected criteria.</p>
                <?php else: ?>
                
                <?php if ($report_type == 'location'): ?>
                <!-- Location Summary Report -->
                <?php
                // Group logs by location
                $location_logs = [];
                foreach ($cleaning_logs as $log) {
                    $loc_id = $log['location_id'];
                    if (!isset($location_logs[$loc_id])) {
                        $location_logs[$loc_id] = [
                            'name' => $log['name'] ?? 'Unknown Location',
                            'logs' => [],
                            'completed' => 0,
                            'total' => 0
                        ];
                    }
                    $location_logs[$loc_id]['logs'][] = $log;
                    $location_logs[$loc_id]['total']++;
                    if ($log['is_completed']) {
                        $location_logs[$loc_id]['completed']++;
                    }
                }
                ?>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>Total Tasks</th>
                                <th>Completed</th>
                                <th>Completion Rate</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($location_logs as $loc_id => $data): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($data['name']); ?></td>
                                <td><?php echo $data['total']; ?></td>
                                <td><?php echo $data['completed']; ?></td>
                                <td>
                                    <?php 
                                    $completion_rate = $data['total'] > 0 ? ($data['completed'] / $data['total'] * 100) : 0;
                                    echo number_format($completion_rate, 1) . '%';
                                    ?>
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $completion_rate; ?>%"></div>
                                    </div>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/modules/cleaning/report.php?report_type=detailed&location_id=<?php echo $loc_id; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View Details
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php elseif ($report_type == 'date'): ?>
                <!-- Date Summary Report -->
                <?php
                // Group logs by date
                $date_logs = [];
                foreach ($cleaning_logs as $log) {
                    $date = $log['completed_date'];
                    if (!isset($date_logs[$date])) {
                        $date_logs[$date] = [
                            'logs' => [],
                            'completed' => 0,
                            'total' => 0
                        ];
                    }
                    $date_logs[$date]['logs'][] = $log;
                    $date_logs[$date]['total']++;
                    if ($log['is_completed']) {
                        $date_logs[$date]['completed']++;
                    }
                }
                // Sort by date (newest first)
                krsort($date_logs);
                ?>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Total Tasks</th>
                                <th>Completed</th>
                                <th>Completion Rate</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($date_logs as $date => $data): ?>
                            <tr>
                                <td><?php echo formatDate($date, 'D, d M Y'); ?></td>
                                <td><?php echo $data['total']; ?></td>
                                <td><?php echo $data['completed']; ?></td>
                                <td>
                                    <?php 
                                    $completion_rate = $data['total'] > 0 ? ($data['completed'] / $data['total'] * 100) : 0;
                                    echo number_format($completion_rate, 1) . '%';
                                    ?>
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $completion_rate; ?>%"></div>
                                    </div>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/modules/cleaning/report.php?report_type=detailed&start_date=<?php echo $date; ?>&end_date=<?php echo $date; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View Details
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php elseif ($report_type == 'task'): ?>
                <!-- Task Summary Report -->
                <?php
                // Group logs by task
                $task_logs = [];
                foreach ($cleaning_logs as $log) {
                    $task_id = $log['task_id'];
                    $task_desc = $log['task_description'];
                    if (!isset($task_logs[$task_id])) {
                        $task_logs[$task_id] = [
                            'description' => $task_desc,
                            'logs' => [],
                            'completed' => 0,
                            'total' => 0
                        ];
                    }
                    $task_logs[$task_id]['logs'][] = $log;
                    $task_logs[$task_id]['total']++;
                    if ($log['is_completed']) {
                        $task_logs[$task_id]['completed']++;
                    }
                }
                // Sort by completion rate (lowest first)
                uasort($task_logs, function($a, $b) {
                    $rate_a = $a['total'] > 0 ? ($a['completed'] / $a['total']) : 0;
                    $rate_b = $b['total'] > 0 ? ($b['completed'] / $b['total']) : 0;
                    return $rate_a <=> $rate_b;
                });
                ?>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Total Instances</th>
                                <th>Completed</th>
                                <th>Completion Rate</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($task_logs as $task_id => $data): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($data['description']); ?></td>
                                <td><?php echo $data['total']; ?></td>
                                <td><?php echo $data['completed']; ?></td>
                                <td>
                                    <?php 
                                    $completion_rate = $data['total'] > 0 ? ($data['completed'] / $data['total'] * 100) : 0;
                                    echo number_format($completion_rate, 1) . '%';
                                    ?>
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $completion_rate; ?>%"></div>
                                    </div>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/modules/cleaning/report.php?report_type=detailed&task_id=<?php echo $task_id; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View Details
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php else: ?>
                <!-- Detailed Report -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Location</th>
                                <th>Task</th>
                                <th>Status</th>
                                <th>Notes</th>
                                <th>Recorded By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cleaning_logs as $log): ?>
                            <tr>
                                <td><?php echo !empty($log['completed_date']) ? formatDate($log['completed_date'], 'd M Y') : 'N/A'; ?></td>
                                <td><?php echo !empty($log['location_name']) ? htmlspecialchars($log['location_name']) : htmlspecialchars($log['name'] ?? 'Unknown Location'); ?></td>
                                <td><?php echo !empty($log['task_description']) ? htmlspecialchars($log['task_description']) : 'Unknown Task'; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $log['is_completed'] ? 'success' : 'danger'; ?>">
                                        <?php echo $log['is_completed'] ? 'Completed' : 'Incomplete'; ?>
                                    </span>
                                </td>
                                <td><?php echo !empty($log['notes']) ? htmlspecialchars($log['notes']) : ''; ?></td>
                                <td><?php echo !empty($log['recorded_by']) ? htmlspecialchars($log['recorded_by']) : 'Unknown'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once INCLUDE_PATH . '/footer.php';
?>
