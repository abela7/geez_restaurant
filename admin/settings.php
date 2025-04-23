<?php
/**
 * System Settings Page
 * 
 * Allows administrators to configure system settings
 */

// Set page title
$page_title = 'System Settings';

// Include header
require_once dirname(dirname(__FILE__)) . '/includes/header.php';

// Require admin role
requireRole(['admin']);

// Process form submission
$success = false;
$error = '';

// Process form submission for reset
$reset_success = false;
$reset_error = '';

// Define settings with default values
$settings = [
    'site_name' => 'Geez Restaurant Food Hygiene & Safety Management System',
    'company_name' => 'Geez Restaurant',
    'company_address' => '',
    'company_phone' => '',
    'company_email' => '',
    'temperature_unit' => 'C',
    'date_format' => 'd/m/Y',
    'time_format' => 'H:i',
    'items_per_page' => '20',
    'enable_email_notifications' => '0',
    'smtp_host' => '',
    'smtp_port' => '587',
    'smtp_username' => '',
    'smtp_password' => '',
    'smtp_encryption' => 'tls'
];

// Load current settings from database
$query = "SELECT setting_key, setting_value FROM system_settings";
// Use fetchAll with PDO::FETCH_ASSOC for compatibility
try {
    $current_settings = $db->fetchAll($query); // Use fetchAll available in Database class
    if ($current_settings) {
        foreach ($current_settings as $row) {
            // Ensure the key exists before assigning
            if (array_key_exists($row['setting_key'], $settings)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
        }
    }
} catch (Exception $e) {
    // Log error or handle cases where the table might not exist yet
    error_log("Error loading system settings: " . $e->getMessage());
    // Optionally set a default error state or message
}

if (isPostRequest()) {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        // Update settings
        foreach ($settings as $key => $value) {
            if (isset($_POST[$key])) {
                $new_value = sanitize($_POST[$key]);
                
                // Check if setting exists
                $check_query = "SELECT setting_id FROM system_settings WHERE setting_key = ?";
                $check_stmt = $db->prepare($check_query);
                $check_stmt->bind_param('s', $key);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result && $check_result->num_rows > 0) {
                    // Update existing setting
                    $update_query = "UPDATE system_settings SET setting_value = ? WHERE setting_key = ?";
                    $update_stmt = $db->prepare($update_query);
                    $update_stmt->bind_param('ss', $new_value, $key);
                    $update_stmt->execute();
                } else {
                    // Insert new setting
                    $insert_query = "INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?)";
                    $insert_stmt = $db->prepare($insert_query);
                    $insert_stmt->bind_param('ss', $key, $new_value);
                    $insert_stmt->execute();
                }
                
                // Update local settings array
                $settings[$key] = $new_value;
            }
        }
        
        $success = true;
        setFlashMessage('Settings updated successfully.', 'success');
    }
}

if (isPostRequest() && isset($_POST['action']) && $_POST['action'] == 'reset_application') {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $reset_error = 'Invalid request. Please try again.';
    } else {
        // Double-check confirmation (although JS confirmation should also exist)
        if (isset($_POST['confirm_reset']) && $_POST['confirm_reset'] === 'RESET_CONFIRMED') {
            
            // List of tables to truncate (excluding users and settings)
            $tables_to_reset = [
                'temperature_checks',
                'cleaning_log',
                'food_waste_log',
                'equipment',
                'cleaning_task',
                'cleaning_locations'
                // Add any other application data tables here, EXCLUDING 'users' and potentially 'settings'/'system_settings'
            ];

            try {
                $db->beginTransaction();
                
                foreach ($tables_to_reset as $table) {
                    // Using DELETE FROM is safer for transactional integrity
                    $db->query("DELETE FROM `" . $table . "`"); 
                }
                
                $db->commit();
                $reset_success = true;
                setFlashMessage('Application data has been successfully reset.', 'success');
                // Redirect to prevent re-submission on refresh
                redirect(BASE_URL . '/admin/settings.php');
                
            } catch (Exception $e) {
                $db->rollback();
                $reset_error = 'An error occurred during the reset process: ' . $e->getMessage();
                error_log("Application Reset Error: " . $e->getMessage());
            }
        } else {
             $reset_error = 'Reset confirmation was not provided correctly.';
        }
    }
}
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-gear"></i> System Settings</h5>
            </div>
            <div class="card-body">
                <p>Manage system-wide settings and perform administrative actions.</p>
            </div>
        </div>
    </div>
                </div>

<div class="row">
    <!-- Application Reset Section -->
    <div class="col-md-6 mb-4">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0"><i class="bi bi-exclamation-triangle"></i> Reset Application Data</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($reset_error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $reset_error; ?>
                </div>
                <?php endif; ?>
                
                <p class="text-danger"><strong>Warning:</strong> This action will permanently delete all application data including:</p>
                <ul>
                    <li>Temperature Checks</li>
                    <li>Cleaning Logs</li>
                    <li>Food Waste Logs</li>
                    <li>Equipment Records</li>
                    <li>Cleaning Tasks</li>
                    <li>Cleaning Locations</li>
                </ul>
                <p class="text-danger"><strong>User accounts will NOT be deleted.</strong> This action cannot be undone.</p>
                
                <form method="post" action="" 
                      onsubmit="return confirm('DANGER! Are you absolutely sure you want to reset ALL application data (except users)? This cannot be undone!');">
                    <?php echo getCsrfTokenField(); ?>
                    <input type="hidden" name="action" value="reset_application">
                    <input type="hidden" name="confirm_reset" value="RESET_CONFIRMED"> 
                    
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="bi bi-trash"></i> Reset All Application Data Now
                    </button>
                </form>
                            </div>
                        </div>
                    </div>
                    
    <!-- Add other settings sections here if needed -->
    <!--
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Other Settings</h5>
                    </div>
            <div class="card-body">
                <p>Placeholder for future settings.</p>
            </div>
        </div>
    </div>
    -->

</div>

<?php
// Include footer
require_once INCLUDE_PATH . '/footer.php';
?>
