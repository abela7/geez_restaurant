<?php
/**
 * Temperature Check Reports Page
 * 
 * Allows users to generate and view temperature check reports
 */

// Set page title
$page_title = 'Temperature Check Reports';

// Include header
require_once dirname(dirname(dirname(__FILE__))) . '/includes/header.php';

// Require login
requireLogin();

// Initialize Equipment and TempCheck classes
require_once CLASS_PATH . '/Equipment.php';
require_once CLASS_PATH . '/TempCheck.php';
$equipment = new Equipment($db);
$temp_check = new TempCheck($db);

// Get all active equipment for filter
$all_equipment = $equipment->getAllActive();

// Get filter parameters
$equipment_id = isset($_GET['equipment_id']) ? (int)$_GET['equipment_id'] : null;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'daily';

// Get temperature checks based on filters
$temperature_checks = $temp_check->getAll($start_date, $end_date, $equipment_id);

// Page actions
$page_actions = '
<a href="' . BASE_URL . '/modules/temperature/add.php" class="btn btn-primary">
    <i class="bi bi-plus-circle"></i> Add Temperature Check
</a>';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Generate Temperature Check Report</h5>
            </div>
            <div class="card-body">
                <form method="get" action="" class="row g-3">
                    <div class="col-md-3">
                        <label for="report_type" class="form-label">Report Type</label>
                        <select class="form-select" id="report_type" name="report_type">
                            <option value="daily" <?php echo $report_type == 'daily' ? 'selected' : ''; ?>>Daily Summary</option>
                            <option value="equipment" <?php echo $report_type == 'equipment' ? 'selected' : ''; ?>>Equipment Summary</option>
                            <option value="detailed" <?php echo $report_type == 'detailed' ? 'selected' : ''; ?>>Detailed List</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="equipment_id" class="form-label">Equipment</label>
                        <select class="form-select" id="equipment_id" name="equipment_id">
                            <option value="">All Equipment</option>
                            <?php foreach ($all_equipment as $item): ?>
                            <option value="<?php echo $item['equipment_id']; ?>" <?php echo $equipment_id == $item['equipment_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($item['name']); ?>
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
                    if ($report_type == 'daily') echo 'Daily Temperature Check Summary';
                    elseif ($report_type == 'equipment') echo 'Equipment Temperature Check Summary';
                    else echo 'Detailed Temperature Check Report';
                    ?>
                </h5>
                <div>
                    <a href="<?php echo BASE_URL; ?>/modules/temperature/print.php?report_type=<?php echo $report_type; ?>&equipment_id=<?php echo $equipment_id; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="bi bi-printer"></i> Print Report
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($temperature_checks)): ?>
                <p class="text-muted">No temperature checks found for the selected criteria.</p>
                <?php else: ?>
                
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
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Total Checks</th>
                                <th>Equipment Checked</th>
                                <th>Actions</th>
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
                                    echo count($equipment_names) . ' (' . implode(', ', array_slice($equipment_names, 0, 3)) . (count($equipment_names) > 3 ? '...' : '') . ')';
                                    ?>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/modules/temperature/view.php?start_date=<?php echo $date; ?>&end_date=<?php echo $date; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View Details
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
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
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Equipment</th>
                                <th>Total Checks</th>
                                <th>Last Check</th>
                                <th>Last Temperature</th>
                                <th>Actions</th>
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
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/modules/temperature/view.php?equipment_id=<?php echo $eq_id; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-sm btn-outline-primary">
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
