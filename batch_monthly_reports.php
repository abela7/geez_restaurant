<?php
/**
 * Batch Monthly Reports Generator
 * Allows printing monthly reports for Temperature Checks, Weekly Cleaning, and Food Waste
 */

// Include configuration and common functions
require_once 'config/config.php';
require_once 'config/database.php';
require_once INCLUDE_PATH . '/functions.php';

// Set timezone
date_default_timezone_set('Europe/London');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Require login
requireLogin();

// Get the current date and time
$current_date = new DateTime();

// Set default date range (August 2023 to current month)
$default_start = new DateTime('2023-08-01');
$default_end = clone $current_date;
$default_end->modify('last day of this month');

// Get user selected date range if provided
$start_date_str = isset($_GET['start_date']) ? $_GET['start_date'] : $default_start->format('Y-m-d');
$end_date_str = isset($_GET['end_date']) ? $_GET['end_date'] : $default_end->format('Y-m-d');

// Convert to DateTime objects
$start_date = new DateTime($start_date_str);
$start_date->setDate($start_date->format('Y'), $start_date->format('m'), 1); // Set to first day of month
$end_date = new DateTime($end_date_str);
$end_date->modify('last day of this month'); // Set to last day of month

// Calculate all months between start and end dates
$months = [];
$month_iterator = clone $start_date;

while ($month_iterator <= $end_date) {
    $month_year = $month_iterator->format('Y-m');
    $month_name = $month_iterator->format('F Y');
    
    $month_start = clone $month_iterator;
    $month_end = clone $month_iterator;
    $month_end->modify('last day of this month');
    
    $months[$month_year] = [
        'name' => $month_name,
        'start' => $month_start->format('Y-m-d'),
        'end' => $month_end->format('Y-m-d')
    ];
    
    // Move to next month
    $month_iterator->modify('first day of next month');
}

// Page title
$page_title = 'Batch Monthly Reports Generator';

// Handle download request if made
$download_requested = isset($_GET['download']) && $_GET['download'] === 'true';
$download_type = isset($_GET['type']) ? $_GET['type'] : '';
$download_month = isset($_GET['month']) ? $_GET['month'] : '';

if ($download_requested && !empty($download_type) && !empty($download_month) && isset($months[$download_month])) {
    $month_data = $months[$download_month];
    
    switch ($download_type) {
        case 'temperature':
            header('Location: modules/temperature/print_checklist.php?start_date=' . $month_data['start'] . '&end_date=' . $month_data['end']);
            exit;
        case 'cleaning':
            header('Location: modules/cleaning/print_weekly_checklist.php?start_date=' . $month_data['start'] . '&end_date=' . $month_data['end']);
            exit;
        case 'waste':
            header('Location: modules/waste/print_log.php?start_date=' . $month_data['start'] . '&end_date=' . $month_data['end']);
            exit;
    }
}

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
        .report-section {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .report-section h3 {
            margin-top: 0;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .month-links {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }
        .month-link {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #fff;
            text-decoration: none;
            color: #333;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }
        .month-link:hover {
            background-color: #f1f1f1;
            border-color: #aaa;
        }
        .month-link i {
            margin-right: 8px;
        }
        .instructions {
            background-color: #e8f4f8;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .instructions ol {
            margin-bottom: 0;
            padding-left: 20px;
        }
    </style>
</head>
<body>

<div class="container py-4">
    <h1><?php echo $page_title; ?></h1>
    
    <div class="instructions mb-4">
        <h5><i class="bi bi-info-circle"></i> Instructions</h5>
        <ol>
            <li>Select the date range for your monthly reports (August 2023 to current date by default)</li>
            <li>Click on any month link in each section to open the printable report for that month</li>
            <li>Use your browser's print function (Ctrl+P or ⌘+P) to print the report</li>
            <li>Organize the printed reports by type and month in a folder</li>
        </ol>
    </div>
    
    <!-- Date Range Selection Form -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Select Date Range</h5>
            <form method="get" action="" class="row g-3">
                <div class="col-md-5">
                    <label for="start_date" class="form-label">From Month:</label>
                    <input type="month" class="form-control" id="start_date" name="start_date" 
                           value="<?php echo $start_date->format('Y-m'); ?>" required>
                </div>
                <div class="col-md-5">
                    <label for="end_date" class="form-label">To Month:</label>
                    <input type="month" class="form-control" id="end_date" name="end_date" 
                           value="<?php echo $end_date->format('Y-m'); ?>" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Update Range</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Temperature Checklist Section -->
    <div class="report-section">
        <h3><i class="bi bi-thermometer-half"></i> Temperature Checklist Reports</h3>
        <div class="month-links">
            <?php foreach ($months as $month_key => $month_data): ?>
            <a href="?download=true&type=temperature&month=<?php echo $month_key; ?>" 
               class="month-link" target="_blank">
                <i class="bi bi-file-earmark-text"></i> <?php echo $month_data['name']; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Weekly Cleaning Checklist Section -->
    <div class="report-section">
        <h3><i class="bi bi-check2-square"></i> Weekly Cleaning Checklist Reports</h3>
        <div class="month-links">
            <?php foreach ($months as $month_key => $month_data): ?>
            <a href="?download=true&type=cleaning&month=<?php echo $month_key; ?>" 
               class="month-link" target="_blank">
                <i class="bi bi-file-earmark-text"></i> <?php echo $month_data['name']; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Food Waste Log Section -->
    <div class="report-section">
        <h3><i class="bi bi-trash"></i> Food Waste Log Reports</h3>
        <div class="month-links">
            <?php foreach ($months as $month_key => $month_data): ?>
            <a href="?download=true&type=waste&month=<?php echo $month_key; ?>" 
               class="month-link" target="_blank">
                <i class="bi bi-file-earmark-text"></i> <?php echo $month_data['name']; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Batch Print Options -->
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">Batch Printing Options</h5>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 
                To print all reports for a specific month, click on each type of report for that month in separate tabs, 
                then print each one using your browser's print function.
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <a href="javascript:void(0);" onclick="printAllType('temperature')" class="btn btn-outline-primary w-100">
                        <i class="bi bi-printer"></i> Open All Temperature Reports
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="javascript:void(0);" onclick="printAllType('cleaning')" class="btn btn-outline-primary w-100">
                        <i class="bi bi-printer"></i> Open All Cleaning Reports
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="javascript:void(0);" onclick="printAllType('waste')" class="btn btn-outline-primary w-100">
                        <i class="bi bi-printer"></i> Open All Waste Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Function to open all reports of a specific type in new tabs
    function printAllType(type) {
        const links = document.querySelectorAll(`a[href*="type=${type}"]`);
        links.forEach((link, index) => {
            // Delay opening tabs to prevent browser blocking
            setTimeout(() => {
                window.open(link.href, '_blank');
            }, index * 300);
        });
    }
</script>

</body>
</html> 