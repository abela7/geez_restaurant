<?php
/**
 * Profile Page
 * 
 * Allows users to view and update their profile information
 */

// Set page title
$page_title = 'My Profile';

// Include header
require_once dirname(dirname(dirname(__FILE__))) . '/includes/header.php';

// Require login
requireLogin();

// Get current user data
$user_data = $user->getById($user->getCurrentUserId());

// Process form submission
$success = false;
$error = '';

if (isPostRequest()) {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        $full_name = sanitize($_POST['full_name'] ?? '');
        $initials = sanitize($_POST['initials'] ?? '');
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate input
        if (empty($full_name)) {
            $error = 'Full name is required.';
        } elseif (!empty($new_password) && $new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } elseif (!empty($new_password) && empty($current_password)) {
            $error = 'Current password is required to set a new password.';
        } elseif (!empty($new_password) && !password_verify($current_password, $user_data['password_hash'])) {
            $error = 'Current password is incorrect.';
        } else {
            // Update user data
            $update_data = [
                'full_name' => $full_name,
                'initials' => $initials
            ];
            
            // Update password if provided
            if (!empty($new_password)) {
                $update_data['password'] = $new_password;
            }
            
            // Perform update
            if ($user->update($user->getCurrentUserId(), $update_data)) {
                $success = true;
                
                // Update session data
                $_SESSION['full_name'] = $full_name;
                $_SESSION['initials'] = $initials;
                
                // Refresh user data
                $user_data = $user->getById($user->getCurrentUserId());
            } else {
                $error = 'Failed to update profile. Please try again.';
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="card-title mb-0"><i class="bi bi-person-circle"></i> My Profile</h4>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    Profile updated successfully.
                </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <?php echo getCsrfTokenField(); ?>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" readonly>
                        <div class="form-text">Username cannot be changed.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user_data['full_name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="initials" class="form-label">Initials</label>
                        <input type="text" class="form-control" id="initials" name="initials" value="<?php echo htmlspecialchars($user_data['initials']); ?>" maxlength="5">
                        <div class="form-text">Your initials for reports (optional).</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <input type="text" class="form-control" id="role" value="<?php echo ucfirst(htmlspecialchars($user_data['role'])); ?>" readonly>
                        <div class="form-text">Role can only be changed by an administrator.</div>
                    </div>
                    
                    <hr>
                    
                    <h5>Change Password</h5>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                        <div class="form-text">Required only if changing password.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once INCLUDE_PATH . '/footer.php';
?>
