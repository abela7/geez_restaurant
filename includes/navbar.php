<?php
/**
 * Navigation Bar Template
 * 
 * Common navigation bar for all pages in the Geez Restaurant application
 */
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <button class="navbar-toggler me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mainOffcanvas" aria-controls="mainOffcanvas" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>/index.php">
            <i class="bi bi-shop"></i> Geez Restaurant
        </a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if ($is_logged_in): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/dashboard.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="tempDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-thermometer-half"></i> Temperature
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="tempDropdown">
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/modules/temperature/index.php">Overview</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/modules/temperature/add.php">Add Check</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/modules/temperature/view.php">View History</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/modules/temperature/report.php">Reports</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="cleaningDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-check2-square"></i> Cleaning
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="cleaningDropdown">
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/modules/cleaning/index.php">Overview</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/modules/cleaning/log.php">Daily Log</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/modules/cleaning/report.php">Reports</a></li>
                        <?php if (hasRole(['manager', 'admin'])): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/modules/cleaning/locations.php">Manage Locations</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/modules/cleaning/tasks.php">Manage Tasks</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="wasteDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-trash"></i> Food Waste
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="wasteDropdown">
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/modules/waste/index.php">Overview</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/modules/waste/add.php">Log Waste</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/modules/waste/view.php">View History</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/modules/waste/report.php">Reports</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/print/index.php">
                        <i class="bi bi-printer"></i> Print Reports
                    </a>
                </li>
                <?php if (hasRole(['admin'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-gear"></i> Admin
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/users.php">Manage Users</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/equipment.php">Manage Equipment</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/settings.php"><i class="bi bi-gear"></i> System Settings</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if ($is_logged_in): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> <?php echo $_SESSION['full_name']; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/modules/auth/profile.php">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/modules/auth/logout.php">Logout</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/auth/login.php">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
