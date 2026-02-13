<?php
session_start();
require_once 'config/db_connect.php';
include 'includes/header.php';

$tanks = $conn->query("SELECT * FROM tanks WHERE status='active'");
$records = $conn->query("SELECT w.*, t.name as tank_name, u.username FROM water_quality w JOIN tanks t ON w.tank_id = t.id JOIN users u ON w.recorded_by = u.id ORDER BY w.recorded_at DESC LIMIT 50");
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">Water Quality Monitoring</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['msg_type']; ?>">
                <?php echo $_SESSION['message'];
                unset($_SESSION['message']);
                unset($_SESSION['msg_type']); ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card card-custom">
                    <div class="card-header bg-primary text-white">Record New Test</div>
                    <div class="card-body">
                        <form action="water_quality_action.php" method="POST">
                            <div class="mb-3">
                                <label>Tank</label>
                                <select name="tank_id" class="form-select" required>
                                    <?php while ($t = $tanks->fetch_assoc()): ?>
                                        <option value="<?php echo $t['id']; ?>">
                                            <?php echo $t['name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3"><label>pH Level (0-14)</label><input type="number" step="0.01"
                                    name="ph_level" class="form-control" required></div>
                            <div class="mb-3"><label>Temperature (°C)</label><input type="number" step="0.1"
                                    name="temperature" class="form-control" required></div>
                            <div class="mb-3"><label>Ammonia (ppm)</label><input type="number" step="0.01"
                                    name="ammonia" class="form-control"></div>
                            <div class="mb-3"><label>Nitrite (ppm)</label><input type="number" step="0.01"
                                    name="nitrite" class="form-control"></div>
                            <div class="mb-3"><label>Nitrate (ppm)</label><input type="number" step="0.01"
                                    name="nitrate" class="form-control"></div>
                            <button type="submit" name="save" class="btn btn-primary w-100">Save Record</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card card-custom">
                    <div class="card-header">Recent Records</div>
                    <div class="card-body">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Tank</th>
                                    <th>pH</th>
                                    <th>Temp</th>
                                    <th>Ammonia</th>
                                    <th>Recorded By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $records->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php echo date('M d, H:i', strtotime($row['recorded_at'])); ?>
                                        </td>
                                        <td>
                                            <?php echo $row['tank_name']; ?>
                                        </td>
                                        <td>
                                            <?php echo $row['ph_level']; ?>
                                        </td>
                                        <td>
                                            <?php echo $row['temperature']; ?>°C
                                        </td>
                                        <td>
                                            <?php echo $row['ammonia_level']; ?>
                                        </td>
                                        <td>
                                            <?php echo $row['username']; ?>
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