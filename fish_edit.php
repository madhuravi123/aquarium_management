<?php
session_start();
require_once 'config/db_connect.php';
include 'includes/header.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $fish = $conn->query("SELECT * FROM fish WHERE id=$id")->fetch_assoc();
    $tanks = $conn->query("SELECT * FROM tanks WHERE status='active'");
}
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <h2>Edit Fish</h2>
        <div class="card card-custom mt-3" style="max-width: 600px;">
            <div class="card-body">
                <form action="fish_action.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $fish['id']; ?>">
                    <div class="mb-3"><label>Name</label><input type="text" name="name" class="form-control"
                            value="<?php echo $fish['name']; ?>" required></div>
                    <div class="mb-3"><label>Species</label><input type="text" name="species" class="form-control"
                            value="<?php echo $fish['species']; ?>"></div>
                    <div class="mb-3"><label>Tank</label>
                        <select name="tank_id" class="form-select">
                            <option value="">Select Tank</option>
                            <?php while ($t = $tanks->fetch_assoc()): ?>
                                <option value="<?php echo $t['id']; ?>" <?php echo $t['id'] == $fish['tank_id'] ? 'selected' : ''; ?>>
                                    <?php echo $t['name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label>Selling Price</label><input type="number" step="0.01" name="selling_price"
                            class="form-control" value="<?php echo $fish['selling_price']; ?>"></div>
                    <div class="mb-3">
                        <label>Image</label>
                        <?php if ($fish['image'])
                            echo "<br><img src='uploads/{$fish['image']}' width='100'><br>"; ?>
                        <input type="file" name="image" class="form-control mt-2">
                    </div>
                    <button type="submit" name="update" class="btn btn-primary">Update Fish</button>
                    <a href="fish.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>