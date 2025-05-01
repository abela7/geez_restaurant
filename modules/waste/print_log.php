<?php
/**
 * Printable Food Waste Log
 */

// Include configuration and common functions
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/database.php';
require_once INCLUDE_PATH . '/functions.php';

// Set timezone
date_default_timezone_set('Europe/London');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Initialize database connection
require_once CLASS_PATH . '/Database.php';
$db = new Database();

// Initialize relevant classes
require_once CLASS_PATH . '/FoodWasteLog.php';
$waste_log_model = new FoodWasteLog($db);

// Require login
requireLogin();

// --- Get Parameters --- 
// Default to the current week if no dates are provided
$today = date('Y-m-d');
$start_date_str = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('monday this week', strtotime($today)));
$end_date_str = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d', strtotime('sunday this week', strtotime($today)));

// Basic validation for dates
$start_date = date('Y-m-d', strtotime($start_date_str));
$end_date = date('Y-m-d', strtotime($end_date_str));

// --- Data Fetching & Preparation ---
$waste_logs = $waste_log_model->getAll($start_date, $end_date);
$total_cost_period = 0;
foreach($waste_logs as $log) {
    $total_cost_period += $log['total_cost'] ?? 0;
}

// Function to get user initials
function getWasteUserInitials($user_id, $db) {
    if (!$user_id) return 'N/A';
    $user_data = $db->fetchRow("SELECT full_name FROM users WHERE user_id = ?", [$user_id]);
    if ($user_data && isset($user_data['full_name']) && !empty($user_data['full_name'])) {
        $parts = explode(' ', trim($user_data['full_name']));
        $initials = '';
        if (isset($parts[0][0])) $initials .= strtoupper($parts[0][0]);
        if (count($parts) > 1 && isset($parts[count($parts)-1][0])) $initials .= strtoupper($parts[count($parts)-1][0]);
        return $initials ?: 'N/A';
    }
    return 'N/A';
}

