<?php
/**
 * View Temperature Checks Page
 * 
 * Allows users to view temperature check history
 */

// Include necessary files
require_once __DIR__ . '/../../config/init.php';

// Check if user is logged in
requireLogin();

// Initialize required classes
$equipment_model = new Equipment($db);
$temp_check = new TempCheck($db);

// Get all active equipment for filtering
$all_equipment = $equipment_model->getAllActive();

// Process filters
$equipment_id = isset($_GET['equipment_id']) && $_GET['equipment_id'] !== '' ? (int)$_GET['equipment_id'] : null;
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$time_period = isset($_GET['time_period']) ? $_GET['time_period'] : '';
$period_value = isset($_GET['period_value']) ? $_GET['period_value'] : '';

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;

// Check if we're viewing a specific temperature check
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $check_id = (int)$_GET['id'];
    $check_details = $temp_check->getById($check_id);
    
    // Redirect if check not found
    if (!$check_details) {
        setFlashMessage('error', 'Temperature check not found.');
        redirect(BASE_URL . '/modules/temperature/view.php');
    }
} else {
    // Get temperature checks based on filters
    if ($time_period && $period_value) {
        // Get checks for a specific period (year, month, week)
        $temperature_checks = $temp_check->getByPeriod($period_value, $time_period, $equipment_id);
    } elseif ($time_period && !$period_value) {
        // Get grouped checks
        $grouped_checks = $temp_check->getGroupedBy($time_period, $equipment_id);
    } else {
        // Get all checks with pagination
        $result = $temp_check->getAll($start_date, $end_date, $equipment_id, $page, $records_per_page);
        $temperature_checks = $result['records'];
        $pagination = $result['pagination'];
    }
}

// Set page title
$page_title = "Temperature Check History";

// Include header
include_once INCLUDE_PATH . '/header.php';

// Page actions
$page_actions = '
<a href="' . BASE_URL . '/modules/temperature/add.php" class="btn btn-primary">
    <i class="bi bi-plus-circle"></i> Add Temperature Check
</a>';

// Add access to the fix compliance utility for admins and managers
if (hasRole(['admin', 'manager'])) {
    $page_actions .= '
    <a href="' . BASE_URL . '/modules/temperature/fix_compliance.php" class="btn btn-outline-secondary ms-2">
        <i class="bi bi-wrench"></i> Fix Compliance Status
    </a>';
}
?>

