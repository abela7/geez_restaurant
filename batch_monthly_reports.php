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

// Initialize database connection
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

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
            // Add default equipment_ids parameter
            $equipment_ids = isset($_GET['equipment_ids']) ? $_GET['equipment_ids'] : [];
            
            // If specific equipment IDs are provided, use them
            // Otherwise, we need to convert month format to the expected 'month' parameter
            $month_param = date('Y-m', strtotime($month_data['start']));
            
            // Redirect to temperature checklist with proper month parameter
            header('Location: modules/temperature/print_checklist.php?month=' . $month_param . (empty($equipment_ids) ? '' : '&equipment_ids[]=' . implode('&equipment_ids[]=', $equipment_ids)));
            exit;
            
        case 'cleaning':
            // Handle cleaning checklist (it expects week parameter)
            $week_requested = isset($_GET['week']) ? $_GET['week'] : '';
            $location_id = isset($_GET['location_id']) ? $_GET['location_id'] : '';
            
            if (!empty($week_requested)) {
                // If a specific week is requested
                header('Location: modules/cleaning/print_weekly_checklist.php?week=' . $week_requested . (!empty($location_id) ? '&location_id=' . $location_id : ''));
            } else {
                // Default to the monthly view (which will show the form)
                header('Location: modules/cleaning/print_weekly_checklist.php?start_date=' . $month_data['start'] . '&end_date=' . $month_data['end'] . (!empty($location_id) ? '&location_id=' . $location_id : ''));
            }
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
        
        <?php
        // Get all active equipment for the dropdown
        $equipmentQuery = "SELECT equipment_id, name, location FROM equipment WHERE is_active = 1 ORDER BY name";
        $equipmentStmt = $conn->prepare($equipmentQuery);
        $equipmentStmt->execute();
        $temperature_equipment = $equipmentStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get selected equipment IDs from GET parameters
        $selected_equipment_ids = isset($_GET['equipment_ids']) ? (array)$_GET['equipment_ids'] : [];
        ?>

        <div class="card mb-3">
            <div class="card-body">
                <form method="get" action="" class="row g-3">
                    <div class="col-md-8">
                        <label for="equipment_ids" class="form-label">Select Equipment (Optional):</label>
                        <select class="form-select" id="equipment_ids" name="equipment_ids[]" multiple>
                            <?php foreach ($temperature_equipment as $equip): ?>
                            <option value="<?php echo $equip['equipment_id']; ?>" 
                                <?php echo in_array($equip['equipment_id'], $selected_equipment_ids) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($equip['name']); ?> 
                                (<?php echo htmlspecialchars($equip['location'] ?? 'N/A'); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple equipment. Leave empty to select later.</small>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary">Update Selection</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <?php foreach ($months as $month_key => $month_data): ?>
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo $month_data['name']; ?></h5>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <p>Temperature checklist for the month of <?php echo $month_data['name']; ?>.</p>
                        
                        <?php 
                        // Create parameter string for equipment IDs
                        $equipment_params = '';
                        if (!empty($selected_equipment_ids)) {
                            foreach ($selected_equipment_ids as $equip_id) {
                                $equipment_params .= '&equipment_ids[]=' . $equip_id;
                            }
                        }
                        ?>
                        
                        <a href="?download=true&type=temperature&month=<?php echo $month_key; ?><?php echo $equipment_params; ?>" 
                           class="btn btn-primary mt-auto" target="_blank">
                            <i class="bi bi-file-earmark-text"></i> View <?php echo $month_data['name']; ?> Checklist
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Weekly Cleaning Checklist Section -->
    <div class="report-section">
        <h3><i class="bi bi-check2-square"></i> Weekly Cleaning Checklist Reports</h3>

        <?php
        // Get all locations for the dropdown
        $locationQuery = "SELECT location_id, name FROM cleaning_locations WHERE is_active = 1 ORDER BY name";
        $locationStmt = $conn->prepare($locationQuery);
        $locationStmt->execute();
        $cleaning_locations = $locationStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Default selected location
        $selected_location_id = isset($_GET['location_id']) ? $_GET['location_id'] : (count($cleaning_locations) > 0 ? $cleaning_locations[0]['location_id'] : null);
        ?>

        <div class="card mb-3">
            <div class="card-body">
                <form method="get" action="" class="row g-3">
                    <div class="col-md-5">
                        <label for="location_id" class="form-label">Select Location:</label>
                        <select class="form-select" id="location_id" name="location_id">
                            <?php foreach ($cleaning_locations as $loc): ?>
                            <option value="<?php echo $loc['location_id']; ?>" 
                                <?php echo ($selected_location_id == $loc['location_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($loc['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary">Update Location</button>
                    </div>
                </form>
            </div>
        </div>

        <?php foreach ($months as $month_key => $month_data): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo $month_data['name']; ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php
                        // Calculate all weeks in this month
                        $start = new DateTime($month_data['start']);
                        $end = new DateTime($month_data['end']);
                        $interval = new DateInterval('P1D'); // 1 day interval
                        $dateRange = new DatePeriod($start, $interval, $end->modify('+1 day'));
                        
                        // Group dates by week
                        $weeks = [];
                        foreach ($dateRange as $date) {
                            $year = $date->format('Y');
                            $week = $date->format('W');
                            $week_key = $year . '-W' . $week;
                            $week_start = clone $date;
                            // Go to Monday of this week
                            $week_start->modify('Monday this week');
                            $week_end = clone $week_start;
                            $week_end->modify('+6 days'); // To Sunday
                            
                            if (!isset($weeks[$week_key])) {
                                $weeks[$week_key] = [
                                    'week' => $week_key,
                                    'start' => $week_start->format('Y-m-d'),
                                    'end' => $week_end->format('Y-m-d'),
                                    'name' => 'Week ' . $week . ' (' . $week_start->format('M d') . ' - ' . $week_end->format('M d') . ')'
                                ];
                            }
                        }
                        
                        // Sort weeks
                        ksort($weeks);
                        
                        foreach ($weeks as $week_key => $week_data):
                            // Only include weeks that have at least one day in this month
                            $week_start = new DateTime($week_data['start']);
                            $week_end = new DateTime($week_data['end']);
                            $month_start = new DateTime($month_data['start']);
                            $month_end = new DateTime($month_data['end']);
                            
                            if (($week_start <= $month_end) && ($week_end >= $month_start)):
                        ?>
                            <div class="col-md-6 mb-2">
                                <a href="?download=true&type=cleaning&month=<?php echo $month_key; ?>&week=<?php echo $week_key; ?>&location_id=<?php echo $selected_location_id; ?>" 
                                   class="btn btn-outline-secondary w-100 text-start" target="_blank">
                                    <i class="bi bi-calendar-week"></i> <?php echo $week_data['name']; ?>
                                </a>
                            </div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Food Waste Log Section -->
    <div class="report-section">
        <h3><i class="bi bi-trash"></i> Food Waste Log Reports</h3>
        
        <div class="row">
            <?php foreach ($months as $month_key => $month_data): ?>
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo $month_data['name']; ?></h5>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <p>Food waste log entries from <?php echo date('M d', strtotime($month_data['start'])); ?> to <?php echo date('M d', strtotime($month_data['end'])); ?>.</p>
                        
                        <a href="?download=true&type=waste&month=<?php echo $month_key; ?>" 
                           class="btn btn-primary mt-auto" target="_blank">
                            <i class="bi bi-file-earmark-text"></i> View <?php echo $month_data['name']; ?> Waste Log
                        </a>
                    </div>
                </div>
            </div>
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