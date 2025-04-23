<?php
/**
 * Sidebar Template
 * 
 * Common sidebar for all pages in the Geez Restaurant application
 */
?>
<div class="position-sticky pt-3">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link" href="<?php echo BASE_URL; ?>/dashboard.php">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#temperatureMenu" role="button" aria-expanded="false" aria-controls="temperatureMenu">
                <i class="bi bi-thermometer-half"></i> Temperature Checks
            </a>
            <div class="collapse" id="temperatureMenu">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/temperature/add.php">
                            <i class="bi bi-plus-circle"></i> Add Check
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/temperature/view.php">
                            <i class="bi bi-list"></i> View History
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/temperature/report.php">
                            <i class="bi bi-file-earmark-text"></i> Reports
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#cleaningMenu" role="button" aria-expanded="false" aria-controls="cleaningMenu">
                <i class="bi bi-check2-square"></i> Cleaning Logs
            </a>
            <div class="collapse" id="cleaningMenu">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/cleaning/log.php">
                            <i class="bi bi-journal-check"></i> Daily Log
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/cleaning/report.php">
                            <i class="bi bi-file-earmark-text"></i> Reports
                        </a>
                    </li>
                    <?php if (hasRole(['manager', 'admin'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/cleaning/locations.php">
                            <i class="bi bi-geo-alt"></i> Manage Locations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/cleaning/tasks.php">
                            <i class="bi bi-list-check"></i> Manage Tasks
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </li>
        
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#wasteMenu" role="button" aria-expanded="false" aria-controls="wasteMenu">
                <i class="bi bi-trash"></i> Food Waste
            </a>
            <div class="collapse" id="wasteMenu">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/waste/add.php">
                            <i class="bi bi-plus-circle"></i> Log Waste
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/waste/view.php">
                            <i class="bi bi-list"></i> View History
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/waste/report.php">
                            <i class="bi bi-file-earmark-text"></i> Reports
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        
        <?php if (hasRole(['admin'])): ?>
        <li class="nav-item mt-3">
            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                <span>Administration</span>
            </h6>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/users.php">
                <i class="bi bi-people"></i> Manage Users
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/equipment.php">
                <i class="bi bi-tools"></i> Manage Equipment
            </a>
        </li>
        <?php endif; ?>
        
        <hr>
        
        <li class="nav-item">
            <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/auth/logout.php">
                <i class="bi bi-box-arrow-right text-danger"></i> Logout
            </a>
        </li>
    </ul>
</div>
