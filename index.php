<?php
/**
 * Index Page
 * 
 * Main entry point for the Geez Restaurant application
 */

// Include configuration first
require_once dirname(__FILE__) . '/config/config.php';
require_once dirname(__FILE__) . '/config/database.php';
require_once INCLUDE_PATH . '/functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Initialize database connection
require_once CLASS_PATH . '/Database.php';
$db = new Database();

// Initialize user
require_once CLASS_PATH . '/User.php';
$user = new User($db);

// Check if user is logged in
if ($user->isLoggedIn()) {
    // Redirect to dashboard
    redirect(BASE_URL . '/dashboard.php');
}

// Set page title
$page_title = 'Welcome';

// Include header
require_once INCLUDE_PATH . '/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 text-center">
        <div class="mt-5 mb-4">
            <h1 class="display-4">Welcome to Geez Restaurant</h1>
            <p class="lead">Food Hygiene & Safety Management System</p>
        </div>
        
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2>Digital Food Safety Records</h2>
                <p>This system helps Geez Restaurant staff maintain accurate and reliable food safety records, replacing traditional paper-based logbooks with a modern digital solution.</p>
                
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-body text-center">
                                <i class="bi bi-thermometer-half text-primary" style="font-size: 2rem;"></i>
                                <h5 class="card-title mt-3">Temperature Checks</h5>
                                <p class="card-text">Record and monitor equipment temperatures to ensure food safety.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-body text-center">
                                <i class="bi bi-check2-square text-success" style="font-size: 2rem;"></i>
                                <h5 class="card-title mt-3">Cleaning Logs</h5>
                                <p class="card-text">Track completion of scheduled cleaning tasks across all areas.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-body text-center">
                                <i class="bi bi-trash text-danger" style="font-size: 2rem;"></i>
                                <h5 class="card-title mt-3">Food Waste Tracking</h5>
                                <p class="card-text">Log and analyze food waste to reduce costs and improve efficiency.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="<?php echo BASE_URL; ?>/modules/auth/login.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right"></i> Login to System
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once INCLUDE_PATH . '/footer.php';
?>
