<?php
/**
 * Printable Temperature Checklist
 */

// Include configuration and common functions
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/database.php';
require_once INCLUDE_PATH . '/functions.php';

// Set timezone to London
date_default_timezone_set('Europe/London');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Initialize database connection
require_once CLASS_PATH . '/Database.php';
$db = new Database();

// Initialize user class
require_once CLASS_PATH . '/User.php';
$user = new User($db);

// Require login
requireLogin();

// Initialize Equipment and TemperatureCheck classes
require_once CLASS_PATH . '/Equipment.php';
require_once CLASS_PATH . '/TempCheck.php';
$equipment_model = new Equipment($db);
$temp_check_model = new TempCheck($db);

// Get parameters (equipment_id and month/year)
$equipment_id = isset($_GET['equipment_id']) ? (int)$_GET['equipment_id'] : null;
$month_year_str = isset($_GET['month']) ? $_GET['month'] : date('Y-m'); // Default to current month

// Validate month format (YYYY-MM)
$month_year = DateTime::createFromFormat('Y-m', $month_year_str);
if (!$month_year) {
    die("Invalid month format. Please use YYYY-MM.");
}
$year = $month_year->format('Y');
$month = $month_year->format('m');
$month_name = $month_year->format('F');

// Get all equipment for the dropdown
$all_equipment = $equipment_model->getAllActive();

// --- Data Fetching ---
$equipment_details = null;
$temperature_checks = [];

if ($equipment_id) {
    $equipment_details = $equipment_model->getById($equipment_id);
    if ($equipment_details) {
        // Calculate start and end dates for the selected month
        $start_date = date('Y-m-01', strtotime($month_year_str));
        $end_date = date('Y-m-t', strtotime($month_year_str)); // 't' gets the last day of the month
        
        // Use the getAll method with date range and equipment ID
        $temperature_checks = $temp_check_model->getAll($start_date, $end_date, $equipment_id);
    } else {
        // Handle case where equipment ID is invalid but provided
        $equipment_id = null; 
    }
}

