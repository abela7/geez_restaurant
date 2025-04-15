<?php
/**
 * Manage Cleaning Locations Page
 * 
 * Allows administrators to manage cleaning locations
 */

// Set page title
$page_title = 'Manage Cleaning Locations';

// Include header
require_once dirname(dirname(dirname(__FILE__))) . '/includes/header.php';

// Require admin or manager role
requireRole(['admin', 'manager']);

// Initialize CleaningLocation class
require_once CLASS_PATH . '/CleaningLocation.php';
$location = new CleaningLocation($db);

// Get all locations
$all_locations = $location->getAll();

// Process form submission
$success = false;
$error = '';

if (isPostRequest()) {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action == 'add' || $action == 'edit') {
            // Add or edit location
            $location_id = isset($_POST['location_id']) ? (int)$_POST['location_id'] : null;
            $location_name = sanitize($_POST['location'] ?? '');
            $establishment = sanitize($_POST['establishment'] ?? '');
            $building = sanitize($_POST['building'] ?? '');
            $kitchen_number = sanitize($_POST['kitchen_number'] ?? '');
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Validate input
            if (empty($location_name)) {
                $error = 'Location name is required.';
            } else {
                // Create a description from the establishment, building and kitchen number
                $description = '';
                if (!empty($establishment)) {
                    $description .= "Establishment: $establishment";
                }
                if (!empty($building)) {
                    $description .= (!empty($description) ? ", " : "") . "Building: $building";
                }
                if (!empty($kitchen_number)) {
                    $description .= (!empty($description) ? ", " : "") . "Kitchen Number: $kitchen_number";
                }
                
                $data = [
                    'name' => $location_name,
                    'description' => $description,
                    'is_active' => $is_active
                ];
                
                if ($action == 'add') {
                    // Add timestamps
                    $data['created_at'] = getCurrentDateTime();
                    $data['updated_at'] = getCurrentDateTime();
                    
                    // Create new location
                    if ($location->create($data)) {
                        $success = true;
                        setFlashMessage('Location added successfully.', 'success');
                        redirect(BASE_URL . '/modules/cleaning/locations.php');
                    } else {
                        $error = 'Failed to add location. Please try again.';
                    }
                } else {
                    // Add updated timestamp
                    $data['updated_at'] = getCurrentDateTime();
                    
                    // Update existing location
                    if ($location->update($location_id, $data)) {
                        $success = true;
                        setFlashMessage('Location updated successfully.', 'success');
                        redirect(BASE_URL . '/modules/cleaning/locations.php');
                    } else {
                        $error = 'Failed to update location. Please try again.';
                    }
                }
            }
        } elseif ($action == 'toggle_active') {
            // Toggle location active status
            $location_id = isset($_POST['location_id']) ? (int)$_POST['location_id'] : null;
            $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 0;
            
            // Add data for toggle_active
            $data = [
                'is_active' => $is_active,
                'updated_at' => getCurrentDateTime()
            ];
            
            // Toggle location active status
            if ($location->update($location_id, $data)) {
                $success = true;
                setFlashMessage('Location status updated successfully.', 'success');
                redirect(BASE_URL . '/modules/cleaning/locations.php');
            } else {
                $error = 'Failed to update location status. Please try again.';
            }
        } elseif ($action == 'delete') {
            // Delete location
            $location_id = isset($_POST['location_id']) ? (int)$_POST['location_id'] : null;
            
            if ($location->delete($location_id)) {
                $success = true;
                setFlashMessage('Location deleted successfully.', 'success');
                redirect(BASE_URL . '/modules/cleaning/locations.php');
            } else {
                $error = 'Failed to delete location. Please try again.';
            }
        }
    }
    
    // Refresh locations list
    $all_locations = $location->getAll();
}

// Page actions
$page_actions = '
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLocationModal">
    <i class="bi bi-plus-circle"></i> Add Location
