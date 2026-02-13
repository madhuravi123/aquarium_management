<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require_once 'config/db_connect.php';
include 'includes/header.php';

// Fetch all tanks
$tanks = $conn->query("SELECT * FROM tanks ORDER BY id DESC");
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">Tank Management</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['msg_type']; ?>">
                <?php
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                unset($_SESSION['msg_type']);
                ?>
            </div>
        <?php endif; ?>

        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addTankModal">
            <i class="fas fa-plus"></i> Add New Tank
        </button>

        <div class="card card-custom">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Capacity (L)</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $tanks->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php echo $row['id']; ?>
                                </td>
                                <td>
                                    <?php echo $row['name']; ?>
                                </td>
                                <td>
                                    <?php echo $row['capacity']; ?>
                                </td>
                                <td>
                                    <?php echo ucfirst($row['water_type']); ?>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-<?php echo $row['status'] == 'active' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="tanks_edit.php?id=<?php echo $row['id']; ?>"
                                        class="btn btn-sm btn-info text-white"><i class="fas fa-edit"></i></a>
                                    <a href="tanks_action.php?delete=<?php echo $row['id']; ?>"
                                        class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i
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

<!-- Add Tank Modal -->
<div class="modal fade" id="addTankModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="tanks_action.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Tank</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Tank Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Capacity (Liters)</label>
                        <input type="number" step="0.01" name="capacity" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Water Type</label>
                        <select name="water_type" class="form-select">
                            <option value="freshwater">Freshwater</option>
                            <option value="saltwater">Saltwater</option>
                            <option value="brackish">Brackish</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="quarantine">Quarantine</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="save" class="btn btn-primary">Save Tank</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>