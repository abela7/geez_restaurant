<?php
/**
 * Print Temperature Check Report
 * 
 * Generates printable reports for temperature checks
 */

// Set page title
$page_title = 'Print Temperature Check Report';

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

// Initialize Equipment and TempCheck classes
require_once CLASS_PATH . '/Equipment.php';
require_once CLASS_PATH . '/TempCheck.php';
$equipment = new Equipment($db);
$temp_check = new TempCheck($db);

// Get parameters
$check_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$equipment_id = isset($_GET['equipment_id']) ? (int)$_GET['equipment_id'] : null;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'detailed';

// Get data based on parameters
if ($check_id) {
    // Single check details
    $check_details = $temp_check->getById($check_id);
    $report_title = 'Temperature Check Details';
} else {
    // Multiple checks based on filters
    $temperature_checks = $temp_check->getAll($start_date, $end_date, $equipment_id);
    
    if ($report_type == 'daily') {
        $report_title = 'Daily Temperature Check Summary';
    } elseif ($report_type == 'equipment') {
        $report_title = 'Equipment Temperature Check Summary';
    } else {
        $report_title = 'Detailed Temperature Check Report';
    }
    
    // Get equipment name if filtered by equipment
    $equipment_name = '';
    if ($equipment_id) {
        $equipment_data = $equipment->getById($equipment_id);
        if ($equipment_data) {
            $equipment_name = $equipment_data['name'];
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
        
        .report-footer {
            margin-top: 30px;
            font-size: 10pt;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            
            .no-print {
                display: none;
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
            <?php if (!$check_id): ?>
            <div class="report-date">
                Period: <?php echo formatDate($start_date, 'd M Y'); ?> to <?php echo formatDate($end_date, 'd M Y'); ?>
                <?php if ($equipment_name): ?>
                <br>Equipment: <?php echo htmlspecialchars($equipment_name); ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($check_id && $check_details): ?>
        <!-- Single Check Details -->
        <table class="report-table">
            <tr>
                <th style="width: 30%;">Equipment:</th>
                <td><?php echo htmlspecialchars($check_details['equipment_name']); ?></td>
            </tr>
            <tr>
                <th>Date & Time:</th>
                <td><?php echo formatDateTime($check_details['check_timestamp'], 'd M Y H:i'); ?></td>
            </tr>
            <tr>
                <th>Temperature Reading:</th>
                <td><?php echo htmlspecialchars($check_details['temperature_reading']); ?></td>
            </tr>
            <?php if (!empty($check_details['stock_quantity_observed'])): ?>
            <tr>
                <th>Stock Quantity:</th>
                <td><?php echo htmlspecialchars($check_details['stock_quantity_observed']); ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($check_details['notes'])): ?>
            <tr>
                <th>Notes:</th>
                <td><?php echo nl2br(htmlspecialchars($check_details['notes'])); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <th>Recorded By:</th>
                <td><?php echo htmlspecialchars($check_details['recorded_by']); ?></td>
            </tr>
            <tr>
                <th>Recorded At:</th>
                <td><?php echo formatDateTime($check_details['recorded_at'], 'd M Y H:i:s'); ?></td>
            </tr>
        </table>
        
        <?php elseif (!empty($temperature_checks)): ?>
        
        <?php if ($report_type == 'daily'): ?>
        <!-- Daily Summary Report -->
        <?php
        // Group checks by date
        $daily_checks = [];
        foreach ($temperature_checks as $check) {
            $date = date('Y-m-d', strtotime($check['check_timestamp']));
            if (!isset($daily_checks[$date])) {
                $daily_checks[$date] = [];
            }
            $daily_checks[$date][] = $check;
        }
        ?>
        
        <table class="report-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Total Checks</th>
                    <th>Equipment Checked</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daily_checks as $date => $checks): ?>
                <tr>
                    <td><?php echo formatDate($date, 'D, d M Y'); ?></td>
                    <td><?php echo count($checks); ?></td>
                    <td>
                        <?php 
                        $equipment_names = array_unique(array_column($checks, 'equipment_name'));
                        echo implode(', ', $equipment_names);
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php elseif ($report_type == 'equipment'): ?>
        <!-- Equipment Summary Report -->
        <?php
        // Group checks by equipment
        $equipment_checks = [];
        foreach ($temperature_checks as $check) {
            $eq_id = $check['equipment_id'];
            if (!isset($equipment_checks[$eq_id])) {
                $equipment_checks[$eq_id] = [
                    'name' => $check['equipment_name'],
                    'checks' => []
                ];
            }
            $equipment_checks[$eq_id]['checks'][] = $check;
        }
        ?>
        
        <table class="report-table">
            <thead>
                <tr>
                    <th>Equipment</th>
                    <th>Total Checks</th>
                    <th>Last Check</th>
                    <th>Last Temperature</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($equipment_checks as $eq_id => $data): ?>
                <?php 
                $last_check = $data['checks'][0]; // Checks are already sorted by date desc
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($data['name']); ?></td>
                    <td><?php echo count($data['checks']); ?></td>
                    <td><?php echo formatDateTime($last_check['check_timestamp'], 'd M Y H:i'); ?></td>
                    <td><?php echo htmlspecialchars($last_check['temperature_reading']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php else: ?>
        <!-- Detailed Report -->
        <table class="report-table">
            <thead>
                <tr>
                    <th>Date/Time</th>
                    <th>Equipment</th>
                    <th>Temperature</th>
                    <th>Stock</th>
                    <th>Recorded By</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($temperature_checks as $check): ?>
                <tr>
                    <td><?php echo formatDateTime($check['check_timestamp'], 'd M Y H:i'); ?></td>
                    <td><?php echo htmlspecialchars($check['equipment_name']); ?></td>
                    <td><?php echo htmlspecialchars($check['temperature_reading']); ?></td>
                    <td><?php echo htmlspecialchars($check['stock_quantity_observed'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($check['recorded_by']); ?></td>
                    <td><?php echo !empty($check['notes']) ? htmlspecialchars(substr($check['notes'], 0, 30)) . (strlen($check['notes']) > 30 ? '...' : '') : ''; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        
        <?php else: ?>
        <p>No temperature checks found for the selected criteria.</p>
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
