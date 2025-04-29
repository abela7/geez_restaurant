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

// Get parameters (equipment_ids and month/year)
$equipment_ids = isset($_GET['equipment_ids']) ? $_GET['equipment_ids'] : [];
$month_year_str = isset($_GET['month']) ? $_GET['month'] : date('Y-m'); // Default to current month
$show_log = isset($_GET['show_log']) && $_GET['show_log'] == '1';

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
$equipment_details_list = [];
$temperature_checks_by_equipment = [];
$all_dates = [];
$consolidated_checks = [];

if (!empty($equipment_ids)) {
    // Calculate start and end dates for the selected month
    $start_date = date('Y-m-01', strtotime($month_year_str));
    $end_date = date('Y-m-t', strtotime($month_year_str)); // 't' gets the last day of the month
    
    foreach ($equipment_ids as $equipment_id) {
        $equipment_details = $equipment_model->getById($equipment_id);
        if ($equipment_details) {
            $equipment_details_list[$equipment_id] = $equipment_details;
            // Use the getAll method with date range and equipment ID
            $temperature_checks = $temp_check_model->getAll($start_date, $end_date, $equipment_id);
            $temperature_checks_by_equipment[$equipment_id] = $temperature_checks;
            
            // Collect all dates and organize checks by date
            foreach ($temperature_checks as $check) {
                $check_date = $check['check_date'] ?? '';
                if (!empty($check_date)) {
                    $all_dates[$check_date] = true;
                    $consolidated_checks[$check_date][$equipment_id] = $check;
                }
            }
        }
    }
    
    // Sort dates in descending order
    krsort($all_dates);
}

