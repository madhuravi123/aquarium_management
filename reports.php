<?php
session_start();
require_once 'config/db_connect.php';
include 'includes/header.php';

// Sales Report
$sales_sql = "SELECT DATE(sale_date) as date, SUM(final_amount) as total FROM sales GROUP BY DATE(sale_date) ORDER BY date DESC LIMIT 30";
$sales_res = $conn->query($sales_sql);

// Low Stock Report
$low_stock = $conn->query("SELECT * FROM fish WHERE stock_quantity <= 5");

// Expense vs Revenue (Simple monthly)
$month = date('m');
$revenue = $conn->query("SELECT SUM(final_amount) as total FROM sales WHERE MONTH(sale_date) = $month")->fetch_assoc()['total'] ?? 0;
$expenses = $conn->query("SELECT SUM(amount) as total FROM expenses WHERE MONTH(expense_date) = $month")->fetch_assoc()['total'] ?? 0;
$profit = $revenue - $expenses;
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">Reports & Analytics</h2>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card card-custom bg-success text-white">
                    <div class="card-body">
                        <h5>Monthly Revenue</h5>
                        <h3>$
                            <?php echo number_format($revenue, 2); ?>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom bg-danger text-white">
                    <div class="card-body">
                        <h5>Monthly Expenses</h5>
                        <h3>$
                            <?php echo number_format($expenses, 2); ?>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom bg-primary text-white">
                    <div class="card-body">
                        <h5>Net Profit</h5>
                        <h3>$
                            <?php echo number_format($profit, 2); ?>
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
                                    <tbody>
                                        <?php while ($row = $low_stock->fetch_assoc()): ?>
                                            <tr class="table-danger">
                                                <td>
                                                    <?php echo $row['name']; ?>
                                                </td>
                                                <td>
                                                    <?php echo $row['stock_quantity']; ?>
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
                                <tbody>
                                    <?php while ($row = $sales_res->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <?php echo $row['date']; ?>
                                            </td>
                                            <td>$
                                                <?php echo number_format($row['total'], 2); ?>
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
    <?php include 'includes/footer.php'; ?>