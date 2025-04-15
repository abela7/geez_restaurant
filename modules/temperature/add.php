<?php
/**
 * Add Temperature Check Page
 * 
 * Allows users to record new temperature checks
 */

// Set page title
$page_title = 'Add Temperature Check';

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

// Process form submission
$success = false;
$error = '';

if (isPostRequest()) {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        $equipment_id = $_POST['equipment_id'] ?? '';
        $temperature_reading = sanitize($_POST['temperature_reading'] ?? '');
        $stock_quantity = sanitize($_POST['stock_quantity'] ?? '');
        $notes = sanitize($_POST['notes'] ?? '');
        $check_timestamp = $_POST['check_timestamp'] ?? date('Y-m-d H:i:s');
        
        // Validate input
        if (empty($equipment_id)) {
            $error = 'Please select equipment.';
        } elseif (empty($temperature_reading)) {
            $error = 'Please enter temperature reading.';
        } else {
            // Get equipment details to check compliance
            $equipment_details = $equipment->getById($equipment_id);
            
            // Determine if temperature is compliant with min/max range
            $is_compliant = 0;
            $corrective_action = '';
            
            if ($equipment_details) {
                $min_temp = $equipment_details['min_temp'];
                $max_temp = $equipment_details['max_temp'];
                
                // Check if temperature is within acceptable range
                if (is_numeric($temperature_reading)) {
                    $is_compliant = ($temperature_reading >= $min_temp && $temperature_reading <= $max_temp) ? 1 : 0;
                    
                    // If not compliant, suggest corrective action
                    if (!$is_compliant) {
                        if ($temperature_reading < $min_temp) {
                            $corrective_action = 'Temperature too low. Check equipment settings and functioning.';
                        } else if ($temperature_reading > $max_temp) {
                            $corrective_action = 'Temperature too high. Check equipment settings and functioning.';
                        }
                    }
                }
            }
            
            // Create temperature check
            $data = [
                'equipment_id' => $equipment_id,
                'check_timestamp' => $check_timestamp,
                'temperature_reading' => $temperature_reading,
                'stock_quantity_observed' => $stock_quantity,
                'notes' => $notes,
                'is_compliant' => $is_compliant,
                'corrective_action' => $corrective_action,
                'recorded_by_user_id' => $user->getCurrentUserId()
            ];
            
            if ($temp_check->create($data)) {
                $success = true;
                setFlashMessage('Temperature check recorded successfully.', 'success');
                redirect(BASE_URL . '/modules/temperature/index.php');
            } else {
                $error = 'Failed to record temperature check. Please try again.';
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="card-title mb-0"><i class="bi bi-thermometer-half"></i> Add Temperature Check</h4>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    Temperature check recorded successfully.
                </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <?php if (empty($all_equipment)): ?>
                <div class="alert alert-warning" role="alert">
                    No equipment found. Please add equipment in the admin section before recording temperature checks.
                </div>
                <?php if (hasRole(['admin', 'manager'])): ?>
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>/admin/equipment.php" class="btn btn-primary">
                        <i class="bi bi-gear"></i> Manage Equipment
                    </a>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <form method="post" action="" class="needs-validation" novalidate>
                    <?php echo getCsrfTokenField(); ?>
                    
                    <div class="mb-3">
                        <label for="equipment_id" class="form-label required">Equipment</label>
                        <select class="form-select" id="equipment_id" name="equipment_id" required>
                            <option value="">Select Equipment</option>
                            <?php foreach ($all_equipment as $item): ?>
                            <option value="<?php echo $item['equipment_id']; ?>">
                                <?php echo htmlspecialchars($item['name']); ?>
                                <?php if (!empty($item['location'])): ?>
                                (<?php echo htmlspecialchars($item['location']); ?>)
                                <?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Please select equipment.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="check_timestamp" class="form-label required">Date & Time</label>
                        <input type="datetime-local" class="form-control" id="check_timestamp" name="check_timestamp" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                        <div class="invalid-feedback">Please enter date and time.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="temperature_reading" class="form-label required">Temperature Reading</label>
                        <input type="text" class="form-control" id="temperature_reading" name="temperature_reading" placeholder="e.g., 4°C or 2.5 / -18" required>
                        <div class="form-text">Enter temperature value(s). For multiple readings, separate with slash (/).</div>
                        <div class="invalid-feedback">Please enter temperature reading.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="stock_quantity" class="form-label">Stock Quantity</label>
                        <input type="text" class="form-control" id="stock_quantity" name="stock_quantity" placeholder="e.g., 3 boxes, 75% full">
                        <div class="form-text">Optional: Enter observed stock quantity or level.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any additional observations or issues..."></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Temperature Check
                        </button>
                        <a href="<?php echo BASE_URL; ?>/modules/temperature/index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once INCLUDE_PATH . '/footer.php';
?>