// Set page title dynamically
$page_title = 'Printable Temperature Checklist';
if (!empty($equipment_details_list)) {
    $page_title .= ' - ' . $month_name . ' ' . $year;
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
            /* Optimize margins for printing */
            @page { 
                margin: 0.3in; /* Reduced margins */
                size: landscape;
            }
            .printable-header {
                background-color: #f8f9fa !important; /* Light background for header */
                padding: 8px;
                border: 1px solid #dee2e6;
                margin-bottom: 10px;
            }
            .table thead th {
                background-color: #e9ecef !important; /* Slightly darker header for table */
                font-weight: bold;
            }
            .table td, .table th {
                padding: 0.2rem 0.4rem; /* Further reduced padding for print */
                vertical-align: middle;
            }
            /* Increase main table font size slightly for print */
            .checklist-table td, .checklist-table th {
                 font-size: 10pt !important; 
            }
            /* Decrease header table font size */
            .header-details-table td {
                 font-size: 9pt !important;
                 padding: 0.1rem 0.3rem !important; /* Reduced padding */
                 border: none; /* Remove borders from header table */
                 text-align: left !important; /* Ensure left alignment */
                 vertical-align: top; /* Align top */
            }
            /* Give label cells a fixed width */
            .header-details-table .header-label {
                width: 120px; /* Adjusted for space saving */
                font-weight: bold;
            }
             h1, h2, h3, h4, h5, h6 {
                margin-top: 0;
                margin-bottom: 0.3rem;
                font-size: 14pt !important;
            }
            /* Center text in main table body cells for print */
            .checklist-table tbody td {
                text-align: center !important;
            }
            .container { /* Ensure container doesn't restrict width too much */
                 max-width: 100% !important; 
                 width: 100% !important;
                 padding: 0 !important;
                 margin: 0 !important;
            }
            a[href]:after { /* Don't show URLs when printing */
              content: none !important;
            }
            .no-print {
                display: none !important;
            }
            .equipment-header {
                text-align: center !important;
                font-weight: bold !important;
                background-color: #e9ecef !important;
            }
            .verification-section {
                margin-top: 20px;
                page-break-inside: avoid;
            }
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
        .checklist-table {
            margin-top: 15px; /* Reduced margin */
        }
        /* Hide the log display by default */
        .log-display {
            display: none;
        }
        /* Show when print button clicked */
        .log-display.show {
            display: block;
        }
        .equipment-header {
            text-align: center;
            font-weight: bold;
            background-color: #e9ecef;
        }
    </style>
</head>
<body>

<div class="container mt-4">

    <div class="no-print mb-4 p-3 border rounded bg-light">
        <h4>Generate Temperature Checklist</h4>
        <form method="get" action="" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label for="equipment_ids" class="form-label">Select Equipment:</label>
                <select class="form-select" id="equipment_ids" name="equipment_ids[]" multiple required>
                    <?php foreach ($all_equipment as $equip): ?>
                    <option value="<?php echo $equip['equipment_id']; ?>" <?php echo (in_array($equip['equipment_id'], $equipment_ids)) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($equip['name']); ?> (<?php echo htmlspecialchars($equip['location'] ?? 'N/A'); ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple equipment</small>
            </div>
            <div class="col-md-4">
                <label for="month" class="form-label">Select Month:</label>
                <input type="month" class="form-control" id="month" name="month" value="<?php echo $month_year_str; ?>" required>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Generate Checklist</button>
            </div>
        </form>
        <?php if (!empty($equipment_details_list)): ?>
        <button id="printButton" class="btn btn-success mt-3"><i class="bi bi-printer"></i> Print Checklist</button>
        <?php endif; ?>
    </div>

    <?php if (empty($equipment_ids)): ?>
        <div class="alert alert-info no-print">Please select equipment and month to generate the checklist.</div>
    <?php elseif (empty($equipment_details_list)): ?>
        <div class="alert alert-danger">No valid equipment selected.</div>
    <?php else: // Equipment selected and found ?>
        
        <div id="logDisplay" class="log-display <?php echo $show_log ? 'show' : ''; ?>">
            <div class="printable-header mb-3">
                <h3 class="text-center mb-2">Temperature Monitoring Log</h3>
                <table class="header-details-table w-100">
                    <tbody>
                        <tr>
                            <td><strong>Month:</strong> <?php echo $month_name . ' ' . $year; ?></td>
                            <td><strong>Location:</strong> 
                                <?php 
                                $locations = array_unique(array_map(function($eq) {
                                    return $eq['location'] ?? 'N/A';
                                }, $equipment_details_list));
                                echo htmlspecialchars(implode(', ', $locations));
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Equipment:</strong>
                                <?php 
                                $equipment_names = array_map(function($eq) {
                                    return $eq['name'] ?? 'N/A';
                                }, $equipment_details_list);
                                echo htmlspecialchars(implode(', ', $equipment_names));
                                ?>
                            </td>
                            <td><strong>Content:</strong>
                                <?php 
                                $contents = array_unique(array_map(function($eq) {
                                    return $eq['content'] ?? 'N/A';
                                }, $equipment_details_list));
                                echo htmlspecialchars(implode(', ', $contents));
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <table class="table table-bordered table-sm checklist-table">
                <thead class="table-light">
                    <tr>
                        <th rowspan="2">Date</th>
                        <?php foreach ($equipment_details_list as $eq_id => $eq_details): ?>
                        <th colspan="3" class="equipment-header"><?php echo htmlspecialchars($eq_details['name']); ?></th>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <?php foreach ($equipment_details_list as $eq_id => $eq_details): ?>
                        <th>Time</th>
                        <th>Temp (&deg;C)</th>
                        <th>Initials</th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($consolidated_checks)): ?>
                        <tr>
                            <td colspan="<?php echo count($equipment_details_list) * 3 + 1; ?>" class="text-center text-muted">
                                No temperature checks recorded for this period.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($all_dates as $date => $_): ?>
                        <tr>
                            <td><?php echo formatDate($date, 'd/m/y'); ?></td>
                            
                            <?php foreach ($equipment_details_list as $eq_id => $eq_details): 
                                $check = $consolidated_checks[$date][$eq_id] ?? null;
                            ?>
                                <td>
                                    <?php echo $check ? formatDateTime($check['check_date'] . ' ' . $check['check_time'], 'H:i') : '&nbsp;'; ?>
                                </td>
                                <td>
                                    <?php echo $check ? htmlspecialchars($check['temperature'] ?? '') : '&nbsp;'; ?>
                                </td>
                                <td>
                                    <?php 
                                    if ($check) {
                                        $fullName = $check['recorded_by'] ?? 'N/A';
                                        $parts = explode(' ', $fullName);
                                        echo htmlspecialchars($parts[0]); // Display only the first part (first name)
                                    } else {
                                        echo '&nbsp;';
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?php 
                    // Add blank rows for manual entries
                    $record_dates_count = count($all_dates);
                    $daysInMonth = date('t', strtotime($month_year_str));
                    $blankRows = max(0, $daysInMonth - $record_dates_count); // One row per day in month
                    for ($i = 0; $i < $blankRows; $i++): ?>
                    <tr>
                        <td>&nbsp;</td>
                        <?php foreach ($equipment_details_list as $eq_id => $eq_details): ?>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
            
            <div class="verification-section">
                <p>Reviewed By (Manager Signature): <span class="signature-line"></span> Date: <span class="signature-line"></span></p>
            </div>
        </div>

    <?php endif; // End if equipment selected ?>

</div> <!-- /container -->

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
// JavaScript to show the log and print when the Print button is clicked
document.getElementById('printButton')?.addEventListener('click', function() {
    document.getElementById('logDisplay').classList.add('show');
    setTimeout(function() {
        window.print();
    }, 300); // Short delay to ensure styles are applied
});
</script>

</body>
</html> 