<div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark sidebar" style="width: 280px;">
    <a href="dashboard.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4">Aquamart</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="dashboard.php"
                class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="tanks.php" class="nav-link text-white">
                <i class="fas fa-box me-2"></i> Tanks
            </a>
        </li>
        <li>
            <a href="fish.php" class="nav-link text-white">
                <i class="fas fa-fish me-2"></i> Fish Stock
            </a>
        </li>
        <li>
            <a href="water_quality.php" class="nav-link text-white">
                <i class="fas fa-tint me-2"></i> Water Quality
            </a>
        </li>
        <li>
            <a href="sales.php" class="nav-link text-white">
                <i class="fas fa-shopping-cart me-2"></i> Sales
            </a>
        </li>
        <li>
            <a href="purchases.php" class="nav-link text-white">
                <i class="fas fa-truck me-2"></i> Purchases
            </a>
        </li>
        <li>
            <a href="expenses.php" class="nav-link text-white">
                <i class="fas fa-file-invoice-dollar me-2"></i> Expenses
            </a>
        </li>
        <li>
            <a href="reports.php" class="nav-link text-white">
                <i class="fas fa-chart-line me-2"></i> Reports
            </a>
        </li>
    </ul>
    <hr>
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1"
            data-bs-toggle="dropdown" aria-expanded="false">
            <strong>
                <?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'User'; ?>
            </strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
            <li><a class="dropdown-item" href="logout.php">Sign out</a></li>
        </ul>
    </div>
</div>