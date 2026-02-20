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
include 'includes/header.php';

// Sales Report
$sales_sql = "SELECT DATE(sale_date) as date, SUM(final_amount) as total FROM sales GROUP BY DATE(sale_date) ORDER BY date DESC LIMIT 30";
$sales_res = $conn->query($sales_sql);

// Low Stock Report
$low_stock = $conn->query("SELECT * FROM fish WHERE stock_quantity <= 5");

// Expense vs Revenue (current month + year)
$month = date('m');
$year  = date('Y');
$revenue  = $conn->query("SELECT SUM(final_amount) as total FROM sales WHERE MONTH(sale_date) = $month AND YEAR(sale_date) = $year")->fetch_assoc()['total'] ?? 0;
$expenses = $conn->query("SELECT SUM(amount) as total FROM expenses WHERE MONTH(expense_date) = $month AND YEAR(expense_date) = $year")->fetch_assoc()['total'] ?? 0;
$profit = $revenue - $expenses;
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Reports & Analytics</h2>
                    <div class="text-end">
                        <span class="live-indicator">Live</span>
                        <div id="last-updated" class="mt-1" style="font-size: 0.75rem; color: #6c757d;">Last updated: <?php echo date('d M Y, g:i A'); ?></div>
                    </div>
                </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card card-custom bg-success text-white">
                    <div class="card-body">
                        <h5>Monthly Revenue</h5>
                        <h3 id="monthly-revenue">&#8377;<?php echo number_format($revenue, 2); ?>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom bg-danger text-white">
                    <div class="card-body">
                        <h5>Monthly Expenses</h5>
                        <h3 id="monthly-expenses">&#8377;<?php echo number_format($expenses, 2); ?>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom bg-primary text-white">
                    <div class="card-body">
                        <h5>Net Profit</h5>
                        <h3 id="net-profit">&#8377;<?php echo number_format($profit, 2); ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card card-custom mb-4">
                    <div class="card-header">Low Stock Alert (<= 5)</div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Fish</th>
                                            <th>Stock</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="low-stock-tbody">
                                        <?php while ($row = $low_stock->fetch_assoc()): ?>
                                            <tr class="table-danger">
                                                <td>
                                                    <?php echo htmlspecialchars($row['name']); ?>
                                                </td>
                                                <td>
                                                    <?php echo (int)$row['stock_quantity']; ?>
                                                </td>
                                                <td><a href="purchases_new.php"
                                                        class="btn btn-sm btn-outline-dark">Restock</a></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-custom">
                        <div class="card-header">Last 30 Days Sales</div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Total Sales</th>
                                    </tr>
                                </thead>
                                <tbody id="sales-30days-tbody">
                                    <?php while ($row = $sales_res->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <?php echo $row['date']; ?>
                                            </td>
                                            <td>&#8377;<?php echo number_format($row['total'], 2); ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
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
            return '&#8377;' + parseFloat(amount).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    
        // Update reports data
        function updateReports() {
            fetch('api_reports.php')
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Reports update error:', data.error);
                        return;
                    }
    
                    // Update summary cards
                    updateCurrencyValue('monthly-revenue', data.revenue);
                    updateCurrencyValue('monthly-expenses', data.expenses);
                    updateCurrencyValue('net-profit', data.profit);
    
                    // Update low stock table
                    updateLowStockTable(data.low_stock_items);
    
                    // Update sales table
                    updateSalesTable(data.sales_30days);
    
                    // Update last updated timestamp
                    const lastUpdatedEl = document.getElementById('last-updated');
                    if (lastUpdatedEl) {
                        lastUpdatedEl.textContent = 'Last updated: ' + data.last_updated;
                    }
                })
                .catch(error => {
                    console.error('Failed to update reports:', error);
                });
        }
    
        // Update currency value with highlight effect
        function updateCurrencyValue(elementId, newValue) {
            const element = document.getElementById(elementId);
            if (element) {
                const newFormatted = formatCurrency(newValue);
                // Remove HTML entities for comparison
                const currentText = element.innerHTML.replace(/&#8377;/g, '₹');
                const newText = newFormatted.replace(/&#8377;/g, '₹');
                if (currentText !== newText) {
                    element.classList.add('stat-updated');
                    element.innerHTML = newFormatted;
                    setTimeout(() => element.classList.remove('stat-updated'), 1000);
                }
            }
        }
    
        // Update low stock table
        function updateLowStockTable(items) {
            const tbody = document.getElementById('low-stock-tbody');
            if (!tbody || !items) return;
    
            let html = '';
            if (items.length === 0) {
                html = '<tr><td colspan="3" class="text-center text-muted">No low stock items</td></tr>';
            } else {
                items.forEach(item => {
                    html += `<tr class="table-danger">
                        <td>${escapeHtml(item.name)}</td>
                        <td>${item.stock_quantity}</td>
                        <td><a href="purchases_new.php" class="btn btn-sm btn-outline-dark">Restock</a></td>
                    </tr>`;
                });
            }
            tbody.innerHTML = html;
        }
    
        // Update sales table
        function updateSalesTable(sales) {
            const tbody = document.getElementById('sales-30days-tbody');
            if (!tbody || !sales) return;
    
            let html = '';
            if (sales.length === 0) {
                html = '<tr><td colspan="2" class="text-center text-muted">No sales data</td></tr>';
            } else {
                sales.forEach(row => {
                    html += `<tr>
                        <td>${row.date}</td>
                        <td>${formatCurrency(row.total)}</td>
                    </tr>`;
                });
            }
            tbody.innerHTML = html;
        }
    
        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    
        // Start auto-update
        function startAutoUpdate() {
            updateReports(); // Initial update
            updateTimer = setInterval(updateReports, UPDATE_INTERVAL);
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
                updateReports(); // Immediate update when tab becomes visible
            }
        });
    
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
        0% { color: #14d2c8; transform: scale(1.05); }
        100% { color: inherit; transform: scale(1); }
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
    </style>
    
    <?php include 'includes/footer.php'; ?>