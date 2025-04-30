<?php
/**
 * Print Management Page
 */

// Set page title
$page_title = 'Printable Reports';

// Include configuration and common functions
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/database.php';
require_once INCLUDE_PATH . '/functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Require login
requireLogin();

// Include header
require_once INCLUDE_PATH . '/header.php';

?>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-printer"></i> Printable Reports</h5>
            </div>
            <div class="card-body">
                <p>Select a report type below to generate a printable version.</p>
            </div>
        </div>
    </div>
</div>

<!-- Batch Monthly Reports Highlight Card -->
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card bg-light border-primary">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="card-title"><i class="bi bi-calendar-month"></i> <strong>Batch Monthly Reports</strong></h5>
                        <p class="card-text">
                            Generate and print monthly reports for Temperature Checklists, Weekly Cleaning, and Food Waste Logs. 
                            Easily organize reports by month from August 2023 to the current date.
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="<?php echo BASE_URL; ?>/batch_monthly_reports.php" class="btn btn-primary">
                            <i class="bi bi-file-earmark-text"></i> Generate Monthly Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Temperature Checklist Card -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title"><i class="bi bi-thermometer-half"></i> Temperature Checklist</h5>
                <p class="card-text">Generate a monthly temperature log sheet for specific equipment.</p>
                <a href="<?php echo BASE_URL; ?>/modules/temperature/print_checklist.php" class="btn btn-primary mt-auto">Go to Temperature Checklist</a>
            </div>
        </div>
    </div>

    <!-- Cleaning Checklist Card -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title"><i class="bi bi-check2-square"></i> Weekly Cleaning Checklist</h5>
                <p class="card-text">Generate a weekly checklist showing completed tasks and user initials for a specific location.</p>
                <a href="<?php echo BASE_URL; ?>/modules/cleaning/print_weekly_checklist.php" class="btn btn-primary mt-auto">Go to Cleaning Checklist</a>
            </div>
        </div>
    </div>

    <!-- Food Waste Log Card -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title"><i class="bi bi-trash"></i> Food Waste Log</h5>
                <p class="card-text">Generate a printable log of recorded food waste incidents within a date range.</p>
                <a href="<?php echo BASE_URL; ?>/modules/waste/print_log.php" class="btn btn-primary mt-auto">Go to Food Waste Log</a>
            </div>
        </div>
    </div>

    <!-- Add more report cards here as needed -->
    <!-- 
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title"><i class="bi bi-check2-square"></i> Cleaning Log</h5>
                <p class="card-text">Generate printable cleaning logs.</p>
                <a href="#" class="btn btn-secondary disabled mt-auto">Coming Soon</a>
            </div>
        </div>
    </div>
    -->

</div>

<?php
// Include footer
require_once INCLUDE_PATH . '/footer.php';
?> 