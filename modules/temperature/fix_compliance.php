<?php
/**
 * Fix Temperature Compliance Status
 * 
 * This script updates the compliance status of all temperature check records
 * to ensure they properly reflect the equipment's min/max temperature range.
 */

// Include configuration and common functions
require_once dirname(dirname(dirname(__FILE__))) . '/config/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/config/database.php';
require_once INCLUDE_PATH . '/functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Initialize database connection
require_once CLASS_PATH . '/Database.php';
$db = new Database();

// Require login and admin privileges
require_once CLASS_PATH . '/User.php';
$user = new User($db);
requireLogin();
requireRole(['admin', 'manager']);

// Initialize Equipment and TempCheck classes
require_once CLASS_PATH . '/Equipment.php';
require_once CLASS_PATH . '/TempCheck.php';
$equipment = new Equipment($db);
$temp_check = new TempCheck($db);

// Set page title
$page_title = 'Fix Compliance Status';

// Include header
require_once INCLUDE_PATH . '/header.php';

// Get all temperature checks that need to be fixed
$sql = "SELECT tc.check_id, tc.temperature, tc.is_compliant, 
               e.min_temp, e.max_temp, e.name as equipment_name
        FROM temperature_checks tc
        JOIN equipment e ON tc.equipment_id = e.equipment_id";
$checks = $db->fetchAll($sql);

$updated_count = 0;
$already_correct = 0;
$processed = 0;

// Check if form was submitted to confirm update
if (isPostRequest() && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    foreach ($checks as $check) {
        $processed++;
        $temp_value = floatval($check['temperature']);
        $min_value = floatval($check['min_temp']);
        $max_value = floatval($check['max_temp']);
        
        // Calculate correct compliance status
        $should_be_compliant = ($temp_value >= $min_value && $temp_value <= $max_value) ? 1 : 0;
        
        // Determine corrective action if not compliant
        $corrective_action = '';
        if (!$should_be_compliant) {
            if ($temp_value < $min_value) {
                $corrective_action = 'Temperature too low. Check equipment settings and functioning.';
            } else if ($temp_value > $max_value) {
                $corrective_action = 'Temperature too high. Check equipment settings and functioning.';
            }
        }
        
        // Check if the current status is incorrect
        if ($check['is_compliant'] != $should_be_compliant) {
            // Update the record
            $update_sql = "UPDATE temperature_checks 
                           SET is_compliant = ?, corrective_action = ?
                           WHERE check_id = ?";
            $db->execute($update_sql, [$should_be_compliant, $corrective_action, $check['check_id']]);
            $updated_count++;
        } else {
            $already_correct++;
        }
    }
    
    setFlashMessage("Processed $processed temperature checks. Updated $updated_count records, $already_correct were already correct.", 'success');
    redirect(BASE_URL . '/modules/temperature/view.php');
}
?>

<div class="container my-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Fix Temperature Compliance Status</h4>
                </div>
                <div class="card-body">
                    <p class="mb-4">
                        This utility will update all temperature check records to ensure their compliance status correctly 
                        reflects the equipment's minimum and maximum temperature ranges.
                    </p>
                    
                    <div class="alert alert-info">
                        <p><strong>Total records to process:</strong> <?php echo count($checks); ?></p>
                        <p class="mb-0">This action will update any temperature check records that have incorrect compliance statuses.</p>
                    </div>
                    
                    <form method="post" action="">
                        <?php echo getCsrfTokenField(); ?>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?php echo BASE_URL; ?>/modules/temperature/view.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-wrench"></i> Fix Compliance Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once INCLUDE_PATH . '/footer.php';
?> 