<?php
/**
 * View Temperature Checks Page
 * 
 * Allows users to view temperature checks history with pagination
 */

// Set page title
$page_title = 'View Temperature Checks';

// Include header
require_once dirname(dirname(dirname(__FILE__))) . '/includes/header.php';

// Require login
requireLogin();

// Initialize Equipment and TempCheck classes
require_once CLASS_PATH . '/Equipment.php';
require_once CLASS_PATH . '/TempCheck.php';
$equipment = new Equipment($db);
$temp_check = new TempCheck($db);

// Get all active equipment for filter
$all_equipment = $equipment->getAllActive();

// Get filter parameters
$equipment_id = isset($_GET['equipment_id']) ? (int)$_GET['equipment_id'] : null;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Get period filter (new)
$period = isset($_GET['period']) ? $_GET['period'] : 'week';

// Set dates based on period if not manually specified
if (!isset($_GET['start_date']) || !isset($_GET['end_date'])) {
    switch ($period) {
        case 'week':
            $start_date = date('Y-m-d', strtotime('-7 days'));
            $end_date = date('Y-m-d');
            break;
        case 'month':
            $start_date = date('Y-m-d', strtotime('first day of this month'));
            $end_date = date('Y-m-d');
            break;
        case 'year':
            $start_date = date('Y-01-01');
            $end_date = date('Y-m-d');
            break;
    }
}

// Pagination parameters (new)
$records_per_page = 20; // Show 20 records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $records_per_page;

// Get specific check if ID provided
$check_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$check_details = null;

if ($check_id) {
    $check_details = $temp_check->getById($check_id);
    if ($check_details) {
        $page_title = 'Temperature Check Details';
    }
}

// Get temperature checks based on filters with pagination
$temperature_checks = $check_id ? [] : $temp_check->getAll($start_date, $end_date, $equipment_id, $records_per_page, $offset);

// Get total count for pagination
$total_records = $check_id ? 0 : $temp_check->countAll($start_date, $end_date, $equipment_id);
$total_pages = ceil($total_records / $records_per_page);

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
                    <!-- Period quick selection buttons (new) -->
                    <div class="col-md-12 mb-3">
                        <div class="btn-group w-100" role="group" aria-label="Time period">
                            <a href="<?php echo BASE_URL; ?>/modules/temperature/view.php?period=week&equipment_id=<?php echo $equipment_id; ?>" class="btn btn-outline-primary <?php echo $period == 'week' ? 'active' : ''; ?>">This Week</a>
                            <a href="<?php echo BASE_URL; ?>/modules/temperature/view.php?period=month&equipment_id=<?php echo $equipment_id; ?>" class="btn btn-outline-primary <?php echo $period == 'month' ? 'active' : ''; ?>">This Month</a>
                            <a href="<?php echo BASE_URL; ?>/modules/temperature/view.php?period=year&equipment_id=<?php echo $equipment_id; ?>" class="btn btn-outline-primary <?php echo $period == 'year' ? 'active' : ''; ?>">This Year</a>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
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
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
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
                <?php if (empty($temperature_checks)): ?>
                <p class="text-muted">No temperature checks found for the selected criteria.</p>
                <?php else: ?>
                <!-- Pagination info (new) -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="text-muted mb-0">
                        Showing <?php echo min(($page - 1) * $records_per_page + 1, $total_records); ?> to 
                        <?php echo min($page * $records_per_page, $total_records); ?> of 
                        <?php echo $total_records; ?> records
                    </p>
                </div>
                
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
                </div>
                
                <!-- Pagination controls (new) -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo BASE_URL; ?>/modules/temperature/view.php?page=1&period=<?php echo $period; ?>&equipment_id=<?php echo $equipment_id; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>">
                                First
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo BASE_URL; ?>/modules/temperature/view.php?page=<?php echo $page - 1; ?>&period=<?php echo $period; ?>&equipment_id=<?php echo $equipment_id; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>">
                                Previous
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php
                        // Show limited page numbers with current page in the middle
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        // Ensure we always show 5 pages when possible
                        if ($end_page - $start_page < 4) {
                            if ($start_page == 1) {
                                $end_page = min($total_pages, $start_page + 4);
                            } elseif ($end_page == $total_pages) {
                                $start_page = max(1, $end_page - 4);
                            }
                        }
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo BASE_URL; ?>/modules/temperature/view.php?page=<?php echo $i; ?>&period=<?php echo $period; ?>&equipment_id=<?php echo $equipment_id; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo BASE_URL; ?>/modules/temperature/view.php?page=<?php echo $page + 1; ?>&period=<?php echo $period; ?>&equipment_id=<?php echo $equipment_id; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>">
                                Next
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo BASE_URL; ?>/modules/temperature/view.php?page=<?php echo $total_pages; ?>&period=<?php echo $period; ?>&equipment_id=<?php echo $equipment_id; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>">
                                Last
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
// Include footer
require_once dirname(dirname(dirname(__FILE__))) . '/includes/footer.php';
?>
