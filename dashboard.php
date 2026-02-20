<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
if ($_SESSION['role'] === 'customer') {
    header("Location: shop.php");
    exit();
}
require_once 'config/db_connect.php';
// Fetch simple stats
$fish_count = $conn->query("SELECT SUM(stock_quantity) as total FROM fish")->fetch_assoc()['total'] ?? 0;
$tanks_count = $conn->query("SELECT COUNT(*) as total FROM tanks")->fetch_assoc()['total'] ?? 0;
$sales_today = $conn->query("SELECT SUM(final_amount) as total FROM sales WHERE DATE(sale_date) = CURDATE()")->fetch_assoc()['total'] ?? 0;
$alerts_count = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE is_read = 0")->fetch_assoc()['total'] ?? 0;

include 'includes/header.php';
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4">
        <h2>Dashboard</h2>
        <p class="text-muted">Welcome,
            <?php echo $_SESSION['username']; ?>!
        </p>

        <div class="row mt-4">
            <!-- Fish Stock -->
            <div class="col-md-3 mb-4">
                <div class="card card-custom bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Fish</h5>
                        <h2 class="display-6">
                            <?php echo $fish_count; ?>
                        </h2>
                        <a href="fish.php" class="text-white">View Details <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Active Tanks -->
            <div class="col-md-3 mb-4">
                <div class="card card-custom bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Active Tanks</h5>
                        <h2 class="display-6">
                            <?php echo $tanks_count; ?>
                        </h2>
                        <a href="tanks.php" class="text-white">View Details <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Today's Sales -->
            <div class="col-md-3 mb-4">
                <div class="card card-custom bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Today's Sales</h5>
                        <h2 class="display-6">&#8377;<?php echo number_format($sales_today, 2); ?>
                        </h2>
                        <a href="sales.php" class="text-white">View Details <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            <div class="col-md-3 mb-4">
                <div class="card card-custom bg-danger text-white">
                    <div class="card-body">
                        <h5 class="card-title">Alerts</h5>
                        <h2 class="display-6">
                            <?php echo $alerts_count; ?>
                        </h2>
                        <a href="notifications.php" class="text-white">View Details <i
                                class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card card-custom">
                    <div class="card-header">Quick Actions</div>
                    <div class="card-body">
                        <a href="sales_new.php" class="btn btn-outline-primary w-100 mb-2">New Sale</a>
                        <a href="purchases_new.php" class="btn btn-outline-success w-100 mb-2">New Purchase</a>
                        <a href="feeding.php" class="btn btn-outline-warning w-100">Log Feeding</a>
                    </div>
                </div>
            </div>
            <!-- Can add charts here later -->
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>