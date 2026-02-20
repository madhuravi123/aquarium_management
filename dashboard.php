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
// Alerts = low stock + sick fish + non-active tanks (same formula as api_dashboard.php)
$alert_low    = (int)($conn->query("SELECT COUNT(*) as t FROM fish WHERE stock_quantity <= 5")->fetch_assoc()['t'] ?? 0);
$alert_sick   = (int)($conn->query("SELECT COUNT(*) as t FROM fish_health WHERE status IN ('sick','recovering')")->fetch_assoc()['t'] ?? 0);
$alert_tanks  = (int)($conn->query("SELECT COUNT(*) as t FROM tanks WHERE status != 'active'")->fetch_assoc()['t'] ?? 0);
$alerts_count = $alert_low + $alert_sick + $alert_tanks;

include 'includes/header.php';
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2>Dashboard</h2>
                <p class="text-muted mb-0">Welcome, <?php echo $_SESSION['username']; ?>!</p>
            </div>
            <div class="text-end">
                <span class="live-indicator">Live</span>
                <div id="last-updated" class="mt-1">Last updated: —</div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Fish Stock -->
            <div class="col-md-3 mb-4">
                <div class="card card-custom bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Fish</h5>
                        <h2 class="display-6" id="fish-count">
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
                        <h2 class="display-6" id="tanks-count">
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
                        <h2 class="display-6" id="sales-today">&#8377;<?php echo number_format($sales_today, 2); ?></h2>
                        <a href="sales.php" class="text-white">View Details <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            <div class="col-md-3 mb-4">
                <div class="card card-custom bg-danger text-white">
                    <div class="card-body">
                        <h5 class="card-title">Alerts</h5>
                        <h2 class="display-6" id="alerts-count">
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

<!-- Live Update Script -->
<script>
(function() {
    // Configuration
    const UPDATE_INTERVAL = 30000; // 30 seconds
    let updateTimer = null;

    // Format currency
    function formatCurrency(amount) {
        return '₹' + parseFloat(amount).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Update dashboard data
    function updateDashboard() {
        fetch('api_dashboard.php')
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Dashboard update error:', data.error);
                    return;
                }

                // Update stat cards with animation
                updateStatValue('fish-count', data.fish_count);
                updateStatValue('tanks-count', data.tanks_count);
                updateStatCurrency('sales-today', data.sales_today);
                updateStatValue('alerts-count', data.alerts_count);

                // Update last updated timestamp
                const lastUpdatedEl = document.getElementById('last-updated');
                if (lastUpdatedEl) {
                    lastUpdatedEl.textContent = 'Last updated: ' + data.last_updated;
                }

                // Update operation badges if they exist
                updateOperationBadges(data);
            })
            .catch(error => {
                console.error('Failed to update dashboard:', error);
            });
    }

    // Update stat value with highlight effect
    function updateStatValue(elementId, newValue) {
        const element = document.getElementById(elementId);
        if (element && element.textContent != newValue) {
            element.classList.add('stat-updated');
            element.textContent = newValue;
            setTimeout(() => element.classList.remove('stat-updated'), 1000);
        }
    }

    // Update currency value with highlight effect
    function updateStatCurrency(elementId, newValue) {
        const element = document.getElementById(elementId);
        if (element) {
            const newFormatted = formatCurrency(newValue);
            if (element.textContent !== newFormatted) {
                element.classList.add('stat-updated');
                element.textContent = newFormatted;
                setTimeout(() => element.classList.remove('stat-updated'), 1000);
            }
        }
    }

    // Update operation badges
    function updateOperationBadges(data) {
        const badges = {
            'badge-pending-feedings': data.pending_feedings,
            'badge-maintenance': data.maintenance_tanks + data.upcoming_maintenance,
            'badge-sick-fish': data.sick_fish,
            'badge-low-stock': data.low_stock_count
        };

        for (const [id, value] of Object.entries(badges)) {
            const badge = document.getElementById(id);
            if (badge) {
                if (value > 0) {
                    badge.textContent = value;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }
        }
    }

    // Start auto-update
    function startAutoUpdate() {
        updateDashboard(); // Initial update
        updateTimer = setInterval(updateDashboard, UPDATE_INTERVAL);
    }

    // Stop auto-update
    function stopAutoUpdate() {
        if (updateTimer) {
            clearInterval(updateTimer);
            updateTimer = null;
        }
    }

    // Handle visibility change (pause when tab is hidden)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopAutoUpdate();
        } else {
            startAutoUpdate();
            updateDashboard(); // Immediate update when tab becomes visible
        }
    });

    // Live clock — updates every second so "Last updated" always shows current time
    function tickClock() {
        const el = document.getElementById('last-updated');
        if (!el) return;
        const now = new Date();
        const opts = { day: '2-digit', month: 'short', year: 'numeric',
                       hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true };
        el.textContent = 'Last updated: ' + now.toLocaleString('en-IN', opts);
    }
    setInterval(tickClock, 1000);
    tickClock(); // run immediately on load

    // Start on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startAutoUpdate);
    } else {
        startAutoUpdate();
    }
})();
</script>

<style>
/* Live update animation */
.stat-updated {
    animation: highlightUpdate 1s ease;
}

@keyframes highlightUpdate {
    0% { color: #14d2c8; transform: scale(1.1); }
    100% { color: inherit; transform: scale(1); }
}

/* Operation badges */
.operation-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.live-indicator {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.75rem;
    color: #28a745;
}

.live-indicator::before {
    content: '';
    width: 8px;
    height: 8px;
    background: #28a745;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

#last-updated {
    font-size: 0.75rem;
    color: #6c757d;
}
</style>

<?php include 'includes/footer.php'; ?>