// Set page title dynamically
$page_title = 'Printable Temperature Checklist';
if ($equipment_details) {
    $page_title .= ' - ' . htmlspecialchars($equipment_details['name']) . ' (' . $month_name . ' ' . $year . ')';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS (includes print styles) -->
    <link rel="stylesheet" href="<?php echo ASSET_URL; ?>/css/custom.css">
    
    <style>
        /* Additional Print Styles specific to this page */
        @media print {
            body { 
                font-size: 10pt; 
                -webkit-print-color-adjust: exact !important; /* Chrome, Safari */
                color-adjust: exact !important; /* Firefox, Edge */
            }
            /* Suggest smaller margins for printing */
            @page { 
                margin: 0.5in; /* Adjust as needed (e.g., 1cm, 0.25in) */
            }
            .printable-header {
                background-color: #f8f9fa !important; /* Light background for header */
                padding: 10px;
                border: 1px solid #dee2e6;
                margin-bottom: 15px;
            }
            .table thead th {
                background-color: #e9ecef !important; /* Slightly darker header for table */
                font-weight: bold;
            }
            .table td, .table th {
                padding: 0.3rem 0.5rem; /* Reduce padding for print */
                vertical-align: middle;
            }
            /* Increase main table font size slightly for print */
            .checklist-table td, .checklist-table th {
                 font-size: 11pt !important; 
            }
            /* Decrease header table font size */
            .header-details-table td {
                 font-size: 9pt !important;
                 padding: 0.1rem 0.5rem !important; /* Adjust padding */
                 border: none; /* Remove borders from header table */
                 text-align: left !important; /* Ensure left alignment */
                 vertical-align: top; /* Align top */
            }
            /* Give label cells a fixed width */
            .header-details-table .header-label {
                width: 150px; /* Adjust as needed */
                font-weight: bold;
            }
             h1, h2, h3, h4, h5, h6 {
                margin-top: 0;
                margin-bottom: 0.5rem;
            }
            /* Center text in main table body cells for print */
            .checklist-table tbody td {
                text-align: center !important;
            }
            .container { /* Ensure container doesn't restrict width too much */
                 max-width: 100% !important; 
                 width: 100% !important;
            }
            a[href]:after { /* Don't show URLs when printing */
              content: none !important;
            }
        }
        .checklist-table {
            margin-top: 20px;
        }
        /* Center text in main table body cells for screen */
        .checklist-table tbody td {
            text-align: center;
        }
        /* Center text in main table header cells */
        .checklist-table thead th {
            text-align: center;
            vertical-align: middle; /* Align vertically centered too */
        }
        .signature-line {
            border-bottom: 1px solid #000;
            display: inline-block;
            width: 100px; /* Adjust as needed */
            margin-left: 5px;
        }
    </style>
</head>
<body>

<div class="container mt-4">

    <div class="no-print mb-4 p-3 border rounded bg-light">
        <h4>Generate Temperature Checklist</h4>
        <form method="get" action="" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label for="equipment_id" class="form-label">Select Equipment:</label>
                <select class="form-select" id="equipment_id" name="equipment_id" required>
                    <option value="">-- Select Equipment --</option>
                    <?php foreach ($all_equipment as $equip): ?>
                    <option value="<?php echo $equip['equipment_id']; ?>" <?php echo ($equipment_id == $equip['equipment_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($equip['name']); ?> (<?php echo htmlspecialchars($equip['location'] ?? 'N/A'); ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="month" class="form-label">Select Month:</label>
                <input type="month" class="form-control" id="month" name="month" value="<?php echo $month_year_str; ?>" required>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Generate Checklist</button>
            </div>
        </form>
        <?php if ($equipment_details): ?>
        <button onclick="window.print()" class="btn btn-success mt-3"><i class="bi bi-printer"></i> Print Checklist</button>
        <?php endif; ?>
    </div>

    <?php if (!$equipment_id): ?>
        <div class="alert alert-info no-print">Please select equipment and month to generate the checklist.</div>
    <?php elseif (!$equipment_details): ?>
        <div class="alert alert-danger">Selected equipment not found.</div>
    <?php else: // Equipment selected and found ?>
        
        <div class="printable-header mb-4">
            <h3 class="text-center mb-3">Temperature Monitoring Log</h3>
            <table class="header-details-table w-100"> <!-- Changed to table -->
                <tbody>
                    <tr>
                        <td><strong>Month:</strong> <?php echo $month_name . ' ' . $year; ?></td>
                        <td><strong>Content:</strong> <?php echo htmlspecialchars($equipment_details['content'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Location:</strong> <?php echo htmlspecialchars($equipment_details['location'] ?? 'N/A'); ?></td>
                        <td><strong>Quantity in Stock:</strong> <?php echo htmlspecialchars($equipment_details['quantity_in_stock'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Equipment:</strong> <?php echo htmlspecialchars($equipment_details['name']); ?></td>
                        <td><strong>Min Stock Qty:</strong> <?php echo htmlspecialchars($equipment_details['min_stock_quantity'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                         <td>
                            <strong>Temp Range:</strong>
                            <?php 
                                $min = $equipment_details['min_temp'] ?? null;
                                $max = $equipment_details['max_temp'] ?? null;
                                if ($min !== null && $max !== null) {
                                    echo htmlspecialchars($min) . '&deg;C - ' . htmlspecialchars($max) . '&deg;C';
                                } else {
                                    echo 'N/A';
                                }
                            ?>
                        </td>
                        <td></td> <!-- Empty cell for alignment -->
                    </tr>
                </tbody>
            </table>
        </div>

        <table class="table table-bordered table-sm checklist-table">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Temp (&deg;C)</th>
                    <th>Stock Qty</th>
                    <th>Notes / Corrective Action</th>
                    <th>Initials</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($temperature_checks)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">No temperature checks recorded for this period.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($temperature_checks as $check): ?>
                    <tr>
                        <td><?php echo formatDate($check['check_date'] ?? '', 'd/m/y'); ?></td>
                        <td><?php echo formatDateTime($check['check_date'] . ' ' . $check['check_time'], 'H:i'); ?></td>
                        <td><?php echo htmlspecialchars($check['temperature'] ?? ''); ?></td>
                        <td><?php /* Stock Qty is per equipment, not per check - leaving blank or can be added manually */ ?></td>
                        <td><?php echo htmlspecialchars($check['notes'] ?? ''); ?> <?php if (!empty($check['corrective_action'])) echo ' | Action: ' . htmlspecialchars($check['corrective_action']); ?></td>
                        <td><?php 
                            $fullName = $check['recorded_by'] ?? 'N/A';
                            $parts = explode(' ', $fullName);
                            echo htmlspecialchars($parts[0]); // Display only the first part (first name)
                        ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                 <?php 
                 // Add some blank rows for manual entries if needed (optional)
                 $rowCount = count($temperature_checks);
                 $blankRows = max(0, 31 - $rowCount); // Changed target from 15 to 31
                 for ($i = 0; $i < $blankRows; $i++): ?>
                 <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                 </tr>
                <?php endfor; ?>
            </tbody>
        </table>
        
        <div class="mt-4" style="page-break-inside: avoid;">
             <h5>Verification:</h5>
             <p>Reviewed By (Manager Signature): <span class="signature-line"></span> Date: <span class="signature-line"></span></p>
        </div>

    <?php endif; // End if equipment selected ?>

</div> <!-- /container -->

<!-- Bootstrap JS Bundle (needed for dropdowns, modals, etc. - optional for pure print) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html> 