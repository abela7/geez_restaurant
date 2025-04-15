<?php
/**
 * Admin Dashboard Page
 * 
 * Main admin dashboard for managing system settings
 */

// Set page title
$page_title = 'Admin Dashboard';

// Include header
require_once dirname(dirname(__FILE__)) . '/includes/header.php';

// Require admin role
requireRole(['admin']);

// Initialize classes
require_once CLASS_PATH . '/Equipment.php';
require_once CLASS_PATH . '/CleaningLocation.php';
require_once CLASS_PATH . '/CleaningTask.php';
require_once CLASS_PATH . '/User.php';
$equipment = new Equipment($db);
$location = new CleaningLocation($db);
$task = new CleaningTask($db);
$user_model = new User($db);

// Get counts
$equipment_count = count($equipment->getAll());
$location_count = count($location->getAll());
$task_count = count($task->getAll());
$user_count = count($user_model->getAll());
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Admin Dashboard</h5>
            </div>
            <div class="card-body">
                <p>Welcome to the admin dashboard. Here you can manage system settings, users, and configuration for the Geez Restaurant Food Hygiene & Safety Management System.</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="display-4"><?php echo $equipment_count; ?></h3>
                <p class="text-muted">Equipment Items</p>
                <a href="<?php echo BASE_URL; ?>/admin/equipment.php" class="btn btn-sm btn-primary">Manage Equipment</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="display-4"><?php echo $location_count; ?></h3>
                <p class="text-muted">Cleaning Locations</p>
                <a href="<?php echo BASE_URL; ?>/modules/cleaning/locations.php" class="btn btn-sm btn-primary">Manage Locations</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="display-4"><?php echo $task_count; ?></h3>
                <p class="text-muted">Cleaning Tasks</p>
                <a href="<?php echo BASE_URL; ?>/modules/cleaning/tasks.php" class="btn btn-sm btn-primary">Manage Tasks</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="display-4"><?php echo $user_count; ?></h3>
                <p class="text-muted">System Users</p>
                <a href="<?php echo BASE_URL; ?>/admin/users.php" class="btn btn-sm btn-primary">Manage Users</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Links</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="<?php echo BASE_URL; ?>/admin/equipment.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-thermometer-half me-2"></i> Manage Equipment
                    </a>
                    <a href="<?php echo BASE_URL; ?>/modules/cleaning/locations.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-geo-alt me-2"></i> Manage Cleaning Locations
                    </a>
                    <a href="<?php echo BASE_URL; ?>/modules/cleaning/tasks.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-check2-square me-2"></i> Manage Cleaning Tasks
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/users.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-people me-2"></i> Manage Users
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/settings.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-gear me-2"></i> System Settings
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/backup.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-cloud-download me-2"></i> Backup & Restore
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">System Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th>System Name:</th>
                            <td>Geez Restaurant Food Hygiene & Safety Management System</td>
                        </tr>
                        <tr>
                            <th>Version:</th>
                            <td>1.0.0</td>
                        </tr>
                        <tr>
                            <th>PHP Version:</th>
                            <td><?php echo phpversion(); ?></td>
                        </tr>
                        <tr>
                            <th>Database:</th>
                            <td>MySQL</td>
                        </tr>
                        <tr>
                            <th>Server:</th>
                            <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td>
                        </tr>
                        <tr>
                            <th>Current User:</th>
                            <td><?php echo htmlspecialchars($_SESSION['full_name']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once INCLUDE_PATH . '/footer.php';
?>
