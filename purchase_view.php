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

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: purchases.php");
    exit();
}

// Fetch purchase header
$stmt = $conn->prepare(
    "SELECT p.*, s.name AS supplier_name, s.phone AS supplier_phone, s.address AS supplier_address
     FROM purchases p
     LEFT JOIN suppliers s ON p.supplier_id = s.id
     WHERE p.id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$purchase = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$purchase) {
    header("Location: purchases.php");
    exit();
}

// Fetch purchase items
$items_stmt = $conn->prepare(
    "SELECT pi.*, f.name AS fish_name
     FROM purchase_items pi
     LEFT JOIN fish f ON pi.fish_id = f.id
     WHERE pi.purchase_id = ?"
);
$items_stmt->bind_param("i", $id);
$items_stmt->execute();
$items = $items_stmt->get_result();
$items_stmt->close();

include 'includes/header.php';
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">Purchase #<?php echo $purchase['id']; ?></h2>
                <small class="text-muted">
                    <?php
                    $pts  = strtotime($purchase['purchase_date']);
                    $ptim = date('H:i', $pts);
                    echo date('d M Y', $pts);
                    if ($ptim !== '00:00') echo ' at ' . date('g:i A', $pts);
                    ?>
                </small>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-<?php echo $purchase['status'] === 'completed' ? 'success' : 'warning'; ?> fs-6">
                    <?php echo ucfirst($purchase['status']); ?>
                </span>
                <a href="purchases.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>

        <div class="row g-4">
            <!-- Supplier Info -->
            <div class="col-md-5">
                <div class="card card-custom h-100">
                    <div class="card-header fw-semibold">
                        <i class="fas fa-truck me-2 text-primary"></i>Supplier Details
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td class="text-muted" style="width:120px;">Supplier</td>
                                <td class="fw-semibold"><?php echo htmlspecialchars($purchase['supplier_name'] ?? '—'); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Phone</td>
                                <td><?php echo htmlspecialchars($purchase['supplier_phone'] ?? '—'); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Address</td>
                                <td><?php echo htmlspecialchars($purchase['supplier_address'] ?? '—'); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Purchase ID</td>
                                <td>#<?php echo $purchase['id']; ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Date</td>
                                <td>
                                    <?php
                                    echo date('d M Y', $pts);
                                    if ($ptim !== '00:00') echo ' &nbsp;<span class="text-muted small">' . date('g:i A', $pts) . '</span>';
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Amount Summary -->
            <div class="col-md-7">
                <div class="card card-custom h-100">
                    <div class="card-header fw-semibold">
                        <i class="fas fa-receipt me-2 text-success"></i>Amount Summary
                    </div>
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                        <div class="display-6 fw-bold text-success mb-1">
                            &#8377;<?php echo number_format($purchase['total_amount'], 2); ?>
                        </div>
                        <div class="text-muted small">Total Purchase Amount</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="card card-custom mt-4">
            <div class="card-header fw-semibold">
                <i class="fas fa-list me-2 text-info"></i>Items Purchased
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Item</th>
                            <th>Type</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        $items->data_seek(0);
                        while ($item = $items->fetch_assoc()):
                            $is_fish    = !empty($item['fish_id']);
                            $item_label = $is_fish
                                ? htmlspecialchars($item['fish_name'] ?? 'Fish #' . $item['fish_id'])
                                : htmlspecialchars($item['item_name'] ?? '—');
                        ?>
                            <tr>
                                <td class="ps-3"><?php echo $i++; ?></td>
                                <td class="fw-semibold"><?php echo $item_label; ?></td>
                                <td>
                                    <?php if ($is_fish): ?>
                                        <span class="badge bg-info text-dark"><i class="fas fa-fish me-1"></i>Fish</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="fas fa-box me-1"></i>Supply</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo (int)$item['quantity']; ?></td>
                                <td>&#8377;<?php echo number_format($item['unit_price'], 2); ?></td>
                                <td class="fw-semibold text-success">&#8377;<?php echo number_format($item['total_price'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="5" class="text-end fw-bold pe-3">Grand Total</td>
                            <td class="fw-bold text-success fs-6">&#8377;<?php echo number_format($purchase['total_amount'], 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
