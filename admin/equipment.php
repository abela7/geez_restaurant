<?php
/**
 * Manage Equipment Page
 * 
 * Allows administrators to manage temperature monitoring equipment
 */

// Set page title
$page_title = 'Manage Equipment';

// Include header
require_once dirname(dirname(__FILE__)) . '/includes/header.php';

// Require admin or manager role
requireRole(['admin', 'manager']);

// Initialize Equipment class
require_once CLASS_PATH . '/Equipment.php';
$equipment = new Equipment($db);

// Get all equipment
$all_equipment = $equipment->getAll();

// Process form submission
$success = false;
$error = '';

if (isPostRequest()) {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        $equipment_id = isset($_POST['equipment_id']) ? (int)$_POST['equipment_id'] : null;
        $name = sanitize($_POST['name'] ?? '');
        $location = sanitize($_POST['location'] ?? '');
        $content = sanitize($_POST['content'] ?? null);
        $quantity_in_stock = isset($_POST['quantity_in_stock']) && $_POST['quantity_in_stock'] !== '' ? (int)$_POST['quantity_in_stock'] : null;
        $min_stock_quantity = isset($_POST['min_stock_quantity']) && $_POST['min_stock_quantity'] !== '' ? (int)$_POST['min_stock_quantity'] : null;
        $min_temp = isset($_POST['min_temp']) && $_POST['min_temp'] !== '' ? floatval($_POST['min_temp']) : null;
        $max_temp = isset($_POST['max_temp']) && $_POST['max_temp'] !== '' ? floatval($_POST['max_temp']) : null;
        $check_frequency = isset($_POST['check_frequency']) ? sanitize($_POST['check_frequency']) : 'Daily';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Validate input
        if (($action == 'add' || $action == 'edit') && empty($name)) {
            $error = 'Equipment name is required.';
        } else {
            $data = [
                'name' => $name,
                'location' => $location,
                'content' => $content,
                'quantity_in_stock' => $quantity_in_stock,
                'min_stock_quantity' => $min_stock_quantity,
                'min_temp' => $min_temp,
                'max_temp' => $max_temp,
                'check_frequency' => $check_frequency,
                'is_active' => $is_active
            ];
            
            if ($action == 'add') {
                // Add created_at timestamp
                $data['created_at'] = getCurrentDateTime();
                $data['updated_at'] = getCurrentDateTime();
                
                // Create new equipment
                if ($equipment->create($data)) {
                    $success = true;
                    setFlashMessage('Equipment added successfully.', 'success');
                    redirect(BASE_URL . '/admin/equipment.php');
                } else {
                    $error = 'Failed to add equipment. Please try again.';
                }
            } elseif ($action == 'edit') {
                // Add updated_at timestamp
                $data['updated_at'] = getCurrentDateTime();
                
                // Update existing equipment
                if ($equipment->update($equipment_id, $data)) {
                    $success = true;
                    setFlashMessage('Equipment updated successfully.', 'success');
                    redirect(BASE_URL . '/admin/equipment.php');
                } else {
                    $error = 'Failed to update equipment. Please try again.';
                }
            } elseif ($action == 'toggle_active') {
                // Add data for toggle_active
                $data = [
                    'is_active' => $is_active,
                    'updated_at' => getCurrentDateTime()
                ];
                
                // Toggle equipment active status
                if ($equipment->update($equipment_id, $data)) {
                    $success = true;
                    setFlashMessage('Equipment status updated successfully.', 'success');
                    redirect(BASE_URL . '/admin/equipment.php');
                } else {
                    $error = 'Failed to update equipment status. Please try again.';
                }
            } elseif ($action == 'delete') {
                // Delete equipment
                if ($equipment->delete($equipment_id)) {
                    $success = true;
                    setFlashMessage('Equipment deleted successfully.', 'success');
                    redirect(BASE_URL . '/admin/equipment.php');
                } else {
                    $error = 'Failed to delete equipment. Please try again.';
                }
            }
        }
    }
    
    // Refresh equipment list
    $all_equipment = $equipment->getAll();
}

// Page actions
$page_actions = '
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">
    <i class="bi bi-plus-circle"></i> Add Equipment