</button>';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Cleaning Locations</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLocationModal">
                    <i class="bi bi-plus-circle"></i> Add Location
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
                
                <?php if (empty($all_locations)): ?>
                <p class="text-muted">No locations found. Click "Add Location" to create a new location.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>Establishment</th>
                                <th>Building</th>
                                <th>Kitchen Number</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_locations as $loc): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($loc['name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($loc['establishment'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($loc['building'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($loc['kitchen_number'] ?? ''); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $loc['is_active'] ? 'success' : 'danger'; ?>">
                                        <?php echo $loc['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editLocationModal" 
                                        data-location-id="<?php echo $loc['location_id']; ?>"
                                        data-location="<?php echo htmlspecialchars($loc['name'] ?? ''); ?>"
                                        data-establishment="<?php echo htmlspecialchars($loc['establishment'] ?? ''); ?>"
                                        data-building="<?php echo htmlspecialchars($loc['building'] ?? ''); ?>"
                                        data-kitchen-number="<?php echo htmlspecialchars($loc['kitchen_number'] ?? ''); ?>"
                                        data-is-active="<?php echo $loc['is_active']; ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    
                                    <form method="post" action="" class="d-inline">
                                        <?php echo getCsrfTokenField(); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="location_id" value="<?php echo $loc['location_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('Are you sure you want to delete this location? This action cannot be undone.')">
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

<!-- Add Location Modal -->
<div class="modal fade" id="addLocationModal" tabindex="-1" aria-labelledby="addLocationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="">
                <?php echo getCsrfTokenField(); ?>
                <input type="hidden" name="action" value="add">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="addLocationModalLabel">Add Cleaning Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="location" class="form-label required">Location Name</label>
                        <input type="text" class="form-control" id="location" name="location" required>
                    </div>
                    <div class="mb-3">
                        <label for="establishment" class="form-label">Establishment</label>
                        <input type="text" class="form-control" id="establishment" name="establishment">
                    </div>
                    <div class="mb-3">
                        <label for="building" class="form-label">Building</label>
                        <input type="text" class="form-control" id="building" name="building">
                    </div>
                    <div class="mb-3">
                        <label for="kitchen_number" class="form-label">Kitchen Number</label>
                        <input type="text" class="form-control" id="kitchen_number" name="kitchen_number">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Location</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Location Modal -->
<div class="modal fade" id="editLocationModal" tabindex="-1" aria-labelledby="editLocationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="">
                <?php echo getCsrfTokenField(); ?>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="location_id" id="edit_location_id">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="editLocationModalLabel">Edit Cleaning Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_location" class="form-label required">Location Name</label>
                        <input type="text" class="form-control" id="edit_location" name="location" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_establishment" class="form-label">Establishment</label>
                        <input type="text" class="form-control" id="edit_establishment" name="establishment">
                    </div>
                    <div class="mb-3">
                        <label for="edit_building" class="form-label">Building</label>
                        <input type="text" class="form-control" id="edit_building" name="building">
                    </div>
                    <div class="mb-3">
                        <label for="edit_kitchen_number" class="form-label">Kitchen Number</label>
                        <input type="text" class="form-control" id="edit_kitchen_number" name="kitchen_number">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active">
                        <label class="form-check-label" for="edit_is_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Location</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Initialize edit location modal
    document.addEventListener('DOMContentLoaded', function() {
        var editLocationModal = document.getElementById('editLocationModal');
        if (editLocationModal) {
            editLocationModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var locationId = button.getAttribute('data-location-id');
                var location = button.getAttribute('data-location');
                var establishment = button.getAttribute('data-establishment');
                var building = button.getAttribute('data-building');
                var kitchenNumber = button.getAttribute('data-kitchen-number');
                var isActive = button.getAttribute('data-is-active') === '1';
                
                var modal = this;
                modal.querySelector('#edit_location_id').value = locationId;
                modal.querySelector('#edit_location').value = location;
                modal.querySelector('#edit_establishment').value = establishment;
                modal.querySelector('#edit_building').value = building;
                modal.querySelector('#edit_kitchen_number').value = kitchenNumber;
                modal.querySelector('#edit_is_active').checked = isActive;
                
                // Log values for debugging
                console.log('Location ID:', locationId);
                console.log('Location Name:', location);
                console.log('Establishment:', establishment);
                console.log('Building:', building);
                console.log('Kitchen Number:', kitchenNumber);
                console.log('Is Active:', isActive);
            });
        }
    });
</script>

<?php
// Include footer
require_once INCLUDE_PATH . '/footer.php';
?>
