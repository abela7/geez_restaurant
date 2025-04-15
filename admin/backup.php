<?php
/**
 * Backup & Restore Page
 * 
 * Allows administrators to backup and restore the database
 */

// Set page title
$page_title = 'Backup & Restore';

// Include header
require_once dirname(dirname(__FILE__)) . '/includes/header.php';

// Require admin role
requireRole(['admin']);

// Process form submission
$success = false;
$error = '';
$backup_file = '';

if (isPostRequest()) {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action == 'backup') {
            // Create backup directory if it doesn't exist
            $backup_dir = dirname(dirname(__FILE__)) . '/backups';
            if (!file_exists($backup_dir)) {
                mkdir($backup_dir, 0755, true);
            }
            
            // Create .htaccess file to prevent direct access
            $htaccess_file = $backup_dir . '/.htaccess';
            if (!file_exists($htaccess_file)) {
                file_put_contents($htaccess_file, "Deny from all");
            }
            
            // Generate backup filename
            $timestamp = date('Y-m-d_H-i-s');
            $backup_filename = "geez_restaurant_backup_{$timestamp}.sql";
            $backup_file = $backup_dir . '/' . $backup_filename;
            
            // Get database credentials from config
            $db_host = DB_HOST;
            $db_user = DB_USER;
            $db_pass = DB_PASS;
            $db_name = DB_NAME;
            
            // Create backup command
            $command = "mysqldump --host={$db_host} --user={$db_user} --password={$db_pass} {$db_name} > {$backup_file}";
            
            // Execute backup command
            exec($command, $output, $return_var);
            
            if ($return_var === 0 && file_exists($backup_file)) {
                $success = true;
                setFlashMessage('Database backup created successfully.', 'success');
            } else {
                $error = 'Failed to create database backup. Please check database credentials and permissions.';
            }
        } elseif ($action == 'restore') {
            // Check if file was uploaded
            if (!isset($_FILES['restore_file']) || $_FILES['restore_file']['error'] !== UPLOAD_ERR_OK) {
                $error = 'Please select a valid backup file to restore.';
            } else {
                $uploaded_file = $_FILES['restore_file']['tmp_name'];
                $file_type = $_FILES['restore_file']['type'];
                $file_name = $_FILES['restore_file']['name'];
                
                // Validate file type
                if (!in_array($file_type, ['application/sql', 'text/plain', 'application/octet-stream'])) {
                    $error = 'Invalid file type. Please upload a valid SQL backup file.';
                } else {
                    // Get database credentials from config
                    $db_host = DB_HOST;
                    $db_user = DB_USER;
                    $db_pass = DB_PASS;
                    $db_name = DB_NAME;
                    
                    // Create restore command
                    $command = "mysql --host={$db_host} --user={$db_user} --password={$db_pass} {$db_name} < {$uploaded_file}";
                    
                    // Execute restore command
                    exec($command, $output, $return_var);
                    
                    if ($return_var === 0) {
                        $success = true;
                        setFlashMessage('Database restored successfully.', 'success');
                    } else {
                        $error = 'Failed to restore database. Please check the backup file and try again.';
                    }
                }
            }
        } elseif ($action == 'download') {
            // Get backup file path
            $backup_file = sanitize($_POST['backup_file'] ?? '');
            $backup_dir = dirname(dirname(__FILE__)) . '/backups';
            $full_path = $backup_dir . '/' . $backup_file;
            
            // Validate file exists and is within backups directory
            if (!file_exists($full_path) || !is_file($full_path) || strpos(realpath($full_path), realpath($backup_dir)) !== 0) {
                $error = 'Invalid backup file.';
            } else {
                // Set headers for download
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($full_path) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($full_path));
                
                // Clear output buffer
                ob_clean();
                flush();
                
                // Output file
                readfile($full_path);
                exit;
            }
        } elseif ($action == 'delete') {
            // Get backup file path
            $backup_file = sanitize($_POST['backup_file'] ?? '');
            $backup_dir = dirname(dirname(__FILE__)) . '/backups';
            $full_path = $backup_dir . '/' . $backup_file;
            
            // Validate file exists and is within backups directory
            if (!file_exists($full_path) || !is_file($full_path) || strpos(realpath($full_path), realpath($backup_dir)) !== 0) {
                $error = 'Invalid backup file.';
            } else {
                // Delete file
                if (unlink($full_path)) {
                    $success = true;
                    setFlashMessage('Backup file deleted successfully.', 'success');
                } else {
                    $error = 'Failed to delete backup file.';
                }
            }
        }
    }
}

// Get list of existing backups
$backup_dir = dirname(dirname(__FILE__)) . '/backups';
$backups = [];

if (file_exists($backup_dir)) {
    $files = scandir($backup_dir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && $file != '.htaccess' && pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
            $backups[] = [
                'name' => $file,
                'size' => filesize($backup_dir . '/' . $file),
                'date' => filemtime($backup_dir . '/' . $file)
            ];
        }
    }
    
    // Sort backups by date (newest first)
    usort($backups, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}
?>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Backup Database</h5>
            </div>
            <div class="card-body">
                <p>Create a backup of the current database. This will export all tables and data to a SQL file that can be used for restoration.</p>
                
                <form method="post" action="">
                    <?php echo getCsrfTokenField(); ?>
                    <input type="hidden" name="action" value="backup">
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-download"></i> Create Backup
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Restore Database</h5>
            </div>
            <div class="card-body">
                <p class="text-danger"><strong>Warning:</strong> Restoring a database will overwrite all current data. Make sure to create a backup before proceeding.</p>
                
                <form method="post" action="" enctype="multipart/form-data">
                    <?php echo getCsrfTokenField(); ?>
                    <input type="hidden" name="action" value="restore">
                    
                    <div class="mb-3">
                        <label for="restore_file" class="form-label">Select Backup File</label>
                        <input type="file" class="form-control" id="restore_file" name="restore_file" accept=".sql">
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-warning" onclick="return confirm('Are you sure you want to restore the database? This will overwrite all current data.')">
                            <i class="bi bi-upload"></i> Restore Database
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Backup History</h5>
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
                
                <?php if (empty($backups)): ?>
                <p class="text-muted">No backup files found. Use the "Create Backup" button to create your first database backup.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Filename</th>
                                <th>Size</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($backups as $backup): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($backup['name']); ?></td>
                                <td><?php echo formatFileSize($backup['size']); ?></td>
                                <td><?php echo date('d M Y H:i:s', $backup['date']); ?></td>
                                <td>
                                    <form method="post" action="" class="d-inline">
                                        <?php echo getCsrfTokenField(); ?>
                                        <input type="hidden" name="action" value="download">
                                        <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($backup['name']); ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-download"></i> Download
                                        </button>
                                    </form>
                                    
                                    <form method="post" action="" class="d-inline">
                                        <?php echo getCsrfTokenField(); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($backup['name']); ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this backup file? This action cannot be undone.')">
                                            <i class="bi bi-trash"></i> Delete
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

<?php
/**
 * Format file size in human-readable format
 * 
 * @param int $bytes File size in bytes
 * @return string Formatted file size
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

// Include footer
require_once INCLUDE_PATH . '/footer.php';
?>
