<?php
session_start();
require_once 'config/db_connect.php';
include 'includes/header.php';

$sql = "SELECT p.*, s.name as supplier_name FROM purchases p LEFT JOIN suppliers s ON p.supplier_id = s.id ORDER BY p.id DESC";
$purchases = $conn->query($sql);
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">Purchase Management</h2>

        <a href="purchases_new.php" class="btn btn-success mb-3"><i class="fas fa-plus"></i> New Purchase</a>

        <div class="card card-custom">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Supplier</th>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $purchases->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php echo $row['id']; ?>
                                </td>
                                <td>
                                    <?php echo $row['supplier_name']; ?>
                                </td>
                                <td>
                                    <?php echo $row['purchase_date']; ?>
                                </td>
                                <td>$
                                    <?php echo number_format($row['total_amount'], 2); ?>
                                </td>
                                <td><span class="badge bg-<?php echo $row['status'] == 'completed' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span></td>
                                <td>
                                    <a href="purchase_view.php?id=<?php echo $row['id']; ?>"
                                        class="btn btn-sm btn-info text-white"><i class="fas fa-eye"></i></a>
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