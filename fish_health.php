<?php
session_start();
require_once 'config/db_connect.php';
include 'includes/header.php';

$fish_list = $conn->query("SELECT * FROM fish");
$tanks = $conn->query("SELECT * FROM tanks");
$health_records = $conn->query("SELECT fh.*, f.name as fish_name, t.name as tank_name FROM fish_health fh LEFT JOIN fish f ON fh.fish_id = f.id LEFT JOIN tanks t ON fh.tank_id = t.id ORDER BY fh.recorded_at DESC");
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">Fish Health Management</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['msg_type']; ?>">
                <?php echo $_SESSION['message'];
                unset($_SESSION['message']);
                unset($_SESSION['msg_type']); ?>
            </div>
        <?php endif; ?>

        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addHealthModal">
            <i class="fas fa-notes-medical"></i> Log Health Issue
        </button>

        <div class="card card-custom">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Fish</th>
                            <th>Tank</th>
                            <th>Disease</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $health_records->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php echo date('d M Y', strtotime($row['recorded_at'])); ?>
                                </td>
                                <td>
                                    <?php echo $row['fish_name']; ?>
                                </td>
                                <td>
                                    <?php echo $row['tank_name']; ?>
                                </td>
                                <td>
                                    <?php echo $row['disease_name']; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php
                                    echo $row['status'] == 'healthy' ? 'success' :
                                        ($row['status'] == 'dead' ? 'dark' :
                                            ($row['status'] == 'recovering' ? 'info' : 'danger'));
                                    ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#editHealth<?php echo $row['id']; ?>"><i
                                            class="fas fa-edit"></i></button>

                                    <!-- Edit Modal inside loop for simplicity or separate page -->
                                    <div class="modal fade" id="editHealth<?php echo $row['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="health_action.php" method="POST">
                                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Update Status</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3"><label>Status</label>
                                                            <select name="status" class="form-select">
                                                                <option value="sick" <?php echo $row['status'] == 'sick' ? 'selected' : ''; ?>>Sick</option>
                                                                <option value="recovering" <?php echo $row['status'] == 'recovering' ? 'selected' : ''; ?>
                                                                    >Recovering</option>
                                                                <option value="healthy" <?php echo $row['status'] == 'healthy' ? 'selected' : ''; ?>>Healthy</option>
                                                                <option value="dead" <?php echo $row['status'] == 'dead' ? 'selected' : ''; ?>>Dead</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3"><label>Treatment/Notes</label>
                                                            <textarea name="treatment"
                                                                class="form-control"><?php echo $row['treatment']; ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" name="update"
                                                            class="btn btn-primary">Update</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Health Modal -->
<div class="modal fade" id="addHealthModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="health_action.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Log Health Issue</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label>Fish (Optional)</label>
                        <select name="fish_id" class="form-select">
                            <option value="">General / Unknown</option>
                            <?php
                            $fish_list->data_seek(0);
                            while ($f = $fish_list->fetch_assoc()): ?>
                                <option value="<?php echo $f['id']; ?>">
                                    <?php echo $f['name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label>Tank</label>
                        <select name="tank_id" class="form-select">
                            <option value="">Select Tank</option>
                            <?php
                            $tanks->data_seek(0);
                            while ($t = $tanks->fetch_assoc()): ?>
                                <option value="<?php echo $t['id']; ?>">
                                    <?php echo $t['name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label>Disease/Issue</label><input type="text" name="disease_name"
                            class="form-control" required></div>
                    <div class="mb-3"><label>Symptoms</label><textarea name="symptoms" class="form-control"></textarea>
                    </div>
                    <div class="mb-3"><label>Treatment</label><textarea name="treatment"
                            class="form-control"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="submti" name="save" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>