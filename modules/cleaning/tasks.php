<?php
/**
 * Manage Cleaning Tasks Page
 * 
 * Allows administrators to manage cleaning tasks
 */

// Set page title
$page_title = 'Manage Cleaning Tasks';

// Include header
require_once dirname(dirname(dirname(__FILE__))) . '/includes/header.php';

// Require admin or manager role
requireRole(['admin', 'manager']);

// Initialize CleaningTask class
require_once CLASS_PATH . '/CleaningTask.php';
$task = new CleaningTask($db);

// Get all tasks
$all_tasks = $task->getAll();

// Get location names for reference
require_once CLASS_PATH . '/CleaningLocation.php';
$locationObj = new CleaningLocation($db);
$locations = $locationObj->getAllActive();
$location_names = [];
foreach ($locations as $loc) {
    $location_names[$loc['location_id']] = $loc['name'];
}

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
            // Add or edit task
            $task_id = isset($_POST['task_id']) ? (int)$_POST['task_id'] : null;
            $description = sanitize($_POST['description'] ?? '');
            $frequency = sanitize($_POST['frequency'] ?? '');
            $location_id = isset($_POST['location_id']) ? (int)$_POST['location_id'] : 1; // Default to first location if not provided
            $instructions = sanitize($_POST['instructions'] ?? '');
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Validate input
            if (empty($description)) {
                $error = 'Task description is required.';
            } else {
                $data = [
                    'description' => $description,
                    'frequency' => $frequency,
                    'location_id' => $location_id,
                    'instructions' => $instructions,
                    'is_active' => $is_active
                ];
                
                if ($action == 'add') {
                    // Create new task
                    if ($task->create($data)) {
                        $success = true;
                        setFlashMessage('Task added successfully.', 'success');
                        redirect(BASE_URL . '/modules/cleaning/tasks.php');
                    } else {
                        $error = 'Failed to add task. Please try again.';
                    }
                } else {
                    // Update existing task
                    if ($task->update($task_id, $data)) {
                        $success = true;
                        setFlashMessage('Task updated successfully.', 'success');
                        redirect(BASE_URL . '/modules/cleaning/tasks.php');
                    } else {
                        $error = 'Failed to update task. Please try again.';
                    }
                }
            }
        } elseif ($action == 'toggle_active') {
            // Toggle task active status
            $task_id = isset($_POST['task_id']) ? (int)$_POST['task_id'] : null;
            $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 0;
            
            if ($task->toggleActive($task_id, $is_active)) {
                $success = true;
                setFlashMessage('Task status updated successfully.', 'success');
                redirect(BASE_URL . '/modules/cleaning/tasks.php');
            } else {
                $error = 'Failed to update task status. Please try again.';
            }
        } elseif ($action == 'delete') {
            // Delete task
            $task_id = isset($_POST['task_id']) ? (int)$_POST['task_id'] : null;
            
            if ($task->delete($task_id)) {
                $success = true;
                setFlashMessage('Task deleted successfully.', 'success');
                redirect(BASE_URL . '/modules/cleaning/tasks.php');
            } else {
                $error = 'Failed to delete task. Please try again.';
            }
        }
    }
    
    // Refresh tasks list
    $all_tasks = $task->getAll();
}

// Page actions
$page_actions = '
<button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTaskModal">
    <i class="bi bi-plus-circle"></i> Add New Cleaning Task
