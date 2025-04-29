<?php
/**
 * Print Food Waste Report
 * 
 * Generates printable reports for food waste logs
 */

// Set page title
$page_title = 'Print Food Waste Report';

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

// Initialize FoodWasteLog and CleaningLocation classes
require_once CLASS_PATH . '/FoodWasteLog.php';
require_once CLASS_PATH . '/CleaningLocation.php';
$waste_log = new FoodWasteLog($db);
$location = new CleaningLocation($db);

// Get parameters
$waste_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$location_id = isset($_GET['location_id']) ? (int)$_GET['location_id'] : null;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'detailed';

// Get data based on parameters
if ($waste_id) {
    // Single waste log details
    $waste_details = $waste_log->getById($waste_id);
    $report_title = 'Food Waste Log Details';
} else {
    // Multiple logs based on filters
    $waste_logs = $waste_log->getAll($start_date, $end_date, $location_id);
    
    if ($report_type == 'summary') {
        $report_title = 'Food Waste Summary Report';
    } elseif ($report_type == 'by_date') {
        $report_title = 'Food Waste by Date Report';
    } elseif ($report_type == 'by_item') {
        $report_title = 'Food Waste by Item Report';
    } elseif ($report_type == 'by_reason') {
        $report_title = 'Food Waste by Reason Report';
    } else {
        $report_title = 'Detailed Food Waste Report';
    }
    
    // Get location name if filtered by location
    $location_name = '';
    if ($location_id) {
        $location_data = $location->getById($location_id);
        if ($location_data) {
            $location_name = $location_data['name'];
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
        
        .summary-box {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }
        
        .summary-title {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 14pt;
        }
        
        .summary-value {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .summary-label {
            font-size: 10pt;
            color: #666;
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
            <?php if (!$waste_id): ?>
            <div class="report-date">
                Period: <?php echo formatDate($start_date, 'd M Y'); ?> to <?php echo formatDate($end_date, 'd M Y'); ?>
                <?php if ($location_name): ?>
                <br>Location: <?php echo htmlspecialchars($location_name); ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($waste_id && $waste_details): ?>
        <!-- Single Waste Log Details -->
        <table class="report-table">
            <tr>
                <th style="width: 30%;">Item:</th>
                <td><?php echo htmlspecialchars($waste_details['item_description']); ?></td>
            </tr>
            <tr>
                <th>Date & Time:</th>
                <td><?php echo formatDateTime($waste_details['waste_timestamp'], 'd M Y H:i'); ?></td>
            </tr>
            <tr>
                <th>Quantity:</th>
                <td><?php echo htmlspecialchars($waste_details['quantity'] . ' ' . $waste_details['unit_of_measure']); ?></td>
            </tr>
            <tr>
                <th>Cost Per Unit:</th>
                <td>£<?php echo number_format($waste_details['cost_per_unit'], 2); ?></td>
            </tr>
            <tr>
                <th>Total Cost:</th>
                <td>£<?php echo number_format($waste_details['total_cost'], 2); ?></td>
            </tr>
            <tr>
                <th>Reason for Waste:</th>
                <td><?php echo htmlspecialchars($waste_details['reason_for_waste']); ?></td>
            </tr>
            <?php if (!empty($waste_details['facility_location'])): ?>
            <tr>
                <th>Location:</th>
                <td><?php echo htmlspecialchars($waste_details['facility_location']); ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($waste_details['notes'])): ?>
            <tr>
                <th>Notes:</th>
                <td><?php echo nl2br(htmlspecialchars($waste_details['notes'])); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <th>Recorded By:</th>
                <td><?php echo htmlspecialchars($waste_details['recorded_by']); ?></td>
            </tr>
            <tr>
                <th>Recorded At:</th>
                <td><?php echo formatDateTime($waste_details['recorded_at'], 'd M Y H:i:s'); ?></td>
            </tr>
        </table>
        
        <?php elseif (!empty($waste_logs)): ?>
        
        <?php if ($report_type == 'summary'): ?>
        <!-- Summary Report -->
        <?php
        // Calculate summary statistics
        $total_incidents = count($waste_logs);
        $total_cost = 0;
        $total_quantity = 0;
        $reasons = [];
        $items = [];
        $dates = [];
        
        foreach ($waste_logs as $log) {
            $total_cost += $log['total_cost'];
            $total_quantity += $log['quantity'];
            
            // Count by reason
            $reason = $log['reason_for_waste'];
            if (!isset($reasons[$reason])) {
                $reasons[$reason] = ['count' => 0, 'cost' => 0];
            }
            $reasons[$reason]['count']++;
            $reasons[$reason]['cost'] += $log['total_cost'];
            
            // Count by item
            $item = $log['item_description'];
            if (!isset($items[$item])) {
                $items[$item] = ['count' => 0, 'cost' => 0, 'quantity' => 0];
            }
            $items[$item]['count']++;
            $items[$item]['cost'] += $log['total_cost'];
            $items[$item]['quantity'] += $log['quantity'];
            
            // Count by date
            $date = date('Y-m-d', strtotime($log['waste_timestamp']));
            if (!isset($dates[$date])) {
                $dates[$date] = ['count' => 0, 'cost' => 0];
            }
            $dates[$date]['count']++;
            $dates[$date]['cost'] += $log['total_cost'];
        }
        
        // Sort by cost (highest first)
        uasort($reasons, function($a, $b) {
            return $b['cost'] <=> $a['cost'];
        });
        
        uasort($items, function($a, $b) {
            return $b['cost'] <=> $a['cost'];
        });
        
        // Sort by date (newest first)
        krsort($dates);
        ?>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <div class="summary-box" style="width: 30%;">
                <div class="summary-title">Total Incidents</div>
                <div class="summary-value"><?php echo $total_incidents; ?></div>
                <div class="summary-label">Waste incidents recorded</div>
            </div>
            <div class="summary-box" style="width: 30%;">
                <div class="summary-title">Total Cost</div>
                <div class="summary-value">£<?php echo number_format($total_cost, 2); ?></div>
                <div class="summary-label">Value of wasted items</div>
            </div>
            <div class="summary-box" style="width: 30%;">
                <div class="summary-title">Average Cost</div>
                <div class="summary-value">£<?php echo $total_incidents > 0 ? number_format($total_cost / $total_incidents, 2) : '0.00'; ?></div>
                <div class="summary-label">Per incident</div>
            </div>
        </div>
        
        <h4>Top Reasons for Waste</h4>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Reason</th>
                    <th>Incidents</th>
                    <th>Total Cost</th>
                    <th>% of Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $top_reasons = array_slice($reasons, 0, 5);
                foreach ($top_reasons as $reason => $data): 
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($reason); ?></td>
                    <td><?php echo $data['count']; ?></td>
                    <td>£<?php echo number_format($data['cost'], 2); ?></td>
                    <td><?php echo number_format(($data['cost'] / $total_cost) * 100, 1); ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <h4>Top Wasted Items</h4>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Incidents</th>
                    <th>Total Cost</th>
                    <th>% of Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $top_items = array_slice($items, 0, 5);
                foreach ($top_items as $item => $data): 
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($item); ?></td>
                    <td><?php echo $data['count']; ?></td>
                    <td>£<?php echo number_format($data['cost'], 2); ?></td>
                    <td><?php echo number_format(($data['cost'] / $total_cost) * 100, 1); ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <h4>Recent Daily Waste Totals</h4>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Incidents</th>
                    <th>Total Cost</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $recent_dates = array_slice($dates, 0, 7);
                foreach ($recent_dates as $date => $data): 
                ?>
                <tr>
                    <td><?php echo formatDate($date, 'D, d M Y'); ?></td>
                    <td><?php echo $data['count']; ?></td>
                    <td>£<?php echo number_format($data['cost'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php elseif ($report_type == 'by_date'): ?>
        <!-- By Date Report -->
        <?php
        // Group logs by date
        $date_logs = [];
        foreach ($waste_logs as $log) {
            $date = date('Y-m-d', strtotime($log['waste_timestamp']));
            if (!isset($date_logs[$date])) {
                $date_logs[$date] = [
                    'count' => 0,
                    'cost' => 0,
                    'quantity' => 0
                ];
            }
            $date_logs[$date]['count']++;
            $date_logs[$date]['cost'] += $log['total_cost'];
            $date_logs[$date]['quantity'] += $log['quantity'];
        }
        // Sort by date (newest first)
        krsort($date_logs);
        ?>
        
        <table class="report-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Incidents</th>
                    <th>Total Cost</th>
                    <th>Average Cost</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($date_logs as $date => $data): ?>
                <tr>
                    <td><?php echo formatDate($date, 'D, d M Y'); ?></td>
                    <td><?php echo $data['count']; ?></td>
                    <td>£<?php echo number_format($data['cost'], 2); ?></td>
                    <td>£<?php echo number_format($data['cost'] / $data['count'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php elseif ($report_type == 'by_item'): ?>
        <!-- By Item Report -->
        <?php
        // Group logs by item
        $item_logs = [];
        foreach ($waste_logs as $log) {
            $item = $log['item_description'];
            if (!isset($item_logs[$item])) {
                $item_logs[$item] = [
                    'count' => 0,
                    'cost' => 0,
                    'quantity' => 0
                ];
            }
            $item_logs[$item]['count']++;
            $item_logs[$item]['cost'] += $log['total_cost'];
            $item_logs[$item]['quantity'] += $log['quantity'];
        }
        // Sort by cost (highest first)
        uasort($item_logs, function($a, $b) {
            return $b['cost'] <=> $a['cost'];
        });
        ?>
        
        <table class="report-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Incidents</th>
                    <th>Total Quantity</th>
                    <th>Total Cost</th>
                    <th>% of Total Cost</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_all_cost = array_sum(array_column($item_logs, 'cost'));
                foreach ($item_logs as $item => $data): 
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($item); ?></td>
                    <td><?php echo $data['count']; ?></td>
                    <td><?php echo number_format($data['quantity'], 2); ?></td>
                    <td>£<?php echo number_format($data['cost'], 2); ?></td>
                    <td><?php echo number_format(($data['cost'] / $total_all_cost) * 100, 1); ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php elseif ($report_type == 'by_reason'): ?>
        <!-- By Reason Report -->
        <?php
        // Group logs by reason
        $reason_logs = [];
        foreach ($waste_logs as $log) {
            $reason = $log['reason_for_waste'];
            if (!isset($reason_logs[$reason])) {
                $reason_logs[$reason] = [
                    'count' => 0,
                    'cost' => 0,
                    'quantity' => 0
                ];
            }
            $reason_logs[$reason]['count']++;
            $reason_logs[$reason]['cost'] += $log['total_cost'];
            $reason_logs[$reason]['quantity'] += $log['quantity'];
        }
        // Sort by cost (highest first)
        uasort($reason_logs, function($a, $b) {
            return $b['cost'] <=> $a['cost'];
        });
        ?>
        
        <table class="report-table">
            <thead>
                <tr>
                    <th>Reason</th>
                    <th>Incidents</th>
                    <th>Total Cost</th>
                    <th>Average Cost</th>
                    <th>% of Total Cost</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_all_cost = array_sum(array_column($reason_logs, 'cost'));
                foreach ($reason_logs as $reason => $data): 
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($reason); ?></td>
                    <td><?php echo $data['count']; ?></td>
                    <td>£<?php echo number_format($data['cost'], 2); ?></td>
                    <td>£<?php echo number_format($data['cost'] / $data['count'], 2); ?></td>
                    <td><?php echo number_format(($data['cost'] / $total_all_cost) * 100, 1); ?>%</td>
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
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Unit Cost</th>
                    <th>Total Cost</th>
                    <th>Reason</th>
                    <th>Location</th>
                    <th>Recorded By</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($waste_logs as $log): ?>
                <tr>
                    <td><?php echo formatDateTime($log['waste_timestamp'], 'd M Y H:i'); ?></td>
                    <td><?php echo htmlspecialchars($log['item_description']); ?></td>
                    <td><?php echo htmlspecialchars($log['quantity'] . ' ' . $log['unit_of_measure']); ?></td>
                    <td>£<?php echo number_format($log['cost_per_unit'], 2); ?></td>
                    <td>£<?php echo number_format($log['total_cost'], 2); ?></td>
                    <td><?php echo htmlspecialchars($log['reason_for_waste']); ?></td>
                    <td><?php echo htmlspecialchars($log['facility_location'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($log['recorded_by']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        
        <?php else: ?>
        <p>No food waste logs found for the selected criteria.</p>
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
