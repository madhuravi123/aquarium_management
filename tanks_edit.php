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

$tank = null;
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM tanks WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $tank = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
if (!$tank) {
    header("Location: tanks.php");
    exit();
}
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <h2>Edit Tank</h2>
        <div class="card card-custom mt-3" style="max-width: 600px;">
            <div class="card-body">
                <form action="tanks_action.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo $tank['id']; ?>">
                    <div class="mb-3">
                        <label>Tank Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($tank['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Capacity (Liters)</label>
                        <input type="number" step="0.01" name="capacity" class="form-control" value="<?php echo $tank['capacity']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Water Type</label>
                        <select name="water_type" class="form-select">
                            <option value="freshwater" <?php echo $tank['water_type'] == 'freshwater' ? 'selected' : ''; ?>>Freshwater</option>
                            <option value="saltwater" <?php echo $tank['water_type'] == 'saltwater' ? 'selected' : ''; ?>>Saltwater</option>
                            <option value="brackish" <?php echo $tank['water_type'] == 'brackish' ? 'selected' : ''; ?>>Brackish</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?php echo $tank['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="maintenance" <?php echo $tank['status'] == 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                            <option value="quarantine" <?php echo $tank['status'] == 'quarantine' ? 'selected' : ''; ?>>Quarantine</option>
                        </select>
                    </div>
                    <button type="submit" name="update" class="btn btn-primary">Update Tank</button>
                    <a href="tanks.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