</button>';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Cleaning Tasks</h5>
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
                
                <div class="d-grid gap-2 mb-4">
                    <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                        <i class="bi bi-plus-circle-fill"></i> Add New Cleaning Task
                    </button>
                </div>
                
                <?php if (empty($all_tasks)): ?>
                <p class="text-muted">No tasks found. Click "Add Task" to create a new cleaning task.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Frequency</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_tasks as $t): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($t['description']); ?></td>
                                <td><?php echo htmlspecialchars($t['frequency'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($location_names[$t['location_id']] ?? 'Unknown'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $t['is_active'] ? 'success' : 'danger'; ?>">
                                        <?php echo $t['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editTaskModal" 
                                        data-task-id="<?php echo $t['task_id']; ?>"
                                        data-description="<?php echo htmlspecialchars($t['description']); ?>"
                                        data-frequency="<?php echo htmlspecialchars($t['frequency'] ?? ''); ?>"
                                        data-location-id="<?php echo $t['location_id']; ?>"
                                        data-instructions="<?php echo htmlspecialchars($t['instructions'] ?? ''); ?>"
                                        data-is-active="<?php echo $t['is_active']; ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    
                                    <form method="post" action="" class="d-inline">
                                        <?php echo getCsrfTokenField(); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="task_id" value="<?php echo $t['task_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('Are you sure you want to delete this task? This action cannot be undone.')">
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

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="">
                <?php echo getCsrfTokenField(); ?>
                <input type="hidden" name="action" value="add">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="addTaskModalLabel">Add Cleaning Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="description" class="form-label required">Task Description</label>
                        <input type="text" class="form-control" id="description" name="description" required>
                    </div>
                    <div class="mb-3">
                        <label for="frequency" class="form-label">Frequency</label>
                        <select class="form-select" id="frequency" name="frequency">
                            <option value="">Select Frequency</option>
                            <option value="Daily">Daily</option>
                            <option value="Weekly">Weekly</option>
                            <option value="Monthly">Monthly</option>
                            <option value="Quarterly">Quarterly</option>
                            <option value="As Needed">As Needed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="location_id" class="form-label">Location</label>
                        <select class="form-select" id="location_id" name="location_id">
                            <option value="">Select Location</option>
                            <?php foreach ($locations as $loc): ?>
                            <option value="<?php echo $loc['location_id']; ?>"><?php echo htmlspecialchars($loc['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="instructions" class="form-label">Instructions</label>
                        <textarea class="form-control" id="instructions" name="instructions" rows="3"></textarea>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="">
                <?php echo getCsrfTokenField(); ?>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="task_id" id="edit_task_id">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="editTaskModalLabel">Edit Cleaning Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_description" class="form-label required">Task Description</label>
                        <input type="text" class="form-control" id="edit_description" name="description" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_frequency" class="form-label">Frequency</label>
                        <select class="form-select" id="edit_frequency" name="frequency">
                            <option value="">Select Frequency</option>
                            <option value="Daily">Daily</option>
                            <option value="Weekly">Weekly</option>
                            <option value="Monthly">Monthly</option>
                            <option value="Quarterly">Quarterly</option>
                            <option value="As Needed">As Needed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_location_id" class="form-label">Location</label>
                        <select class="form-select" id="edit_location_id" name="location_id">
                            <option value="">Select Location</option>
                            <?php foreach ($locations as $loc): ?>
                            <option value="<?php echo $loc['location_id']; ?>"><?php echo htmlspecialchars($loc['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_instructions" class="form-label">Instructions</label>
                        <textarea class="form-control" id="edit_instructions" name="instructions" rows="3"></textarea>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active">
                        <label class="form-check-label" for="edit_is_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Initialize edit task modal
    document.addEventListener('DOMContentLoaded', function() {
        var editTaskModal = document.getElementById('editTaskModal');
        if (editTaskModal) {
            editTaskModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var taskId = button.getAttribute('data-task-id');
                var description = button.getAttribute('data-description');
                var frequency = button.getAttribute('data-frequency');
                var locationId = button.getAttribute('data-location-id');
                var instructions = button.getAttribute('data-instructions');
                var isActive = button.getAttribute('data-is-active') === '1';
                
                var modal = this;
                modal.querySelector('#edit_task_id').value = taskId;
                modal.querySelector('#edit_description').value = description;
                modal.querySelector('#edit_frequency').value = frequency;
                modal.querySelector('#edit_location_id').value = locationId;
                modal.querySelector('#edit_instructions').value = instructions;
                modal.querySelector('#edit_is_active').checked = isActive;
            });
        }
    });
</script>

<?php
// Include footer
require_once INCLUDE_PATH . '/footer.php';
?>
