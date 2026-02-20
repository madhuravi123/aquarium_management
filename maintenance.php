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

$tanks = $conn->query("SELECT * FROM tanks");
$logs = $conn->query("SELECT m.*, t.name as tank_name, u.username FROM maintenance_log m JOIN tanks t ON m.tank_id = t.id JOIN users u ON m.performed_by = u.id ORDER BY m.maintenance_date DESC");
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">Maintenance Logs</h2>

        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addMaintModal">
            <i class="fas fa-tools"></i> Log Maintenance
        </button>

        <div class="card card-custom">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Tank</th>
                            <th>Activity</th>
                            <th>Performed By</th>
                            <th>Next Schedule</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $logs->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php echo date('d M Y g:i A', strtotime($row['maintenance_date'])); ?>
                                </td>
                                <td>
                                    <?php echo $row['tank_name']; ?>
                                </td>
                                <td>
                                    <?php echo $row['activity_type']; ?>
                                </td>
                                <td>
                                    <?php echo $row['username']; ?>
                                </td>
                                <td>
                                    <?php echo $row['next_scheduled_date']; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Maintenance Modal -->
<div class="modal fade" id="addMaintModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="maintenance_action.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Log Maintenance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label>Tank</label>
                        <select name="tank_id" class="form-select" required>
                            <?php while ($t = $tanks->fetch_assoc()): ?>
                                <option value="<?php echo $t['id']; ?>">
                                    <?php echo $t['name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label>Activity</label>
                        <select name="activity_type" class="form-select">
                            <option value="Water Change">Water Change</option>
                            <option value="Filter Cleaning">Filter Cleaning</option>
                            <option value="Glass Cleaning">Glass Cleaning</option>
                            <option value="Equipment Check">Equipment Check</option>
                            <option value="Full Service">Full Service</option>
                        </select>
                    </div>
                    <div class="mb-3"><label>Description</label><textarea name="description"
                            class="form-control"></textarea></div>
                    <div class="mb-3"><label>Next Scheduled Date</label><input type="date" name="next_date"
                            class="form-control"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="save" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>