// --- Page Setup --- 
$page_title = 'Food Waste Log (' . formatDate($start_date, 'd/m/y') . ' - ' . formatDate($end_date, 'd/m/y') . ')';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo ASSET_URL; ?>/css/custom.css">
    
    <style>
        /* Print Styles */
        @media print {
            body { 
                font-size: 9pt; 
                -webkit-print-color-adjust: exact !important; 
                color-adjust: exact !important; 
                background-color: white !important;
            }
            /* Adjust margins to minimize browser header/footer space */
            @page { 
                margin: 0.5in; /* Default margin for content */
                /* Optionally try reducing top/bottom further if needed */
                /* margin-top: 0.25in; */
                /* margin-bottom: 0.25in; */
            }
            /* Hide everything marked with no-print class */
            .no-print {
                display: none !important;
            }
            /* Hide nav, header, footer, etc. */
            nav, header, footer, .sidebar, .navbar, .nav {
                display: none !important;
            }
            /* Top level container takes full width */
            .container-fluid, .container {
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            /* Table styles */
            .table td, .table th { 
                padding: 0.2rem 0.4rem; 
                vertical-align: middle; 
                font-size: 8pt;
                border-color: #000 !important;
            }
            .table thead th { 
                background-color: #e9ecef !important; 
                font-weight: bold; 
                text-align: center;
            }
            /* Headers */
            h1, h2, h3, h4, h5, h6 { 
                margin-top: 0; 
                margin-bottom: 0.5rem; 
            }
            /* Remove link styling */
            a[href]:after { 
                content: none !important; 
            }
            /* Header table styles */
            .printable-header {
                margin-bottom: 15px !important;
            }
            .printable-header td { 
                border: none !important; 
                padding: 2px 5px; 
                font-size: 10pt; 
            }
            .summary-table td { 
                border: none !important; 
                padding: 1px 5px; 
                font-size: 10pt; 
            }
            .totals-row td { 
                font-weight: bold; 
                border-top: 2px solid black !important; 
            }
            /* Page break behavior */
            .page-break-before { 
                page-break-before: always; 
            }
            .avoid-break { 
                page-break-inside: avoid; 
            }
        }
        /* Screen Styles */
        .waste-log-table { margin-top: 20px; font-size: 0.9rem; }
        .waste-log-table thead th { text-align: center; vertical-align: middle; }
        .waste-log-table tbody td { text-align: center; vertical-align: middle; }
        .waste-log-table .text-col { text-align: left !important; }
        .waste-log-table .num-col { text-align: right !important; padding-right: 5px !important; }
        .header-details-table td { border: none; padding: 5px; }
    </style>
</head>
<body>

<div class="container-fluid mt-4"> <!-- Use container-fluid for wider layout -->

    <!-- Selection Form (No Print) -->
    <div class="no-print mb-4 p-3 border rounded bg-light">
        <h4>Generate Food Waste Log</h4>
        <form method="get" action="" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Start Date:</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date_str; ?>" required>
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">End Date:</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date_str; ?>" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Generate Log</button>
            </div>
            <div class="col-md-2">
                 <button onclick="window.print()" class="btn btn-success w-100"><i class="bi bi-printer"></i> Print Log</button>
            </div>
        </form>
    </div>

    <!-- Printable Area -->
    <div class="printable-header mb-3">
         <h3 class="text-center">Food Waste Log</h3>
         <table class="header-details-table w-100">
             <tr>
                 <td><strong>Period:</strong> <?php echo formatDate($start_date, 'd/m/y'); ?> - <?php echo formatDate($end_date, 'd/m/y'); ?></td>
                 <td><strong>Facility:</strong> <?php echo APP_NAME; /* Or other identifier */ ?></td>
             </tr>
         </table>
    </div>

    <table class="table table-bordered table-sm waste-log-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th class="text-col">Item Description</th>
                <th class="text-col">Reason For Waste</th>
                <th>Qty</th>
                <th class="num-col">Cost/Unit</th>
                <th class="num-col">Total Cost</th>
                <th class="text-col">Recorded By</th>
                <th>Initials</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($waste_logs)): ?>
                <tr>
                    <td colspan="9" class="text-center text-muted">No food waste recorded for this period.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($waste_logs as $log): ?>
                <tr>
                    <td><?php echo formatDate($log['waste_date'] ?? '', 'd/m/y'); ?></td>
                    <td><?php echo formatDateTime($log['waste_timestamp'] ?? '', 'H:i'); ?></td>
                    <td class="text-col"><?php echo htmlspecialchars($log['item_description'] ?? ''); ?></td>
                    <td class="text-col"><?php echo htmlspecialchars($log['reason_for_waste'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($log['quantity'] ?? '0'); ?> <?php echo htmlspecialchars($log['unit_of_measure'] ?? 'kg'); ?></td>
                    <td class="num-col"><?php echo isset($log['cost_per_unit']) ? formatCurrency($log['cost_per_unit']) : 'N/A'; ?></td>
                    <td class="num-col"><?php echo formatCurrency($log['total_cost'] ?? 0); ?></td>
                    <td class="text-col"><?php echo htmlspecialchars($log['recorded_by'] ?? 'N/A'); ?></td>
                    <td><?php echo getWasteUserInitials($log['recorded_by_user_id'], $db); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
             <?php 
             // Add blank rows if needed for manual entries
             $rowCount = count($waste_logs);
             $minRows = 15; // Adjust as needed for print layout
             $blankRows = max(0, $minRows - $rowCount);
             for ($i = 0; $i < $blankRows; $i++):
             ?>
             <tr>
                <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
             </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <!-- Footer for printed page -->
    <div class="mt-4 avoid-break">
        <p class="small text-muted">Geez Restaurant Management System</p>
    </div>

</div> <!-- /container -->

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html> 