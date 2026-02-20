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

$sql = "SELECT s.*, c.name as customer_name FROM sales s LEFT JOIN customers c ON s.customer_id = c.id ORDER BY s.id DESC";
$sales = $conn->query($sql);
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">Sales Management</h2>

        <a href="sales_new.php" class="btn btn-success mb-3"><i class="fas fa-plus"></i> New Sale</a>

        <div class="card card-custom">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $sales->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php echo $row['id']; ?>
                                </td>
                                <td>
                                    <?php echo $row['customer_name'] ?? 'Walk-in Customer'; ?>
                                </td>
                                <td>
                                    <?php echo $row['sale_date']; ?>
                                </td>
                                <td>&#8377;<?php echo number_format($row['final_amount'], 2); ?>
                                </td>
                                <td>
                                    <?php echo ucfirst($row['payment_method']); ?>
                                </td>
                                <td>
                                    <a href="invoice_print.php?id=<?php echo $row['id']; ?>" target="_blank"
                                        class="btn btn-sm btn-secondary"><i class="fas fa-print"></i> Invoice</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>