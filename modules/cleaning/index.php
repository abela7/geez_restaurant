<?php
/**
 * Cleaning Log Index Page
 * 
 * Overview page for cleaning logs module
 */

// Set page title
$page_title = 'Cleaning Logs';

// Include header
require_once dirname(dirname(dirname(__FILE__))) . '/includes/header.php';

// Require login
requireLogin();

// Initialize CleaningLocation and CleaningTask classes
require_once CLASS_PATH . '/CleaningLocation.php';
require_once CLASS_PATH . '/CleaningTask.php';
require_once CLASS_PATH . '/CleaningLog.php';
$location = new CleaningLocation($db);
$task = new CleaningTask($db);
$cleaning_log = new CleaningLog($db);

// Get all active locations
$all_locations = $location->getAllActive();

// Get current date
$current_date = getCurrentDate();

// Get recent cleaning logs
$recent_logs = $cleaning_log->getAll(date('Y-m-d', strtotime('-7 days')), $current_date);
$recent_logs = array_slice($recent_logs, 0, 10);

// Page actions
$page_actions = '
<a href="' . BASE_URL . '/modules/cleaning/log.php" class="btn btn-primary">
    <i class="bi bi-journal-check"></i> Daily Cleaning Log
</a>
<a href="' . BASE_URL . '/modules/cleaning/report.php" class="btn btn-outline-secondary">
    <i class="bi bi-file-earmark-text"></i> Reports
</a>';
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Cleaning Logs Overview</h5>
            </div>
            <div class="card-body">
                <p>This module allows you to record and track the completion of cleaning tasks across different locations. Regular cleaning is essential for maintaining food safety standards.</p>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="d-grid gap-2">
                            <a href="<?php echo BASE_URL; ?>/modules/cleaning/log.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-journal-check"></i> Complete Today's Cleaning Log
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-grid gap-2">
                            <a href="<?php echo BASE_URL; ?>/modules/cleaning/tasks.php?action=add" class="btn btn-success btn-lg">
                                <i class="bi bi-plus-circle"></i> Add New Cleaning Task
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
                <h5 class="card-title mb-0">Cleaning Locations</h5>
            </div>
            <div class="card-body">
                <?php if (empty($all_locations)): ?>
                <p class="text-muted">No locations found. Please add locations in the admin section.</p>
                <?php else: ?>
                <div class="list-group">
                    <?php foreach ($all_locations as $loc): ?>
                    <a href="<?php echo BASE_URL; ?>/modules/cleaning/log.php?location_id=<?php echo $loc['location_id']; ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?php echo htmlspecialchars($loc['name']); ?></h6>
                        </div>
                        <?php if (!empty($loc['establishment'])): ?>
                        <small class="text-muted">Establishment: <?php echo htmlspecialchars($loc['establishment']); ?></small>
                        <?php endif; ?>
                        <?php if (!empty($loc['building'])): ?>
                        <br><small class="text-muted">Building: <?php echo htmlspecialchars($loc['building']); ?></small>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <?php if (hasRole(['admin', 'manager'])): ?>
                <div class="mt-3">
                    <a href="<?php echo BASE_URL; ?>/modules/cleaning/locations.php" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-gear"></i> Manage Locations
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Cleaning Logs</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_logs)): ?>
                <p class="text-muted">No cleaning logs found. Start by completing today's cleaning log.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Location</th>
                                <th>Task</th>
                                <th>Status</th>
                                <th>Recorded By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_logs as $log): ?>
                            <tr>
                                <td><?php echo formatDate($log['completed_date'], 'd M Y'); ?></td>
                                <td><?php echo htmlspecialchars($log['name']); ?></td>
                                <td><?php echo htmlspecialchars($log['task_description']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $log['is_completed'] ? 'success' : 'danger'; ?>">
                                        <?php echo $log['is_completed'] ? 'Completed' : 'Incomplete'; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($log['recorded_by']); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/modules/cleaning/log.php?date=<?php echo $log['completed_date']; ?>&location_id=<?php echo $log['location_id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <a href="<?php echo BASE_URL; ?>/modules/cleaning/report.php" class="btn btn-outline-primary">
                        View All Cleaning Logs <i class="bi bi-arrow-right"></i>
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
