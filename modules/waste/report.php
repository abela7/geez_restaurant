<?php
/**
 * Food Waste Reports Page
 * 
 * Allows users to generate and view food waste reports
 */

// Set page title
$page_title = 'Food Waste Reports';

// Include header
require_once dirname(dirname(dirname(__FILE__))) . '/includes/header.php';

// Require login
requireLogin();

// Initialize FoodWasteLog and CleaningLocation classes
require_once CLASS_PATH . '/FoodWasteLog.php';
require_once CLASS_PATH . '/CleaningLocation.php';
$waste_log = new FoodWasteLog($db);
$location = new CleaningLocation($db);

// Get all active locations for filter
$all_locations = $location->getAllActive();

// Get filter parameters
$location_id = isset($_GET['location_id']) ? (int)$_GET['location_id'] : null;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'summary';

// Get waste logs based on filters
$waste_logs = $waste_log->getAll($start_date, $end_date, $location_id);

// Page actions
$page_actions = '
<a href="' . BASE_URL . '/modules/waste/add.php" class="btn btn-primary">
    <i class="bi bi-plus-circle"></i> Record Food Waste
</a>';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Generate Food Waste Report</h5>
            </div>
            <div class="card-body">
                <form method="get" action="" class="row g-3">
                    <div class="col-md-3">
                        <label for="report_type" class="form-label">Report Type</label>
                        <select class="form-select" id="report_type" name="report_type">
                            <option value="summary" <?php echo $report_type == 'summary' ? 'selected' : ''; ?>>Summary Report</option>
                            <option value="by_date" <?php echo $report_type == 'by_date' ? 'selected' : ''; ?>>By Date</option>
                            <option value="by_item" <?php echo $report_type == 'by_item' ? 'selected' : ''; ?>>By Item</option>
                            <option value="by_reason" <?php echo $report_type == 'by_reason' ? 'selected' : ''; ?>>By Reason</option>
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
                    if ($report_type == 'summary') echo 'Food Waste Summary Report';
                    elseif ($report_type == 'by_date') echo 'Food Waste by Date Report';
                    elseif ($report_type == 'by_item') echo 'Food Waste by Item Report';
                    elseif ($report_type == 'by_reason') echo 'Food Waste by Reason Report';
                    else echo 'Detailed Food Waste Report';
                    ?>
                </h5>
                <div>
                    <a href="<?php echo BASE_URL; ?>/modules/waste/print.php?report_type=<?php echo $report_type; ?>&location_id=<?php echo $location_id; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="bi bi-printer"></i> Print Report
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($waste_logs)): ?>
                <p class="text-muted">No food waste logs found for the selected criteria.</p>
                <?php else: ?>
                
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
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h3 class="display-4"><?php echo $total_incidents; ?></h3>
                                <p class="text-muted">Total Waste Incidents</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h3 class="display-4">$<?php echo number_format($total_cost, 2); ?></h3>
                                <p class="text-muted">Total Waste Cost</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h3 class="display-4">$<?php echo $total_incidents > 0 ? number_format($total_cost / $total_incidents, 2) : '0.00'; ?></h3>
                                <p class="text-muted">Average Cost per Incident</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <h5>Top Reasons for Waste</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
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
                                        <td>$<?php echo number_format($data['cost'], 2); ?></td>
                                        <td><?php echo number_format(($data['cost'] / $total_cost) * 100, 1); ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <h5>Top Wasted Items</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
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
                                        <td>$<?php echo number_format($data['cost'], 2); ?></td>
                                        <td><?php echo number_format(($data['cost'] / $total_cost) * 100, 1); ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <h5>Recent Daily Waste Totals</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
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
                                        <td>$<?php echo number_format($data['cost'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
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
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Incidents</th>
                                <th>Total Cost</th>
                                <th>Average Cost</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($date_logs as $date => $data): ?>
                            <tr>
                                <td><?php echo formatDate($date, 'D, d M Y'); ?></td>
                                <td><?php echo $data['count']; ?></td>
                                <td>$<?php echo number_format($data['cost'], 2); ?></td>
                                <td>$<?php echo number_format($data['cost'] / $data['count'], 2); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/modules/waste/view.php?start_date=<?php echo $date; ?>&end_date=<?php echo $date; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View Details
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
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
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
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
                                <td>$<?php echo number_format($data['cost'], 2); ?></td>
                                <td><?php echo number_format(($data['cost'] / $total_all_cost) * 100, 1); ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
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
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
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
                                <td>$<?php echo number_format($data['cost'], 2); ?></td>
                                <td>$<?php echo number_format($data['cost'] / $data['count'], 2); ?></td>
                                <td><?php echo number_format(($data['cost'] / $total_all_cost) * 100, 1); ?>%</td>
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
                                <td>$<?php echo number_format($log['cost_per_unit'], 2); ?></td>
                                <td>$<?php echo number_format($log['total_cost'], 2); ?></td>
                                <td><?php echo htmlspecialchars($log['reason_for_waste']); ?></td>
                                <td><?php echo htmlspecialchars($log['facility_location'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($log['recorded_by']); ?></td>
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
