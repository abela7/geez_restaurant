<?php
/**
 * Header Template
 * 
 * Common header for all pages in the Geez Restaurant application
 */

// Include configuration first
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
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
$is_logged_in = $user->isLoggedIn();
$current_user_role = $user->getCurrentUserRole();

// Get flash message if exists
$flash_message = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . APP_NAME : APP_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo ASSET_URL; ?>/img/favicon_io/favicon-16x16.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo ASSET_URL; ?>/css/custom.css">
    
    <!-- Page-specific CSS -->
    <?php if (isset($page_css)): ?>
    <link rel="stylesheet" href="<?php echo ASSET_URL; ?>/css/<?php echo $page_css; ?>">
    <?php endif; ?>
</head>
<body>
    
    <?php include INCLUDE_PATH . '/navbar.php'; // Navbar remains at the top ?>

    <!-- Offcanvas Sidebar -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mainOffcanvas" aria-labelledby="mainOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="mainOffcanvasLabel">Menu</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <?php 
            // Include the sidebar content here if the user is logged in
            if ($is_logged_in) {
                include INCLUDE_PATH . '/sidebar.php'; 
            } else {
                // Optionally show something else or nothing if not logged in
                echo '<p>Please log in to see navigation.</p>';
            }
            ?>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="container-fluid">
        <div class="row mt-3">
            <?php /* The old sidebar div is removed */ ?>
            <?php // Adjust main content area to take full width initially ?>
            <main class="col-12 px-md-4">
                
                <?php
                // Display flash messages
                if ($flash_message): 
                ?>
                <div class="alert alert-<?php echo $flash_message['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $flash_message['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php /* Comment out the main page title header section
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo isset($page_title) ? $page_title : APP_NAME; ?></h1>
                    <?php if (isset($page_actions)): ?>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <?php echo $page_actions; ?>
                    </div>
                    <?php endif; ?>
                </div>
                */ ?>

                <?php // Main content starts here ?>
            </main>
        </div>
    </div>
</body>
</html>
