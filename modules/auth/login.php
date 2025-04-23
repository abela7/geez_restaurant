<?php
/**
 * Login Page
 * 
 * Handles user authentication for the Geez Restaurant application
 */

// Set page title
$page_title = 'Login';

// Include header
require_once dirname(dirname(dirname(__FILE__))) . '/includes/header.php';

// Check if user is already logged in
if ($user->isLoggedIn()) {
    redirect(BASE_URL . '/dashboard.php');
}

// Process login form
$error = '';
$debug_info = []; // Track debug info

if (isPostRequest()) {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Debug info
        $debug_info['Attempted Login'] = "Username: '{$username}', Password length: " . strlen($password);
        
        // Validate input
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            // Get the user data directly for debugging
            require_once CLASS_PATH . '/Database.php';
            $db_debug = new Database();
            $sql = "SELECT * FROM users WHERE username = ?";
            $user_data = $db_debug->fetchRow($sql, [$username]);
            
            if ($user_data) {
                $debug_info['User Found'] = "Yes";
                $debug_info['User ID'] = $user_data['user_id'];
                $debug_info['Username'] = $user_data['username'];
                $debug_info['Is Active'] = $user_data['is_active'] ? 'Yes' : 'No';
                $debug_info['Role'] = $user_data['role'];
                $debug_info['Password Hash'] = $user_data['password'];
                $debug_info['Password Hash Length'] = strlen($user_data['password']);
                
                // Check password directly
                $password_verify_result = password_verify($password, $user_data['password']);
                $debug_info['password_verify() Result'] = $password_verify_result ? 'TRUE - Password matches hash' : 'FALSE - Password does not match hash';
                
                // Get password hash info
                $hash_info = password_get_info($user_data['password']);
                $debug_info['Hash Algorithm'] = $hash_info['algoName'];
                $debug_info['Hash Cost'] = $hash_info['options']['cost'] ?? 'N/A';
                
                // Generate a test hash with the entered password
                $test_hash = password_hash($password, PASSWORD_DEFAULT);
                $debug_info['Test Hash with Same Password'] = $test_hash;
                $debug_info['Test Hash Length'] = strlen($test_hash);
            } else {
                $debug_info['User Found'] = "No - User '{$username}' does not exist in database";
            }
            
            // Attempt to authenticate user
            if ($user->authenticate($username, $password)) {
                // Redirect to dashboard
                setFlashMessage('Login successful. Welcome back!', 'success');
                redirect(BASE_URL . '/dashboard.php');
            } else {
                $error = $user->getErrorMessage() ?? 'Invalid username or password.';
                $debug_info['Error from authenticate()'] = $error;
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="card-title mb-0"><i class="bi bi-box-arrow-in-right"></i> Login</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <?php echo getCsrfTokenField(); ?>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="username" name="username" required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($debug_info)): ?>
<div class="row justify-content-center mt-4">
    <div class="col-md-8">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">DEBUG INFORMATION</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <strong>Warning:</strong> This debugging information is shown for troubleshooting only. 
                    REMOVE THIS SECTION BEFORE DEPLOYING TO PRODUCTION.
                </div>
                
                <table class="table table-sm table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Debug Item</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($debug_info as $key => $value): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($key); ?></strong></td>
                            <td><?php echo htmlspecialchars($value); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <h6 class="mt-4">Additional PHP Info:</h6>
                <table class="table table-sm table-striped table-bordered">
                    <tr>
                        <td><strong>PHP Version</strong></td>
                        <td><?php echo PHP_VERSION; ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
// Include footer
require_once INCLUDE_PATH . '/footer.php';
?>