</button>';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Temperature Monitoring Equipment</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">
                    <i class="bi bi-plus-circle"></i> Add Equipment
                </button>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    Operation completed successfully.
                </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <?php if (empty($all_equipment)): ?>
                <p class="text-muted">No equipment found. Click "Add Equipment" to create a new equipment item.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Content</th>
                                <th>Stock (Min)</th>
                                <th>Min/Max Temp</th>
                                <th>Check Frequency</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_equipment as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['location'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($item['content'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php 
                                        $stock_display = 'N/A';
                                        if (isset($item['quantity_in_stock']) && $item['quantity_in_stock'] !== null) {
                                            $stock_display = htmlspecialchars($item['quantity_in_stock']);
                                            if (isset($item['min_stock_quantity']) && $item['min_stock_quantity'] !== null) {
                                                $stock_display .= ' (' . htmlspecialchars($item['min_stock_quantity']) . ')';
                                            }
                                        } elseif (isset($item['min_stock_quantity']) && $item['min_stock_quantity'] !== null) {
                                            $stock_display = 'N/A (' . htmlspecialchars($item['min_stock_quantity']) . ')';
                                        }
                                        echo $stock_display;
                                    ?>
                                </td>
                                <td>
                                    <?php if (isset($item['min_temp']) && isset($item['max_temp']) && 
                                            $item['min_temp'] !== null && $item['max_temp'] !== null): ?>
                                        <?php echo htmlspecialchars($item['min_temp']); ?>°C - <?php echo htmlspecialchars($item['max_temp']); ?>°C
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($item['check_frequency'] ?? ''); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $item['is_active'] ? 'success' : 'danger'; ?>">
                                        <?php echo $item['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editEquipmentModal" 
                                        data-equipment-id="<?php echo $item['equipment_id']; ?>"
                                        data-name="<?php echo htmlspecialchars($item['name']); ?>"
                                        data-location="<?php echo htmlspecialchars($item['location'] ?? ''); ?>"
                                        data-content="<?php echo htmlspecialchars($item['content'] ?? ''); ?>"
                                        data-quantity-in-stock="<?php echo htmlspecialchars($item['quantity_in_stock'] ?? ''); ?>"
                                        data-min-stock-quantity="<?php echo htmlspecialchars($item['min_stock_quantity'] ?? ''); ?>"
                                        data-min-temp="<?php echo htmlspecialchars($item['min_temp'] ?? ''); ?>"
                                        data-max-temp="<?php echo htmlspecialchars($item['max_temp'] ?? ''); ?>"
                                        data-check-frequency="<?php echo htmlspecialchars($item['check_frequency'] ?? ''); ?>"
                                        data-is-active="<?php echo $item['is_active']; ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    
                                    <form method="post" action="" class="d-inline">
                                        <?php echo getCsrfTokenField(); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="equipment_id" value="<?php echo $item['equipment_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('Are you sure you want to delete this equipment? This action cannot be undone.')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Equipment Modal -->
<div class="modal fade" id="addEquipmentModal" tabindex="-1" aria-labelledby="addEquipmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="">
                <?php echo getCsrfTokenField(); ?>
                <input type="hidden" name="action" value="add">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="addEquipmentModalLabel">Add Equipment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label required">Equipment Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location">
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Typical Content</label>
                        <input type="text" class="form-control" id="content" name="content" placeholder="e.g., Dairy Products, Frozen Vegetables">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="quantity_in_stock" class="form-label">Current Stock Quantity</label>
                            <input type="number" class="form-control" id="quantity_in_stock" name="quantity_in_stock" step="1">
                        </div>
                        <div class="col-md-6">
                            <label for="min_stock_quantity" class="form-label">Minimum Stock Quantity</label>
                            <input type="number" class="form-control" id="min_stock_quantity" name="min_stock_quantity" step="1">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="min_temp" class="form-label">Minimum Temperature (°C)</label>
                            <input type="number" class="form-control" id="min_temp" name="min_temp" step="0.1">
                        </div>
                        <div class="col-md-6">
                            <label for="max_temp" class="form-label">Maximum Temperature (°C)</label>
                            <input type="number" class="form-control" id="max_temp" name="max_temp" step="0.1">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="check_frequency" class="form-label">Check Frequency</label>
                        <select class="form-select" id="check_frequency" name="check_frequency">
                            <option value="Daily">Daily</option>
                            <option value="Twice Daily">Twice Daily</option>
                            <option value="Every 4 Hours">Every 4 Hours</option>
                            <option value="Every Shift">Every Shift</option>
                            <option value="Weekly">Weekly</option>
                            <option value="Monthly">Monthly</option>
                        </select>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Equipment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Equipment Modal -->
<div class="modal fade" id="editEquipmentModal" tabindex="-1" aria-labelledby="editEquipmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="">
                <?php echo getCsrfTokenField(); ?>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="equipment_id" id="edit_equipment_id">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="editEquipmentModalLabel">Edit Equipment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label required">Equipment Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="edit_location" name="location">
                    </div>
                    <div class="mb-3">
                        <label for="edit_content" class="form-label">Typical Content</label>
                        <input type="text" class="form-control" id="edit_content" name="content" placeholder="e.g., Dairy Products, Frozen Vegetables">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_quantity_in_stock" class="form-label">Current Stock Quantity</label>
                            <input type="number" class="form-control" id="edit_quantity_in_stock" name="quantity_in_stock" step="1">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_min_stock_quantity" class="form-label">Minimum Stock Quantity</label>
                            <input type="number" class="form-control" id="edit_min_stock_quantity" name="min_stock_quantity" step="1">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_min_temp" class="form-label">Minimum Temperature (°C)</label>
                            <input type="number" class="form-control" id="edit_min_temp" name="min_temp" step="0.1">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_max_temp" class="form-label">Maximum Temperature (°C)</label>
                            <input type="number" class="form-control" id="edit_max_temp" name="max_temp" step="0.1">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_check_frequency" class="form-label">Check Frequency</label>
                        <select class="form-select" id="edit_check_frequency" name="check_frequency">
                            <option value="Daily">Daily</option>
                            <option value="Twice Daily">Twice Daily</option>
                            <option value="Every 4 Hours">Every 4 Hours</option>
                            <option value="Every Shift">Every Shift</option>
                            <option value="Weekly">Weekly</option>
                            <option value="Monthly">Monthly</option>
                        </select>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active">
                        <label class="form-check-label" for="edit_is_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Equipment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Initialize edit equipment modal
    document.addEventListener('DOMContentLoaded', function() {
        var editEquipmentModal = document.getElementById('editEquipmentModal');
        if (editEquipmentModal) {
            editEquipmentModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var equipmentId = button.getAttribute('data-equipment-id');
                var name = button.getAttribute('data-name');
                var location = button.getAttribute('data-location');
                var content = button.getAttribute('data-content');
                var quantityInStock = button.getAttribute('data-quantity-in-stock');
                var minStockQuantity = button.getAttribute('data-min-stock-quantity');
                var minTemp = button.getAttribute('data-min-temp');
                var maxTemp = button.getAttribute('data-max-temp');
                var checkFrequency = button.getAttribute('data-check-frequency');
                var isActive = button.getAttribute('data-is-active') === '1';
                
                var modal = this;
                modal.querySelector('#edit_equipment_id').value = equipmentId;
                modal.querySelector('#edit_name').value = name;
                modal.querySelector('#edit_location').value = location;
                modal.querySelector('#edit_content').value = content;
                modal.querySelector('#edit_quantity_in_stock').value = quantityInStock;
                modal.querySelector('#edit_min_stock_quantity').value = minStockQuantity;
                modal.querySelector('#edit_min_temp').value = minTemp;
                modal.querySelector('#edit_max_temp').value = maxTemp;
                modal.querySelector('#edit_check_frequency').value = checkFrequency;
                modal.querySelector('#edit_is_active').checked = isActive;
                
                // Log values for debugging
                console.log('Equipment ID:', equipmentId);
                console.log('Name:', name);
                console.log('Location:', location);
                console.log('Content:', content);
                console.log('Quantity in Stock:', quantityInStock);
                console.log('Min Stock Quantity:', minStockQuantity);
                console.log('Min Temp:', minTemp);
                console.log('Max Temp:', maxTemp);
                console.log('Check Frequency:', checkFrequency);
                console.log('Is Active:', isActive);
            });
        }
    });
</script>

<?php
// Include footer
require_once INCLUDE_PATH . '/footer.php';
?>
