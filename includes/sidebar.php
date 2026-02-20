<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark sidebar" style="width: 280px; min-height: 100vh;">
    <a href="dashboard.php" class="d-flex align-items-center mb-1 text-white text-decoration-none">
        <i class="fas fa-fish me-2 fs-5"></i>
        <div>
            <span class="fs-5 fw-bold">Harini Aquarium</span>
            <small class="d-block text-secondary" style="font-size:0.7rem;">Fish &amp; Aquarium Shop</small>
        </div>
    </a>
    <small class="text-secondary mb-1" style="font-size:0.7rem;">
        <i class="fas fa-map-marker-alt me-1"></i>Manjakuppam, Cuddalore – 607001
    </small>
    <hr>

    <!-- Role badge -->
    <?php if (isset($_SESSION['role'])): ?>
    <div class="mb-2">
        <span class="badge <?php echo $_SESSION['role'] === 'admin' ? 'bg-danger' : 'bg-primary'; ?> w-100 py-1">
            <i class="fas <?php echo $_SESSION['role'] === 'admin' ? 'fa-crown' : 'fa-user'; ?> me-1"></i>
            <?php echo ucfirst($_SESSION['role']); ?> Panel
        </span>
    </div>
    <?php endif; ?>

    <ul class="nav nav-pills flex-column mb-auto">

        <!-- ① Overview -->
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link text-white <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="reports.php" class="nav-link text-white <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line me-2"></i> Reports
            </a>
        </li>

        <li><hr class="text-secondary my-1"></li>

        <!-- ② Business -->
        <li>
            <a href="sales.php" class="nav-link text-white <?php echo ($current_page == 'sales.php' || $current_page == 'sales_new.php') ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart me-2"></i> Sales
            </a>
        </li>
        <li>
            <a href="purchases.php" class="nav-link text-white <?php echo ($current_page == 'purchases.php' || $current_page == 'purchases_new.php' || $current_page == 'purchase_view.php') ? 'active' : ''; ?>">
                <i class="fas fa-truck me-2"></i> Purchases
            </a>
        </li>
        <li>
            <a href="customers.php" class="nav-link text-white <?php echo $current_page == 'customers.php' ? 'active' : ''; ?>">
                <i class="fas fa-users me-2"></i> Customers
            </a>
        </li>
        <li>
            <a href="suppliers.php" class="nav-link text-white <?php echo $current_page == 'suppliers.php' ? 'active' : ''; ?>">
                <i class="fas fa-industry me-2"></i> Suppliers
            </a>
        </li>

        <li><hr class="text-secondary my-1"></li>

        <!-- ③ Aquarium Operations -->
        <li>
            <a href="tanks.php" class="nav-link text-white <?php echo ($current_page == 'tanks.php' || $current_page == 'tanks_edit.php') ? 'active' : ''; ?>">
                <i class="fas fa-box me-2"></i> Tanks
            </a>
        </li>
        <li>
            <a href="fish.php" class="nav-link text-white <?php echo ($current_page == 'fish.php' || $current_page == 'fish_edit.php') ? 'active' : ''; ?>">
                <i class="fas fa-fish me-2"></i> Fish Stock
            </a>
        </li>
        <li>
            <a href="fish_health.php" class="nav-link text-white <?php echo $current_page == 'fish_health.php' ? 'active' : ''; ?>">
                <i class="fas fa-heartbeat me-2"></i> Fish Health
            </a>
        </li>
        <li>
            <a href="water_quality.php" class="nav-link text-white <?php echo $current_page == 'water_quality.php' ? 'active' : ''; ?>">
                <i class="fas fa-tint me-2"></i> Water Quality
            </a>
        </li>
        <li>
            <a href="feeding.php" class="nav-link text-white <?php echo $current_page == 'feeding.php' ? 'active' : ''; ?>">
                <i class="fas fa-drumstick-bite me-2"></i> Feeding
            </a>
        </li>
        <li>
            <a href="maintenance.php" class="nav-link text-white <?php echo $current_page == 'maintenance.php' ? 'active' : ''; ?>">
                <i class="fas fa-tools me-2"></i> Maintenance
            </a>
        </li>

        <li><hr class="text-secondary my-1"></li>

        <!-- ④ Admin -->
        <li>
            <a href="employees.php" class="nav-link text-white <?php echo $current_page == 'employees.php' ? 'active' : ''; ?>">
                <i class="fas fa-id-badge me-2"></i> Employees
            </a>
        </li>
        <li>
            <a href="expenses.php" class="nav-link text-white <?php echo $current_page == 'expenses.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar me-2"></i> Expenses
            </a>
        </li>
        <li>
            <a href="notifications.php" class="nav-link text-white <?php echo $current_page == 'notifications.php' ? 'active' : ''; ?>">
                <i class="fas fa-bell me-2"></i> Notifications
                <?php
                if (isset($conn)) {
                    $unread_res = $conn->query("SELECT COUNT(*) as c FROM notifications WHERE is_read = 0");
                    if ($unread_res) {
                        $unread_count = $unread_res->fetch_assoc()['c'];
                        if ($unread_count > 0) {
                            echo '<span class="badge bg-danger ms-2">' . $unread_count . '</span>';
                        }
                    }
                }
                ?>
            </a>
        </li>

    </ul>
    <hr>
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1"
            data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-user-circle me-2"></i>
            <strong><?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : htmlspecialchars($_SESSION['username'] ?? 'User'); ?></strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Sign out</a></li>
        </ul>
    </div>
</div>
