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

$expenses = $conn->query("SELECT * FROM expenses ORDER BY expense_date DESC");
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">Expense Management</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['msg_type']; ?>">
                <?php echo $_SESSION['message'];
                unset($_SESSION['message']);
                unset($_SESSION['msg_type']); ?>
            </div>
        <?php endif; ?>

        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
            <i class="fas fa-plus"></i> Add New Expense
        </button>

        <div class="card card-custom">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $expenses->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php echo $row['expense_date']; ?>
                                </td>
                                <td>
                                    <?php echo $row['category']; ?>
                                </td>
                                <td>
                                    <?php echo $row['description']; ?>
                                </td>
                                <td>&#8377;<?php echo number_format($row['amount'], 2); ?></td>
                                <td>
                                    <a href="expenses_action.php?delete=<?php echo $row['id']; ?>"
                                        class="btn btn-sm btn-danger" onclick="return confirm('Delete this expense?')"><i
                                            class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="expenses_action.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label>Category</label>
                        <select name="category" class="form-select">
                            <option value="Rent">Rent</option>
                            <option value="Electricity">Electricity</option>
                            <option value="Salary">Salary</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Supplies">Supplies</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3"><label>Amount</label><input type="number" step="0.01" name="amount"
                            class="form-control" required></div>
                    <div class="mb-3"><label>Date</label><input type="date" name="expense_date" class="form-control"
                            value="<?php echo date('Y-m-d'); ?>"></div>
                    <div class="mb-3"><label>Description</label><textarea name="description"
                            class="form-control"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="save" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>