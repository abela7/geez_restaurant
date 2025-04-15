<?php
/**
 * Print Cleaning Log Report
 * 
 * Generates printable reports for cleaning logs
 */

// Set page title
$page_title = 'Print Cleaning Log Report';

// Include header (minimal version for printing)
require_once dirname(dirname(dirname(__FILE__))) . '/config/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/config/database.php';
require_once INCLUDE_PATH . '/functions.php';

// Require login
session_name(SESSION_NAME);
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/modules/auth/login.php');
    exit;
}

// Initialize database connection
require_once CLASS_PATH . '/Database.php';
$db = new Database();

// Initialize CleaningLocation, CleaningTask, and CleaningLog classes
require_once CLASS_PATH . '/CleaningLocation.php';
require_once CLASS_PATH . '/CleaningTask.php';
require_once CLASS_PATH . '/CleaningLog.php';
$location = new CleaningLocation($db);
$task = new CleaningTask($db);
$cleaning_log = new CleaningLog($db);

// Get parameters
$location_id = isset($_GET['location_id']) ? (int)$_GET['location_id'] : null;
$task_id = isset($_GET['task_id']) ? (int)$_GET['task_id'] : null;
$date = isset($_GET['date']) ? $_GET['date'] : null;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'detailed';

// Get data based on parameters
if ($date && $location_id) {
    // Single day cleaning log for a specific location
    $cleaning_logs = $cleaning_log->getByDateAndLocation($date, $location_id);
    $location_data = $location->getById($location_id);
    $report_title = 'Daily Cleaning Log';
    $report_subtitle = $location_data ? $location_data['name'] : 'Unknown Location';
    $report_date = formatDate($date, 'l, d M Y');
} else {
    // Multiple logs based on filters
    $cleaning_logs = $cleaning_log->getAll($start_date, $end_date, $location_id);
    
    if ($report_type == 'location') {
        $report_title = 'Cleaning Report by Location';
    } elseif ($report_type == 'date') {
        $report_title = 'Cleaning Report by Date';
    } elseif ($report_type == 'task') {
        $report_title = 'Cleaning Report by Task';
    } else {
        $report_title = 'Detailed Cleaning Report';
    }
    
    $report_subtitle = '';
    $report_date = 'Period: ' . formatDate($start_date, 'd M Y') . ' to ' . formatDate($end_date, 'd M Y');
    
    // Get location name if filtered by location
    if ($location_id) {
        $location_data = $location->getById($location_id);
        if ($location_data) {
            $report_subtitle = $location_data['name'];
        }
    }
    
    // Get task description if filtered by task
    if ($task_id) {
        $task_data = $task->getById($task_id);
        if ($task_data) {
            $report_subtitle = $task_data['description'];
        }
    }
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
    
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        
        .report-header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .report-title {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .report-subtitle {
            font-size: 14pt;
            margin-bottom: 5px;
        }
        
        .report-date {
            font-size: 12pt;
            margin-bottom: 20px;
        }
        
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .report-table th, .report-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .report-table th {
            background-color: #f2f2f2;
        }
        
        .status-completed {
            color: green;
            font-weight: bold;
        }
        
        .status-incomplete {
            color: red;
        }
        
        .report-footer {
            margin-top: 30px;
            font-size: 10pt;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #000;
            width: 200px;
            display: inline-block;
            text-align: center;
            margin-right: 50px;
        }
        
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="no-print mb-3">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Print Report
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                <i class="bi bi-x"></i> Close
            </button>
        </div>
        
        <div class="report-header">
            <div class="report-title">Geez Restaurant</div>
            <div class="report-subtitle"><?php echo $report_title; ?></div>
            <?php if (!empty($report_subtitle)): ?>
            <div class="report-subtitle"><?php echo htmlspecialchars($report_subtitle); ?></div>
            <?php endif; ?>
            <div class="report-date"><?php echo $report_date; ?></div>
        </div>
        
        <?php if ($date && $location_id): ?>
        <!-- Daily Cleaning Log for a Location -->
        <div class="table-responsive">
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width: 60%;">Task</th>
                        <th style="width: 20%;">Status</th>
                        <th style="width: 20%;">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cleaning_logs)): ?>
                    <tr>
                        <td colspan="3" class="text-center">No cleaning tasks recorded for this date and location.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($cleaning_logs as $log): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($log['task_description']); ?></td>
                        <td class="<?php echo $log['is_completed'] ? 'status-completed' : 'status-incomplete'; ?>">
                            <?php echo $log['is_completed'] ? 'COMPLETED' : 'NOT COMPLETED'; ?>
                        </td>
                        <td><?php echo !empty($log['notes']) ? htmlspecialchars($log['notes']) : ''; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div style="display: flex; justify-content: space-between; margin-top: 50px;">
            <div>
                <div class="signature-line"></div>
                <p>Manager Signature</p>
            </div>
            <div>
                <div class="signature-line"></div>
                <p>Staff Signature</p>
            </div>
        </div>
        
        <?php elseif (!empty($cleaning_logs)): ?>
        
        <?php if ($report_type == 'location'): ?>
        <!-- Location Summary Report -->
        <?php
        // Group logs by location
        $location_logs = [];
        foreach ($cleaning_logs as $log) {
            $loc_id = $log['location_id'];
            if (!isset($location_logs[$loc_id])) {
                $location_logs[$loc_id] = [
                    'name' => $log['location_name'],
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
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Location</th>
                        <th>Total Tasks</th>
                        <th>Completed</th>
                        <th>Completion Rate</th>
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
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Total Tasks</th>
                        <th>Completed</th>
                        <th>Completion Rate</th>
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
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Total Instances</th>
                        <th>Completed</th>
                        <th>Completion Rate</th>
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
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php else: ?>
        <!-- Detailed Report -->
        <div class="table-responsive">
            <table class="report-table">
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
                        <td><?php echo formatDate($log['completed_date'], 'd M Y'); ?></td>
                        <td><?php echo htmlspecialchars($log['location_name']); ?></td>
                        <td><?php echo htmlspecialchars($log['task_description']); ?></td>
                        <td class="<?php echo $log['is_completed'] ? 'status-completed' : 'status-incomplete'; ?>">
                            <?php echo $log['is_completed'] ? 'COMPLETED' : 'NOT COMPLETED'; ?>
                        </td>
                        <td><?php echo !empty($log['notes']) ? htmlspecialchars($log['notes']) : ''; ?></td>
                        <td><?php echo htmlspecialchars($log['recorded_by']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <p>No cleaning logs found for the selected criteria.</p>
        <?php endif; ?>
        
        <div class="report-footer">
            <p>Generated on <?php echo date('d M Y H:i:s'); ?> by <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
            <p>Geez Restaurant Food Hygiene & Safety Management System</p>
        </div>
    </div>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            // Slight delay to ensure everything is loaded
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
