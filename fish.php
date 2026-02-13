<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require_once 'config/db_connect.php';
include 'includes/header.php';

// Fetch fish with tank names
$sql = "SELECT fish.*, tanks.name as tank_name FROM fish LEFT JOIN tanks ON fish.tank_id = tanks.id ORDER BY fish.id DESC";
$fish_result = $conn->query($sql);
$tanks = $conn->query("SELECT * FROM tanks WHERE status='active'");
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">Fish Management</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['msg_type']; ?>">
                <?php echo $_SESSION['message'];
                unset($_SESSION['message']);
                unset($_SESSION['msg_type']); ?>
            </div>
        <?php endif; ?>

        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addFishModal">
            <i class="fas fa-plus"></i> Add New Fish
        </button>

        <div class="row">
            <?php while ($row = $fish_result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card card-custom h-100">
                        <?php if ($row['image']): ?>
                            <img src="uploads/<?php echo $row['image']; ?>" class="card-img-top"
                                style="height: 200px; object-fit: cover;" alt="Fish Image">
                        <?php else: ?>
                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center"
                                style="height: 200px;">
                                <i class="fas fa-fish fa-3x"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title">
                                <?php echo $row['name']; ?>
                            </h5>
                            <p class="card-text">
                                <strong>Species:</strong>
                                <?php echo $row['species']; ?><br>
                                <strong>Price:</strong> Buy: $
                                <?php echo $row['purchase_price']; ?> / Sell: $
                                <?php echo $row['selling_price']; ?><br>
                                <strong>Stock:</strong>
                                <?php echo $row['stock_quantity']; ?><br>
                                <strong>Tank:</strong>
                                <?php echo $row['tank_name'] ?? 'Unassigned'; ?>
                            </p>
                            <a href="fish_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm"><i
                                    class="fas fa-edit"></i> Edit</a>
                            <a href="fish_action.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm"
                                onclick="return confirm('Delete this fish?')"><i class="fas fa-trash"></i> Delete</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- Add Fish Modal -->
<div class="modal fade" id="addFishModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="fish_action.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Fish</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Name</label><input type="text" name="name"
                                class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Species</label><input type="text" name="species"
                                class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Water Type</label>
                            <select name="water_type" class="form-select">
                                <option value="freshwater">Freshwater</option>
                                <option value="saltwater">Saltwater</option>
                                <option value="brackish">Brackish</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3"><label>Tank</label>
                            <select name="tank_id" class="form-select">
                                <option value="">Select Tank</option>
                                <?php while ($t = $tanks->fetch_assoc()): ?>
                                    <option value="<?php echo $t['id']; ?>">
                                        <?php echo $t['name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3"><label>Purchase Price</label><input type="number" step="0.01"
                                name="purchase_price" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Selling Price</label><input type="number" step="0.01"
                                name="selling_price" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Initial Stock</label><input type="number" name="stock"
                                class="form-control" value="0"></div>
                        <div class="col-md-6 mb-3"><label>Image</label><input type="file" name="image"
                                class="form-control"></div>
                        <!-- Extra fields like color/size can be added here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="save" class="btn btn-primary">Save Fish</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>