<?php if ($check_details): ?>
<!-- Single Check Details View -->
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0">Temperature Check Details</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Equipment:</div>
                    <div class="col-md-8"><?php echo htmlspecialchars($check_details['equipment_name']); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Date & Time:</div>
                    <div class="col-md-8"><?php echo formatDateTime($check_details['check_timestamp'], 'd M Y H:i'); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Temperature Reading:</div>
                    <div class="col-md-8"><?php echo htmlspecialchars($check_details['temperature_reading']); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Compliance Status:</div>
                    <div class="col-md-8">
                        <span class="badge bg-<?php echo isset($check_details['is_compliant']) && $check_details['is_compliant'] ? 'success' : 'danger'; ?>">
                            <?php echo isset($check_details['is_compliant']) && $check_details['is_compliant'] ? 'Compliant' : 'Non-Compliant'; ?>
                        </span>
                    </div>
                </div>
                <?php if (!empty($check_details['stock_quantity_observed'])): ?>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Stock Quantity:</div>
                    <div class="col-md-8"><?php echo htmlspecialchars($check_details['stock_quantity_observed']); ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($check_details['notes'])): ?>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Notes:</div>
                    <div class="col-md-8"><?php echo nl2br(htmlspecialchars($check_details['notes'])); ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($check_details['corrective_action'])): ?>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Corrective Action:</div>
                    <div class="col-md-8"><?php echo nl2br(htmlspecialchars($check_details['corrective_action'])); ?></div>
                </div>
                <?php endif; ?>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Recorded By:</div>
                    <div class="col-md-8"><?php echo htmlspecialchars($check_details['recorded_by']); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Recorded At:</div>
                    <div class="col-md-8"><?php echo formatDateTime($check_details['recorded_at'], 'd M Y H:i:s'); ?></div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="<?php echo BASE_URL; ?>/modules/temperature/view.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                    <a href="<?php echo BASE_URL; ?>/modules/temperature/edit.php?id=<?php echo $check_details['check_id']; ?>" class="btn btn-outline-primary">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                    <a href="<?php echo BASE_URL; ?>/modules/temperature/print.php?id=<?php echo $check_details['check_id']; ?>" class="btn btn-outline-primary" target="_blank">
                        <i class="bi bi-printer"></i> Print
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this temperature check record? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="post" action="<?php echo BASE_URL; ?>/modules/temperature/delete.php">
                    <?php echo getCsrfTokenField(); ?>
                    <input type="hidden" name="check_id" value="<?php echo $check_details['check_id']; ?>">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Temperature Checks List View -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Filter Temperature Checks</h5>
            </div>
            <div class="card-body">
                <form method="get" action="" class="row g-3">
                    <div class="col-md-3">
                        <label for="equipment_id" class="form-label">Equipment</label>
                        <select class="form-select" id="equipment_id" name="equipment_id">
                            <option value="">All Equipment</option>
                            <?php foreach ($all_equipment as $item): ?>
                            <option value="<?php echo $item['equipment_id']; ?>" <?php echo $equipment_id == $item['equipment_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($item['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="time_period" class="form-label">Group By</label>
                        <select class="form-select" id="time_period" name="time_period" onchange="toggleDateFields()">
                            <option value="">Custom Date Range</option>
                            <option value="year" <?php echo $time_period == 'year' ? 'selected' : ''; ?>>Year</option>
                            <option value="month" <?php echo $time_period == 'month' ? 'selected' : ''; ?>>Month</option>
                            <option value="week" <?php echo $time_period == 'week' ? 'selected' : ''; ?>>Week</option>
                        </select>
                    </div>
                    
                    <div id="specific_period_container" class="col-md-3" style="display: <?php echo $time_period && $period_value ? 'block' : 'none'; ?>;">
                        <label for="period_value" class="form-label">Specific Period</label>
                        <input type="text" class="form-control" id="period_value" name="period_value" value="<?php echo htmlspecialchars($period_value ?? ''); ?>" placeholder="e.g., 2023, 2023-10, 2023-W40">
                    </div>
                    
                    <div id="date_range_container" class="col-md-4" style="display: <?php echo !$time_period ? 'flex' : 'none'; ?>;">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-1">
                        <label for="per_page" class="form-label">Per Page</label>
                        <select class="form-select" id="per_page" name="per_page">
                            <option value="10" <?php echo $records_per_page == 10 ? 'selected' : ''; ?>>10</option>
                            <option value="20" <?php echo $records_per_page == 20 ? 'selected' : ''; ?>>20</option>
                            <option value="50" <?php echo $records_per_page == 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $records_per_page == 100 ? 'selected' : ''; ?>>100</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-filter"></i> Filter
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Temperature Check History</h5>
                <div>
                    <a href="<?php echo BASE_URL; ?>/modules/temperature/print.php?equipment_id=<?php echo $equipment_id; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="bi bi-printer"></i> Print Report
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($time_period && !$period_value && !empty($grouped_checks)): ?>
                <!-- Group View by Year/Month/Week -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Checks</th>
                                <th>First Check</th>
                                <th>Last Check</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grouped_checks as $group): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($group['period_label']); ?></td>
                                <td><?php echo $group['check_count']; ?></td>
                                <td><?php echo formatDateTime($group['min_date'], 'd M Y'); ?></td>
                                <td><?php echo formatDateTime($group['max_date'], 'd M Y'); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/modules/temperature/view.php?time_period=<?php echo $time_period; ?>&period_value=<?php echo $group['period_value']; ?>&equipment_id=<?php echo $equipment_id; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View Checks
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php elseif (empty($temperature_checks)): ?>
                <p class="text-muted">No temperature checks found for the selected criteria.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>Equipment</th>
                                <th>Temperature</th>
                                <th>Status</th>
                                <th>Stock</th>
                                <th>Recorded By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($temperature_checks as $check): ?>
                            <tr>
                                <td><?php echo formatDateTime($check['check_timestamp'], 'd M Y H:i'); ?></td>
                                <td><?php echo htmlspecialchars($check['equipment_name']); ?></td>
                                <td><?php echo htmlspecialchars($check['temperature_reading']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo isset($check['is_compliant']) && $check['is_compliant'] ? 'success' : 'danger'; ?>">
                                        <?php echo isset($check['is_compliant']) && $check['is_compliant'] ? 'Compliant' : 'Non-Compliant'; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($check['stock_quantity_observed'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($check['recorded_by']); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/modules/temperature/view.php?id=<?php echo $check['check_id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/modules/temperature/edit.php?id=<?php echo $check['check_id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-danger" 
                                       onclick="if(confirm('Are you sure you want to delete this record?')) window.location.href='<?php echo BASE_URL; ?>/modules/temperature/delete.php?id=<?php echo $check['check_id']; ?>&csrf_token=<?php echo urlencode(generateCsrfToken()); ?>'">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if (!$time_period && isset($pagination) && $pagination['total_pages'] > 1): ?>
                    <!-- Pagination Controls -->
                    <nav aria-label="Temperature check pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination['has_previous']): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo BASE_URL; ?>/modules/temperature/view.php?page=1&per_page=<?php echo $records_per_page; ?>&equipment_id=<?php echo $equipment_id; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" aria-label="First">
                                    <span aria-hidden="true">&laquo;&laquo;</span>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo BASE_URL; ?>/modules/temperature/view.php?page=<?php echo $pagination['current_page'] - 1; ?>&per_page=<?php echo $records_per_page; ?>&equipment_id=<?php echo $equipment_id; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php
                            // Calculate which page numbers to show
                            $start_page = max(1, $pagination['current_page'] - 2);
                            $end_page = min($pagination['total_pages'], $pagination['current_page'] + 2);
                            
                            // Always show at least 5 pages if available
                            if ($end_page - $start_page + 1 < 5) {
                                if ($start_page == 1) {
                                    $end_page = min($pagination['total_pages'], $start_page + 4);
                                } elseif ($end_page == $pagination['total_pages']) {
                                    $start_page = max(1, $end_page - 4);
                                }
                            }
                            
                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                            <li class="page-item <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo BASE_URL; ?>/modules/temperature/view.php?page=<?php echo $i; ?>&per_page=<?php echo $records_per_page; ?>&equipment_id=<?php echo $equipment_id; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['has_next']): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo BASE_URL; ?>/modules/temperature/view.php?page=<?php echo $pagination['current_page'] + 1; ?>&per_page=<?php echo $records_per_page; ?>&equipment_id=<?php echo $equipment_id; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo BASE_URL; ?>/modules/temperature/view.php?page=<?php echo $pagination['total_pages']; ?>&per_page=<?php echo $records_per_page; ?>&equipment_id=<?php echo $equipment_id; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" aria-label="Last">
                                    <span aria-hidden="true">&raquo;&raquo;</span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                        <div class="text-center mt-2 text-muted">
                            Showing <?php echo ($pagination['current_page'] - 1) * $pagination['records_per_page'] + 1; ?> to 
                            <?php echo min($pagination['current_page'] * $pagination['records_per_page'], $pagination['total_records']); ?> 
                            of <?php echo $pagination['total_records']; ?> records
                        </div>
                    </nav>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- JavaScript to toggle date fields based on time period selection -->
<script>
function toggleDateFields() {
    const timePeriodSelect = document.getElementById('time_period');
    const dateRangeContainer = document.getElementById('date_range_container');
    const specificPeriodContainer = document.getElementById('specific_period_container');
    
    if (timePeriodSelect.value) {
        dateRangeContainer.style.display = 'none';
        specificPeriodContainer.style.display = 'block';
        
        // Update placeholder based on selected time period
        const periodInput = document.getElementById('period_value');
        switch(timePeriodSelect.value) {
            case 'year':
                periodInput.placeholder = 'e.g., 2023';
                break;
            case 'month':
                periodInput.placeholder = 'e.g., 2023-10';
                break;
            case 'week':
                periodInput.placeholder = 'e.g., 2023-W40';
                break;
        }
    } else {
        dateRangeContainer.style.display = 'flex';
        specificPeriodContainer.style.display = 'none';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleDateFields();
});
</script>

<?php
// Include footer
require_once INCLUDE_PATH . '/footer.php';
?>
