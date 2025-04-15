<?php
/**
 * Dashboard Page
 * 
 * Main dashboard for the Geez Restaurant application
 */

// Include configuration first
require_once dirname(__FILE__) . '/config/config.php';
require_once dirname(__FILE__) . '/config/database.php';
require_once INCLUDE_PATH . '/functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Initialize database connection
require_once CLASS_PATH . '/Database.php';
$db = new Database();

// Initialize user
require_once CLASS_PATH . '/User.php';
$user = new User($db);

// Set page title
$page_title = 'Dashboard';

// Require login
requireLogin();

// Get current date
$current_date = getCurrentDate();

// Get recent temperature checks (last 5)
$recent_temp_checks = $db->fetchAll(
    "SELECT tc.check_id, tc.temperature, tc.is_compliant, tc.check_date, tc.check_time, 
    CONCAT(tc.check_date, ' ', tc.check_time) as check_timestamp,
    e.equipment_id, e.name as equipment_name
    FROM temperature_checks tc
    JOIN equipment e ON tc.equipment_id = e.equipment_id
    ORDER BY tc.check_date DESC, tc.check_time DESC
    LIMIT 5"
);

// Get recent cleaning logs (last 5)
$recent_cleaning = $db->fetchAll(
    "SELECT cl.*, ct.description as task_description, cloc.name as location_name 
     FROM cleaning_log cl 
     JOIN cleaning_task ct ON cl.task_id = ct.task_id 
     JOIN cleaning_locations cloc ON cl.location_id = cloc.location_id 
     ORDER BY cl.completed_date DESC, cl.completed_time DESC LIMIT 5"
);

// Get recent food waste logs (last 5)
$recent_waste = $db->fetchAll(
    "SELECT fw.*, u.full_name as recorded_by 
     FROM food_waste_log fw 
     JOIN users u ON fw.recorded_by_user_id = u.user_id 
     ORDER BY fw.waste_date DESC LIMIT 5"
);

// Include header after all checks and queries
require_once INCLUDE_PATH . '/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="bi bi-thermometer-half"></i> Temperature Checks</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2 mb-3">
                    <a href="<?php echo BASE_URL; ?>/modules/temperature/add.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add Temperature Check
                    </a>
                </div>
                
                <h6>Recent Checks</h6>
                <?php if (empty($recent_temp_checks)): ?>
                <p class="text-muted">No recent temperature checks found.</p>
                <?php else: ?>
                <div class="list-group">
                    <?php foreach ($recent_temp_checks as $check): ?>
                    <a href="<?php echo BASE_URL; ?>/modules/temperature/view.php?id=<?php echo $check['check_id']; ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?php echo htmlspecialchars($check['equipment_name']); ?></h6>
                            <small><?php echo formatDateTime($check['check_timestamp'], 'd M H:i'); ?></small>
                        </div>
                        <p class="mb-1">
                            Temp: <?php echo htmlspecialchars($check['temperature']); ?>°C
                            <span class="badge bg-<?php echo isset($check['is_compliant']) && $check['is_compliant'] ? 'success' : 'danger'; ?>">
                                <?php echo isset($check['is_compliant']) && $check['is_compliant'] ? 'Compliant' : 'Non-Compliant'; ?>
                            </span>
                        </p>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div class="mt-3">
                    <a href="<?php echo BASE_URL; ?>/modules/temperature/view.php" class="btn btn-sm btn-outline-primary">
                        View All <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0"><i class="bi bi-check2-square"></i> Cleaning Tasks</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2 mb-3">
                    <a href="<?php echo BASE_URL; ?>/modules/cleaning/log.php" class="btn btn-success">
                        <i class="bi bi-journal-check"></i> Daily Cleaning Log
                    </a>
                </div>
                
                <h6>Recent Cleaning Logs</h6>
                <?php if (empty($recent_cleaning)): ?>
                <p class="text-muted">No recent cleaning logs found.</p>
                <?php else: ?>
                <div class="list-group">
                    <?php foreach ($recent_cleaning as $log): ?>
                    <a href="<?php echo BASE_URL; ?>/modules/cleaning/log.php?id=<?php echo $log['log_id']; ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?php echo htmlspecialchars($log['location_name']); ?></h6>
                            <small>
                                <?php
                                $date = new DateTime($log['completed_date']);
                                echo $date->format('M d, Y');
                                
                                if (!empty($log['completed_time'])) {
                                    echo ' at ' . date('g:i A', strtotime($log['completed_time']));
                                }
                                ?>
                            </small>
                        </div>
                        <p class="mb-1"><?php echo htmlspecialchars($log['task_description']); ?></p>
                        <?php if (isset($log['notes']) && !empty($log['notes'])): ?>
                        <small class="text-muted"><?php echo htmlspecialchars($log['notes']); ?></small>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div class="mt-3">
                    <a href="<?php echo BASE_URL; ?>/modules/cleaning/report.php" class="btn btn-sm btn-outline-success">
                        View All <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0"><i class="bi bi-trash"></i> Food Waste</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2 mb-3">
                    <a href="<?php echo BASE_URL; ?>/modules/waste/add.php" class="btn btn-danger">
                        <i class="bi bi-plus-circle"></i> Log Food Waste
                    </a>
                </div>
                
                <h6>Recent Waste Logs</h6>
                <?php if (empty($recent_waste)): ?>
                <p class="text-muted">No recent food waste logs found.</p>
                <?php else: ?>
                <div class="list-group">
                    <?php foreach ($recent_waste as $waste): ?>
                    <a href="<?php echo BASE_URL; ?>/modules/waste/view.php?id=<?php echo $waste['waste_id']; ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?php echo htmlspecialchars($waste['food_item']); ?></h6>
                            <small><?php echo formatDate($waste['waste_date'] ?? '', 'M d, Y'); ?></small>
                        </div>
                        <p class="mb-1">
                            <?php echo htmlspecialchars($waste['weight_kg']); ?> kg 
                            (<?php echo htmlspecialchars($waste['waste_type']); ?>) - 
                            Cost: <?php echo formatCurrency($waste['cost']); ?>
                        </p>
                        <small>
                            <?php if (!empty($waste['reason'])): ?>
                                Reason: <?php echo htmlspecialchars($waste['reason']); ?><br>
                            <?php endif; ?>
                            Recorded by: <?php echo htmlspecialchars($waste['recorded_by']); ?>
                        </small>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div class="mt-3">
                    <a href="<?php echo BASE_URL; ?>/modules/waste/view.php" class="btn btn-sm btn-outline-danger">
                        View All <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-calendar3"></i> Today's Overview</h5>
            </div>
            <div class="card-body">
                <h6>Date: <?php echo formatDate($current_date ?? '', 'l, F j, Y'); ?></h6>
                
                <div class="mt-3">
                    <h6>Quick Links</h6>
                    <div class="list-group">
                        <a href="<?php echo BASE_URL; ?>/modules/temperature/add.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-thermometer-half text-primary"></i> Record Temperature Check
                        </a>
                        <a href="<?php echo BASE_URL; ?>/modules/cleaning/log.php?date=<?php echo $current_date; ?>" class="list-group-item list-group-item-action">
                            <i class="bi bi-check2-square text-success"></i> Today's Cleaning Log
                        </a>
                        <a href="<?php echo BASE_URL; ?>/modules/waste/add.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-trash text-danger"></i> Log Food Waste
                        </a>
                        <?php if (hasRole(['manager', 'admin'])): ?>
                        <a href="<?php echo BASE_URL; ?>/modules/temperature/report.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-file-earmark-text text-info"></i> Generate Reports
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once INCLUDE_PATH . '/footer.php';
?>
