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
if (isPostRequest()) {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Validate input
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            // Attempt to authenticate user
            if ($user->authenticate($username, $password)) {
                // Redirect to dashboard
                setFlashMessage('Login successful. Welcome back!', 'success');
                redirect(BASE_URL . '/dashboard.php');
            } else {
                $error = 'Invalid username or password.';
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

<?php
// Include footer
require_once INCLUDE_PATH . '/footer.php';
?>
