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

$response = [];
$month = date('m');
$year = date('Y');

// Monthly Revenue
$revenue_result = $conn->query("SELECT SUM(final_amount) as total FROM sales WHERE MONTH(sale_date) = $month AND YEAR(sale_date) = $year");
$response['revenue'] = $revenue_result->fetch_assoc()['total'] ?? 0;

// Monthly Expenses
$expenses_result = $conn->query("SELECT SUM(amount) as total FROM expenses WHERE MONTH(expense_date) = $month AND YEAR(expense_date) = $year");
$response['expenses'] = $expenses_result->fetch_assoc()['total'] ?? 0;

// Net Profit
$response['profit'] = $response['revenue'] - $response['expenses'];

// Low Stock Items (detailed)
$low_stock_items = [];
$low_stock_result = $conn->query("SELECT id, name, stock_quantity FROM fish WHERE stock_quantity <= 5 ORDER BY stock_quantity ASC");
while ($row = $low_stock_result->fetch_assoc()) {
    $low_stock_items[] = $row;
}
$response['low_stock_items'] = $low_stock_items;

// Last 30 Days Sales
$sales_data = [];
$sales_result = $conn->query("
    SELECT DATE(sale_date) as date, SUM(final_amount) as total 
    FROM sales 
    GROUP BY DATE(sale_date) 
    ORDER BY date DESC 
    LIMIT 30
");
while ($row = $sales_result->fetch_assoc()) {
    $sales_data[] = $row;
}
$response['sales_30days'] = $sales_data;

// Additional Live Metrics
// Total Sales Count This Month
$sales_count_result = $conn->query("SELECT COUNT(*) as total FROM sales WHERE MONTH(sale_date) = $month AND YEAR(sale_date) = $year");
$response['sales_count'] = $sales_count_result->fetch_assoc()['total'] ?? 0;

// Average Sale Value This Month
$avg_sale_result = $conn->query("SELECT AVG(final_amount) as average FROM sales WHERE MONTH(sale_date) = $month AND YEAR(sale_date) = $year");
$response['avg_sale_value'] = round($avg_sale_result->fetch_assoc()['average'] ?? 0, 2);

// Total Customers
$customers_result = $conn->query("SELECT COUNT(*) as total FROM customers");
$response['total_customers'] = $customers_result->fetch_assoc()['total'] ?? 0;

// New Customers This Month
$new_customers_result = $conn->query("SELECT COUNT(*) as total FROM customers WHERE MONTH(created_at) = $month AND YEAR(created_at) = $year");
$response['new_customers'] = $new_customers_result->fetch_assoc()['total'] ?? 0;

// Inventory Value (fish stock value)
$inventory_value_result = $conn->query("SELECT SUM(stock_quantity * purchase_price) as total FROM fish");
$response['inventory_value'] = $inventory_value_result->fetch_assoc()['total'] ?? 0;

// Pending Purchases (orders not yet received)
$pending_purchases_result = $conn->query("SELECT COUNT(*) as total FROM purchases WHERE status = 'pending'");
$response['pending_purchases'] = $pending_purchases_result->fetch_assoc()['total'] ?? 0;

// Timestamp for last update
$response['last_updated'] = date('d M Y, g:i A');

echo json_encode($response);
?>
