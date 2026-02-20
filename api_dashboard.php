<?php
session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SESSION['role'] === 'customer') {
    echo json_encode(['error' => 'Access denied']);
    exit();
}

require_once 'config/db_connect.php';

// Fetch live dashboard stats
$response = [];

// Total Fish Count
$fish_result = $conn->query("SELECT SUM(stock_quantity) as total FROM fish");
$response['fish_count'] = $fish_result->fetch_assoc()['total'] ?? 0;

// Active Tanks Count
$tanks_result = $conn->query("SELECT COUNT(*) as total FROM tanks");
$response['tanks_count'] = $tanks_result->fetch_assoc()['total'] ?? 0;

// Today's Sales
$sales_result = $conn->query("SELECT SUM(final_amount) as total FROM sales WHERE DATE(sale_date) = CURDATE()");
$response['sales_today'] = $sales_result->fetch_assoc()['total'] ?? 0;

// Alerts Count = low stock fish + sick/recovering fish + non-active tanks
// (These are real operational issues that always need attention)
$alert_low   = (int)($conn->query("SELECT COUNT(*) as t FROM fish WHERE stock_quantity <= 5")->fetch_assoc()['t'] ?? 0);
$alert_sick  = (int)($conn->query("SELECT COUNT(*) as t FROM fish_health WHERE status IN ('sick','recovering')")->fetch_assoc()['t'] ?? 0);
$alert_tanks = (int)($conn->query("SELECT COUNT(*) as t FROM tanks WHERE status != 'active'")->fetch_assoc()['t'] ?? 0);
$response['alerts_count'] = $alert_low + $alert_sick + $alert_tanks;

// Additional live data - Pending Operations
// Pending/Maintenance Tanks
$maintenance_tanks = $conn->query("SELECT COUNT(*) as total FROM tanks WHERE status != 'active'");
$response['maintenance_tanks'] = $maintenance_tanks->fetch_assoc()['total'] ?? 0;

// Low Stock Items Count
$low_stock_count = $conn->query("SELECT COUNT(*) as total FROM fish WHERE stock_quantity <= 5");
$response['low_stock_count'] = $low_stock_count->fetch_assoc()['total'] ?? 0;

// Pending Feedings (feedings due today but not done)
$pending_feedings = $conn->query("
    SELECT COUNT(*) as total 
    FROM feeding_schedule fs
    WHERE fs.feeding_time <= CURTIME()
    AND NOT EXISTS (
        SELECT 1 FROM feeding_logs fl 
        WHERE fl.tank_id = fs.tank_id 
        AND DATE(fl.fed_at) = CURDATE()
    )
");
$response['pending_feedings'] = $pending_feedings->fetch_assoc()['total'] ?? 0;

// Upcoming Maintenance (next 7 days)
$upcoming_maintenance = $conn->query("
    SELECT COUNT(*) as total 
    FROM maintenance_log 
    WHERE next_scheduled_date IS NOT NULL 
    AND next_scheduled_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
");
$response['upcoming_maintenance'] = $upcoming_maintenance->fetch_assoc()['total'] ?? 0;

// Sick Fish Count
$sick_fish = $conn->query("SELECT COUNT(*) as total FROM fish_health WHERE status IN ('sick', 'recovering')");
$response['sick_fish'] = $sick_fish->fetch_assoc()['total'] ?? 0;

// Timestamp for last update
$response['last_updated'] = date('d M Y, g:i A');

echo json_encode($response);
?>
