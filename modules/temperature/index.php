<?php
/**
 * Temperature Check Index Page
 * 
 * Overview page for temperature checks module
 */

// Set page title
$page_title = 'Temperature Checks';

// Include header
require_once dirname(dirname(dirname(__FILE__))) . '/includes/header.php';

// Require login
requireLogin();

// Initialize Equipment and TempCheck classes
require_once CLASS_PATH . '/Equipment.php';
require_once CLASS_PATH . '/TempCheck.php';
$equipment = new Equipment($db);
$temp_check = new TempCheck($db);

// Get all active equipment
$all_equipment = $equipment->getAllActive();

// Get recent temperature checks
$recent_checks = $temp_check->getAll(null, null, null);
$recent_checks = array_slice($recent_checks, 0, 10);

// Page actions
$page_actions = '
<a href="' . BASE_URL . '/modules/temperature/add.php" class="btn btn-primary">
    <i class="bi bi-plus-circle"></i> Add Temperature Check
</a>
<a href="' . BASE_URL . '/modules/temperature/report.php" class="btn btn-outline-secondary">
    <i class="bi bi-file-earmark-text"></i> Reports
</a>';
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Temperature Checks Overview</h5>
            </div>
            <div class="card-body">
                <p>This module allows you to record and monitor temperature checks for refrigeration equipment. Regular temperature monitoring is essential for food safety compliance.</p>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="d-grid gap-2">
                            <a href="<?php echo BASE_URL; ?>/modules/temperature/add.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-plus-circle"></i> Record New Temperature Check
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-grid gap-2">
                            <a href="<?php echo BASE_URL; ?>/modules/temperature/view.php" class="btn btn-secondary btn-lg">
                                <i class="bi bi-list"></i> View Temperature History
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
                <h5 class="card-title mb-0">Equipment List</h5>
            </div>
            <div class="card-body">
                <?php if (empty($all_equipment)): ?>
                <p class="text-muted">No equipment found. Please add equipment in the admin section.</p>
                <?php else: ?>
                <div class="list-group">
                    <?php foreach ($all_equipment as $item): ?>
                    <a href="<?php echo BASE_URL; ?>/modules/temperature/view.php?equipment_id=<?php echo $item['equipment_id']; ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                        </div>
                        <?php if (!empty($item['location'])): ?>
                        <small class="text-muted">Location: <?php echo htmlspecialchars($item['location']); ?></small>
                        <?php endif; ?>
                        <?php if (!empty($item['temp_range_target'])): ?>
                        <br><small class="text-muted">Target Range: <?php echo htmlspecialchars($item['temp_range_target']); ?></small>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <?php if (hasRole(['admin', 'manager'])): ?>
                <div class="mt-3">
                    <a href="<?php echo BASE_URL; ?>/admin/equipment.php" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-gear"></i> Manage Equipment
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Temperature Checks</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_checks)): ?>
                <p class="text-muted">No temperature checks found. Start by adding a new temperature check.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>Equipment</th>
                                <th>Temperature</th>
                                <th>Stock</th>
                                <th>Recorded By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_checks as $check): ?>
                            <tr>
                                <td><?php echo formatDateTime($check['check_timestamp'], 'd M Y H:i'); ?></td>
                                <td><?php echo htmlspecialchars($check['equipment_name']); ?></td>
                                <td><?php echo htmlspecialchars($check['temperature_reading']); ?></td>
                                <td><?php echo htmlspecialchars($check['stock_quantity_observed'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($check['recorded_by']); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/modules/temperature/view.php?id=<?php echo $check['check_id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <a href="<?php echo BASE_URL; ?>/modules/temperature/view.php" class="btn btn-outline-primary">
                        View All Temperature Checks <i class="bi bi-arrow-right"></i>
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
