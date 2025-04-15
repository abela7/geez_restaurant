<?php
/**
 * View Food Waste Logs Page
 * 
 * Allows users to view food waste log history
 */

// Set page title
$page_title = 'View Food Waste Logs';

// Include header
require_once dirname(dirname(dirname(__FILE__))) . '/includes/header.php';

// Require login
requireLogin();

// Initialize FoodWasteLog class
require_once CLASS_PATH . '/FoodWasteLog.php';
$waste_log = new FoodWasteLog($db);

// Get filter parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Get specific waste log if ID provided
$waste_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$waste_details = null;

if ($waste_id) {
    $waste_details = $waste_log->getById($waste_id);
    if ($waste_details) {
        $page_title = 'Food Waste Log Details';
    }
}

// Get waste logs based on filters
$waste_logs = $waste_id ? [] : $waste_log->getAll($start_date, $end_date);

// Page actions
$page_actions = '
<a href="' . BASE_URL . '/modules/waste/add.php" class="btn btn-primary">
    <i class="bi bi-plus-circle"></i> Record Food Waste
</a>';
?>

<?php if ($waste_details): ?>
<!-- Single Waste Log Details View -->
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0">Food Waste Log Details</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Item:</div>
                    <div class="col-md-8"><?php echo htmlspecialchars($waste_details['item_description']); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Date & Time:</div>
                    <div class="col-md-8"><?php echo formatDateTime($waste_details['waste_timestamp'], 'd M Y H:i'); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Quantity:</div>
                    <div class="col-md-8"><?php echo htmlspecialchars($waste_details['quantity'] . ' ' . $waste_details['unit_of_measure']); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Cost Per Unit:</div>
                    <div class="col-md-8">$<?php echo number_format($waste_details['cost_per_unit'], 2); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Total Cost:</div>
                    <div class="col-md-8">$<?php echo number_format($waste_details['total_cost'], 2); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Reason for Waste:</div>
                    <div class="col-md-8"><?php echo htmlspecialchars($waste_details['reason_for_waste']); ?></div>
                </div>
                <?php if (!empty($waste_details['notes'])): ?>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Notes:</div>
                    <div class="col-md-8"><?php echo nl2br(htmlspecialchars($waste_details['notes'])); ?></div>
                </div>
                <?php endif; ?>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Recorded By:</div>
                    <div class="col-md-8"><?php echo htmlspecialchars($waste_details['recorded_by']); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Recorded At:</div>
                    <div class="col-md-8"><?php echo formatDateTime($waste_details['recorded_at'], 'd M Y H:i:s'); ?></div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="<?php echo BASE_URL; ?>/modules/waste/view.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                    <a href="<?php echo BASE_URL; ?>/modules/waste/print.php?id=<?php echo $waste_details['waste_id']; ?>" class="btn btn-outline-primary" target="_blank">
                        <i class="bi bi-printer"></i> Print
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Food Waste Logs List View -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Filter Food Waste Logs</h5>
            </div>
            <div class="card-body">
                <form method="get" action="" class="row g-3">
                    <div class="col-md-5">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                    </div>
                    <div class="col-md-5">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-filter"></i> Filter
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
                <h5 class="card-title mb-0">Food Waste Log History</h5>
                <div>
                    <a href="<?php echo BASE_URL; ?>/modules/waste/print.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="bi bi-printer"></i> Print Report
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($waste_logs)): ?>
                <p class="text-muted">No food waste logs found for the selected criteria.</p>
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
                            <?php foreach ($waste_logs as $log): ?>
                            <tr>
                                <td><?php echo formatDateTime($log['waste_timestamp'], 'd M Y H:i'); ?></td>
                                <td><?php echo htmlspecialchars($log['item_description']); ?></td>
                                <td><?php echo htmlspecialchars($log['quantity'] . ' ' . $log['unit_of_measure']); ?></td>
                                <td>$<?php echo number_format($log['total_cost'], 2); ?></td>
                                <td><?php echo htmlspecialchars($log['reason_for_waste']); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/modules/waste/view.php?id=<?php echo $log['waste_id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Summary Section -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Summary</h6>
                            </div>
                            <div class="card-body">
                                <?php
                                $total_incidents = count($waste_logs);
                                $total_cost = 0;
                                foreach ($waste_logs as $log) {
                                    $total_cost += $log['total_cost'];
                                }
                                ?>
                                <p><strong>Total Incidents:</strong> <?php echo $total_incidents; ?></p>
                                <p><strong>Total Cost:</strong> $<?php echo number_format($total_cost, 2); ?></p>
                                <p><strong>Average Cost per Incident:</strong> $<?php echo $total_incidents > 0 ? number_format($total_cost / $total_incidents, 2) : '0.00'; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Top Reasons for Waste</h6>
                            </div>
                            <div class="card-body">
                                <?php
                                // Count reasons
                                $reasons = [];
                                foreach ($waste_logs as $log) {
                                    $reason = $log['reason_for_waste'];
                                    if (!isset($reasons[$reason])) {
                                        $reasons[$reason] = 0;
                                    }
                                    $reasons[$reason]++;
                                }
                                arsort($reasons);
                                $top_reasons = array_slice($reasons, 0, 3);
                                ?>
                                <ul>
                                    <?php foreach ($top_reasons as $reason => $count): ?>
                                    <li><strong><?php echo htmlspecialchars($reason); ?>:</strong> <?php echo $count; ?> incidents</li>
                                    <?php endforeach; ?>
                                </ul>
                                <a href="<?php echo BASE_URL; ?>/modules/waste/report.php" class="btn btn-sm btn-outline-primary">
                                    View Detailed Reports <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
// Include footer
require_once INCLUDE_PATH . '/footer.php';
?>
