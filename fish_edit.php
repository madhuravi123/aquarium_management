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

$fish = null;
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM fish WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $fish = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
if (!$fish) {
    header("Location: fish.php");
    exit();
}
$tanks = $conn->query("SELECT * FROM tanks WHERE status='active'");
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
                            value="<?php echo htmlspecialchars($fish['name']); ?>" required></div>
                    <div class="mb-3"><label>Species</label><input type="text" name="species" class="form-control"
                            value="<?php echo htmlspecialchars($fish['species'] ?? ''); ?>"></div>
                    <div class="mb-3"><label>Tank</label>
                        <select name="tank_id" class="form-select">
                            <option value="">Select Tank</option>
                            <?php while ($t = $tanks->fetch_assoc()): ?>
                                <option value="<?php echo $t['id']; ?>" <?php echo $t['id'] == $fish['tank_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($t['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label>Selling Price</label><input type="number" step="0.01" name="selling_price"
                            class="form-control" value="<?php echo $fish['selling_price']; ?>"></div>
                    <div class="mb-3">
                        <label>Image</label>
                        <?php if ($fish['image']): ?>
                            <br><img src="uploads/<?php echo htmlspecialchars($fish['image']); ?>" width="100" class="mb-2"><br>
                        <?php endif; ?>
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
