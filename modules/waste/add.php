<?php
/**
 * Add Food Waste Log Page
 * 
 * Allows users to record new food waste incidents
 */

// Set page title
$page_title = 'Record Food Waste';

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

// Process form submission
$success = false;
$error = '';

if (isPostRequest()) {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        $item_description = sanitize($_POST['item_description'] ?? '');
        $quantity = floatval($_POST['quantity'] ?? 0);
        $unit_of_measure = sanitize($_POST['unit_of_measure'] ?? '');
        $cost_per_unit = floatval($_POST['cost_per_unit'] ?? 0);
        $reason_for_waste = sanitize($_POST['reason_for_waste'] ?? '');
        $facility_location_id = isset($_POST['facility_location_id']) ? (int)$_POST['facility_location_id'] : null;
        $waste_timestamp = $_POST['waste_timestamp'] ?? date('Y-m-d H:i:s');
        $notes = sanitize($_POST['notes'] ?? '');
        
        // Validate input
        if (empty($item_description)) {
            $error = 'Please enter item description.';
        } elseif ($quantity <= 0) {
            $error = 'Please enter a valid quantity.';
        } elseif (empty($unit_of_measure)) {
            $error = 'Please enter unit of measure.';
        } elseif (empty($reason_for_waste)) {
            $error = 'Please select reason for waste.';
        } else {
            // Calculate total cost
            $total_cost = $cost_per_unit * $quantity;
            
            // Create waste log entry
            $data = [
                'item_description' => $item_description,
                'quantity' => $quantity,
                'unit_of_measure' => $unit_of_measure,
                'cost_per_unit' => $cost_per_unit,
                'total_cost' => $total_cost,
                'reason_for_waste' => $reason_for_waste,
                'facility_location_id' => $facility_location_id,
                'waste_timestamp' => $waste_timestamp,
                'notes' => $notes,
                'recorded_by_user_id' => $user->getCurrentUserId()
            ];
            
            if ($waste_log->create($data)) {
                $success = true;
                setFlashMessage('Food waste recorded successfully.', 'success');
                redirect(BASE_URL . '/modules/waste/index.php');
            } else {
                $error = 'Failed to record food waste. Please try again.';
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="card-title mb-0"><i class="bi bi-trash"></i> Record Food Waste</h4>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    Food waste recorded successfully.
                </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form method="post" action="" class="needs-validation" novalidate>
                    <?php echo getCsrfTokenField(); ?>
                    
                    <div class="mb-3">
                        <label for="waste_timestamp" class="form-label required">Date & Time</label>
                        <input type="datetime-local" class="form-control" id="waste_timestamp" name="waste_timestamp" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                        <div class="invalid-feedback">Please enter date and time.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="item_description" class="form-label required">Item Description</label>
                        <input type="text" class="form-control" id="item_description" name="item_description" placeholder="e.g., Chicken breast, Mixed vegetables, Rice" required>
                        <div class="invalid-feedback">Please enter item description.</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="quantity" class="form-label required">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" step="0.01" min="0.01" placeholder="e.g., 2.5" required>
                            <div class="invalid-feedback">Please enter a valid quantity.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="unit_of_measure" class="form-label required">Unit of Measure</label>
                            <select class="form-select" id="unit_of_measure" name="unit_of_measure" required>
                                <option value="">Select Unit</option>
                                <option value="kg">Kilograms (kg)</option>
                                <option value="g">Grams (g)</option>
                                <option value="lb">Pounds (lb)</option>
                                <option value="oz">Ounces (oz)</option>
                                <option value="L">Liters (L)</option>
                                <option value="ml">Milliliters (ml)</option>
                                <option value="servings">Servings</option>
                                <option value="portions">Portions</option>
                                <option value="units">Units</option>
                                <option value="plates">Plates</option>
                                <option value="containers">Containers</option>
                                <option value="pans">Pans</option>
                            </select>
                            <div class="invalid-feedback">Please select unit of measure.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="cost_per_unit" class="form-label">Cost Per Unit (£)</label>
                        <input type="number" class="form-control" id="cost_per_unit" name="cost_per_unit" step="0.01" min="0" placeholder="e.g., 4.99">
                        <div class="form-text">Enter the cost per unit to calculate total waste value.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reason_for_waste" class="form-label required">Reason for Waste</label>
                        <select class="form-select" id="reason_for_waste" name="reason_for_waste" required>
                            <option value="">Select Reason</option>
                            <option value="Expired">Expired</option>
                            <option value="Spoiled">Spoiled</option>
                            <option value="Overproduction">Overproduction</option>
                            <option value="Preparation Error">Preparation Error</option>
                            <option value="Customer Return">Customer Return</option>
                            <option value="Quality Control">Quality Control</option>
                            <option value="Contamination">Contamination</option>
                            <option value="Equipment Failure">Equipment Failure</option>
                            <option value="Power Outage">Power Outage</option>
                            <option value="Other">Other (specify in notes)</option>
                        </select>
                        <div class="invalid-feedback">Please select reason for waste.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="facility_location_id" class="form-label">Facility Location</label>
                        <select class="form-select" id="facility_location_id" name="facility_location_id">
                            <option value="">Select Location</option>
                            <?php foreach ($all_locations as $loc): ?>
                            <option value="<?php echo $loc['location_id']; ?>">
                                <?php echo htmlspecialchars($loc['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any additional details about the waste incident..."></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Food Waste Log
                        </button>
                        <a href="<?php echo BASE_URL; ?>/modules/waste/index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Calculate total cost when quantity or cost per unit changes
    document.addEventListener('DOMContentLoaded', function() {
        const quantityInput = document.getElementById('quantity');
        const costPerUnitInput = document.getElementById('cost_per_unit');
        
        function updateTotalCost() {
            const quantity = parseFloat(quantityInput.value) || 0;
            const costPerUnit = parseFloat(costPerUnitInput.value) || 0;
            const totalCost = (quantity * costPerUnit).toFixed(2);
            
            // You could display this somewhere if needed
            console.log('Total Cost: $' + totalCost);
        }
        
        if (quantityInput && costPerUnitInput) {
            quantityInput.addEventListener('input', updateTotalCost);
            costPerUnitInput.addEventListener('input', updateTotalCost);
        }
    });
</script>

<?php
// Include footer
require_once INCLUDE_PATH . '/footer.php';
?>
