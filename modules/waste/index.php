<?php
/**
 * Food Waste Log Index Page
 * 
 * Overview page for food waste logs module
 */

// Set page title
$page_title = 'Food Waste Log';

// Include header
require_once dirname(dirname(dirname(__FILE__))) . '/includes/header.php';

// Require login
requireLogin();

// Initialize FoodWasteLog and CleaningLocation classes
require_once CLASS_PATH . '/FoodWasteLog.php';
require_once CLASS_PATH . '/CleaningLocation.php';
$waste_log = new FoodWasteLog($db);
$location = new CleaningLocation($db);

// Get all active locations
$all_locations = $location->getAllActive();

// Get current date
$current_date = getCurrentDate();

// Get recent waste logs
$recent_logs = $waste_log->getAll(date('Y-m-d', strtotime('-30 days')), $current_date);
$recent_logs = array_slice($recent_logs, 0, 10);

// Page actions
$page_actions = '
<a href="' . BASE_URL . '/modules/waste/add.php" class="btn btn-primary">
    <i class="bi bi-plus-circle"></i> Record Food Waste
</a>
<a href="' . BASE_URL . '/modules/waste/report.php" class="btn btn-outline-secondary">
    <i class="bi bi-file-earmark-text"></i> Reports
</a>';
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Food Waste Log Overview</h5>
            </div>
            <div class="card-body">
                <p>This module allows you to record and track food waste incidents. Monitoring food waste helps identify patterns, reduce costs, and improve sustainability.</p>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="d-grid gap-2">
                            <a href="<?php echo BASE_URL; ?>/modules/waste/add.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-plus-circle"></i> Record New Food Waste
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-grid gap-2">
                            <a href="<?php echo BASE_URL; ?>/modules/waste/report.php" class="btn btn-secondary btn-lg">
                                <i class="bi bi-file-earmark-text"></i> View Waste Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Stats</h5>
            </div>
            <div class="card-body">
                <?php
                // Calculate quick stats
                $today_logs = $waste_log->getAll($current_date, $current_date);
                $today_count = count($today_logs);
                $today_cost = 0;
                foreach ($today_logs as $log) {
                    $today_cost += $log['total_cost'];
                }
                
                $week_logs = $waste_log->getAll(date('Y-m-d', strtotime('-7 days')), $current_date);
                $week_count = count($week_logs);
                $week_cost = 0;
                foreach ($week_logs as $log) {
                    $week_cost += $log['total_cost'];
                }
                
                $month_logs = $waste_log->getAll(date('Y-m-d', strtotime('-30 days')), $current_date);
                $month_count = count($month_logs);
                $month_cost = 0;
                foreach ($month_logs as $log) {
                    $month_cost += $log['total_cost'];
                }
                ?>
                
                <div class="row g-0 mb-3">
                    <div class="col-6 border-end">
                        <div class="p-3 text-center">
                            <h5><?php echo $today_count; ?></h5>
                            <p class="text-muted mb-0">Today's Incidents</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 text-center">
                            <h5>£<?php echo number_format($today_cost, 2); ?></h5>
                            <p class="text-muted mb-0">Today's Cost</p>
                        </div>
                    </div>
                </div>
                
                <div class="row g-0 mb-3">
                    <div class="col-6 border-end">
                        <div class="p-3 text-center">
                            <h5><?php echo $week_count; ?></h5>
                            <p class="text-muted mb-0">This Week</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 text-center">
                            <h5>£<?php echo number_format($week_cost, 2); ?></h5>
                            <p class="text-muted mb-0">Weekly Cost</p>
                        </div>
                    </div>
                </div>
                
                <div class="row g-0">
                    <div class="col-6 border-end">
                        <div class="p-3 text-center">
                            <h5><?php echo $month_count; ?></h5>
                            <p class="text-muted mb-0">This Month</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 text-center">
                            <h5>£<?php echo number_format($month_cost, 2); ?></h5>
                            <p class="text-muted mb-0">Monthly Cost</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3 text-center">
                    <a href="<?php echo BASE_URL; ?>/modules/waste/report.php" class="btn btn-sm btn-outline-primary">
                        View Detailed Reports <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Food Waste Logs</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_logs)): ?>
                <p class="text-muted">No food waste logs found. Start by recording a new food waste incident.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Cost</th>
                                <th>Reason</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_logs as $log): ?>
                            <tr>
                                <td><?php echo formatDateTime($log['waste_timestamp'], 'd M Y H:i'); ?></td>
                                <td><?php echo htmlspecialchars($log['item_description']); ?></td>
                                <td><?php echo htmlspecialchars($log['quantity'] . ' ' . $log['unit_of_measure']); ?></td>
                                <td>£<?php echo number_format($log['total_cost'], 2); ?></td>
                                <td><?php echo htmlspecialchars($log['reason_for_waste']); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/modules/waste/view.php?id=<?php echo $log['waste_id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <a href="<?php echo BASE_URL; ?>/modules/waste/view.php" class="btn btn-outline-primary">
                        View All Waste Logs <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once INCLUDE_PATH . '/footer.php';
?>
