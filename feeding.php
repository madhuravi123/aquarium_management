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

$tanks_sched = $conn->query("SELECT * FROM tanks WHERE status='active'");
$tanks_log = $conn->query("SELECT * FROM tanks WHERE status='active'");

$schedules = $conn->query("SELECT fs.*, t.name as tank_name FROM feeding_schedule fs JOIN tanks t ON fs.tank_id = t.id");
$logs = $conn->query("SELECT fl.*, t.name as tank_name, u.username FROM feeding_logs fl JOIN tanks t ON fl.tank_id = t.id JOIN users u ON fl.fed_by = u.id ORDER BY fl.fed_at DESC LIMIT 20");
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">Feeding Management</h2>

        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item"><button class="nav-link active" id="log-tab" data-bs-toggle="tab" data-bs-target="#log"
                    type="button">Feeding Logs</button></li>
            <li class="nav-item"><button class="nav-link" id="schedule-tab" data-bs-toggle="tab"
                    data-bs-target="#schedule" type="button">Schedules</button></li>
        </ul>

        <div class="tab-content" id="myTabContent">
            <!-- Logs Tab -->
            <div class="tab-pane fade show active p-3" id="log" role="tabpanel">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card card-custom">
                            <div class="card-header bg-success text-white">Log Feeding</div>
                            <div class="card-body">
                                <form action="feeding_action.php" method="POST">
                                    <div class="mb-3">
                                        <label>Tank</label>
                                        <select name="tank_id" class="form-select" required>
                                            <?php while ($t = $tanks_log->fetch_assoc()): ?>
                                                <option value="<?php echo $t['id']; ?>">
                                                    <?php echo $t['name']; ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3"><label>Food Type</label><input type="text" name="food_type"
                                            class="form-control" required></div>
                                    <button type="submit" name="log_feed" class="btn btn-success w-100">Record
                                        Feeding</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Tank</th>
                                    <th>Food</th>
                                    <th>Fed By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $logs->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php echo date('d M g:i A', strtotime($row['fed_at'])); ?>
                                        </td>
                                        <td>
                                            <?php echo $row['tank_name']; ?>
                                        </td>
                                        <td>
                                            <?php echo $row['food_type']; ?>
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

            <!-- Schedule Tab -->
            <div class="tab-pane fade p-3" id="schedule" role="tabpanel">
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addSchedModal">Add
                    Schedule</button>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tank</th>
                            <th>Food</th>
                            <th>Time</th>
                            <th>Qty (g)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $schedules->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php echo $row['tank_name']; ?>
                                </td>
                                <td>
                                    <?php echo $row['food_type']; ?>
                                </td>
                                <td>
                                    <?php echo $row['feeding_time']; ?>
                                </td>
                                <td>
                                    <?php echo $row['quantity_grams']; ?>
                                </td>
                                <td><a href="feeding_action.php?delete_sched=<?php echo $row['id']; ?>"
                                        class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Schedule Modal -->
<div class="modal fade" id="addSchedModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="feeding_action.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">New Schedule</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label>Tank</label>
                        <select name="tank_id" class="form-select">
                            <?php
                            $tanks_sched->data_seek(0);
                            while ($t = $tanks_sched->fetch_assoc()): ?>
                                <option value="<?php echo $t['id']; ?>">
                                    <?php echo $t['name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label>Food Type</label><input type="text" name="food_type" class="form-control">
                    </div>
                    <div class="mb-3"><label>Time</label><input type="time" name="feeding_time" class="form-control">
                    </div>
                    <div class="mb-3"><label>Qty (grams)</label><input type="number" step="0.1" name="quantity"
                            class="form-control"></div>
                </div>
                <div class="modal-footer"><button type="submit" name="save_sched" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>