<?php
/**
 * Manage Users Page
 * 
 * Allows administrators to manage system users
 */

// Set page title
$page_title = 'Manage Users';

// Include header
require_once dirname(dirname(__FILE__)) . '/includes/header.php';

// Require admin role
requireRole(['admin']);

// Initialize User class
require_once CLASS_PATH . '/User.php';
$user_model = new User($db);

// Get all users
$all_users = $user_model->getAll();

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
            // Add or edit user
            $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
            $username = sanitize($_POST['username'] ?? '');
            $full_name = sanitize($_POST['full_name'] ?? '');
            $role = sanitize($_POST['role'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Validate input
            if (empty($username)) {
                $error = 'Username is required.';
            } elseif (empty($full_name)) {
                $error = 'Full name is required.';
            } elseif (empty($role)) {
                $error = 'Role is required.';
            } elseif ($action == 'add' && empty($password)) {
                $error = 'Password is required for new users.';
            } elseif (($action == 'add' || !empty($password)) && $password !== $confirm_password) {
                $error = 'Passwords do not match.';
            } else {
                $data = [
                    'username' => $username,
                    'full_name' => $full_name,
                    'role' => $role,
                    'is_active' => $is_active
                ];
                
                // Add password if provided
                if (!empty($password)) {
                    $data['password'] = password_hash($password, PASSWORD_DEFAULT);
                }
                
                if ($action == 'add') {
                    // Check if username already exists
                    if ($user_model->getUserByUsername($username)) {
                        $error = 'Username already exists. Please choose a different username.';
                    } else {
                        // Create new user
                        if ($user_model->create($data)) {
                            $success = true;
                            setFlashMessage('User added successfully.', 'success');
                            redirect(BASE_URL . '/admin/users.php');
                        } else {
                            $error = 'Failed to add user. Please try again.';
                        }
                    }
                } else {
                    // Check if username already exists for another user
                    $existing_user = $user_model->getUserByUsername($username);
                    if ($existing_user && $existing_user['user_id'] != $user_id) {
                        $error = 'Username already exists. Please choose a different username.';
                    } else {
                        // Update existing user
                        if ($user_model->update($user_id, $data)) {
                            $success = true;
                            setFlashMessage('User updated successfully.', 'success');
                            redirect(BASE_URL . '/admin/users.php');
                        } else {
                            $error = 'Failed to update user. Please try again.';
                        }
                    }
                }
            }
        } elseif ($action == 'toggle_active') {
            // Toggle user active status
            $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
            $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 0;
            
            // Prevent deactivating own account
            if ($user_id == $_SESSION['user_id'] && $is_active == 0) {
                $error = 'You cannot deactivate your own account.';
            } else {
                if ($user_model->toggleActive($user_id, $is_active)) {
                    $success = true;
                    setFlashMessage('User status updated successfully.', 'success');
                    redirect(BASE_URL . '/admin/users.php');
                } else {
                    $error = 'Failed to update user status. Please try again.';
                }
            }
        } elseif ($action == 'delete') {
            // Delete user
            $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
            
            // Prevent deleting own account
            if ($user_id == $_SESSION['user_id']) {
                $error = 'You cannot delete your own account.';
            } else {
                if ($user_model->delete($user_id)) {
                    $success = true;
                    setFlashMessage('User deleted successfully.', 'success');
                    redirect(BASE_URL . '/admin/users.php');
                } else {
                    $error = 'Failed to delete user. Please try again.';
                }
            }
        }
    }
    
    // Refresh users list
    $all_users = $user_model->getAll();
}

// Page actions
$page_actions = '
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
    <i class="bi bi-person-plus"></i> Add User
</button>';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">System Users</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-person-plus"></i> Add User
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
                
                <?php if (empty($all_users)): ?>
                <p class="text-muted">No users found. Click "Add User" to create a new user account.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_users as $u): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $u['role'] == 'admin' ? 'danger' : 
                                            ($u['role'] == 'manager' ? 'warning' : 'info'); 
                                    ?>">
                                        <?php echo ucfirst($u['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $u['is_active'] ? 'success' : 'danger'; ?>">
                                        <?php echo $u['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $u['last_login'] ? formatDateTime($u['last_login'], 'd M Y H:i') : 'Never'; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editUserModal" 
                                        data-user-id="<?php echo $u['user_id']; ?>"
                                        data-username="<?php echo htmlspecialchars($u['username']); ?>"
                                        data-full-name="<?php echo htmlspecialchars($u['full_name']); ?>"
                                        data-role="<?php echo $u['role']; ?>"
                                        data-is-active="<?php echo $u['is_active']; ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    
                                    <?php if ($u['user_id'] != $_SESSION['user_id']): ?>
                                    <form method="post" action="" class="d-inline">
                                        <?php echo getCsrfTokenField(); ?>
                                        <input type="hidden" name="action" value="toggle_active">
                                        <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                                        <input type="hidden" name="is_active" value="<?php echo $u['is_active'] ? 0 : 1; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-<?php echo $u['is_active'] ? 'warning' : 'success'; ?>" 
                                            onclick="return confirm('Are you sure you want to <?php echo $u['is_active'] ? 'deactivate' : 'activate'; ?> this user?')">
                                            <i class="bi bi-<?php echo $u['is_active'] ? 'x-circle' : 'check-circle'; ?>"></i>
                                        </button>
                                    </form>
                                    
                                    <form method="post" action="" class="d-inline">
                                        <?php echo getCsrfTokenField(); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="">
                <?php echo getCsrfTokenField(); ?>
                <input type="hidden" name="action" value="add">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="username" class="form-label required">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="full_name" class="form-label required">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label required">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="admin">Administrator</option>
                            <option value="manager">Manager</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label required">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label required">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="">
                <?php echo getCsrfTokenField(); ?>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="user_id" id="edit_user_id">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_username" class="form-label required">Username</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_full_name" class="form-label required">Full Name</label>
                        <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_role" class="form-label required">Role</label>
                        <select class="form-select" id="edit_role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="admin">Administrator</option>
                            <option value="manager">Manager</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="edit_password" name="password">
                        <div class="form-text">Leave blank to keep current password.</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="edit_confirm_password" name="confirm_password">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active">
                        <label class="form-check-label" for="edit_is_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Initialize edit user modal
    document.addEventListener('DOMContentLoaded', function() {
        var editUserModal = document.getElementById('editUserModal');
        if (editUserModal) {
            editUserModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var userId = button.getAttribute('data-user-id');
                var username = button.getAttribute('data-username');
                var fullName = button.getAttribute('data-full-name');
                var email = button.getAttribute('data-email');
                var role = button.getAttribute('data-role');
                var isActive = button.getAttribute('data-is-active') === '1';
                
                var modal = this;
                modal.querySelector('#edit_user_id').value = userId;
                modal.querySelector('#edit_username').value = username;
                modal.querySelector('#edit_full_name').value = fullName;
                modal.querySelector('#edit_email').value = email;
                modal.querySelector('#edit_role').value = role;
                modal.querySelector('#edit_is_active').checked = isActive;
            });
        }
    });
</script>

<?php
// Include footer
require_once INCLUDE_PATH . '/footer.php';
?>
