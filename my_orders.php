<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: index.php");
    exit();
}
require_once 'config/db_connect.php';
$page_title = 'My Orders';

$user_id = (int)$_SESSION['user_id'];

// Fetch orders placed by this user
$orders_res = $conn->query("
    SELECT s.id, s.final_amount, s.payment_method, s.sale_date, s.notes,
           c.name as customer_name, c.address
    FROM sales s
    LEFT JOIN customers c ON s.customer_id = c.id
    WHERE s.user_id = $user_id
    ORDER BY s.sale_date DESC
");

// Selected order details
$selected_order = null;
$order_items    = [];
if (isset($_GET['view'])) {
    $oid = (int)$_GET['view'];
    $ord = $conn->query("
        SELECT s.*, c.name as cname, c.phone as cphone, c.address as caddress
        FROM sales s LEFT JOIN customers c ON s.customer_id = c.id
        WHERE s.id = $oid AND s.user_id = $user_id
    ");
    if ($ord && $ord->num_rows > 0) {
        $selected_order = $ord->fetch_assoc();
        $items_res = $conn->query("
            SELECT si.quantity, si.unit_price, si.total_price, f.name, f.species
            FROM sale_items si JOIN fish f ON si.fish_id = f.id
            WHERE si.sale_id = $oid
        ");
        while ($i = $items_res->fetch_assoc()) $order_items[] = $i;
    }
}

include 'includes/customer_header.php';
?>

<div class="container py-4">
    <h4 class="fw-bold mb-1"><i class="fas fa-box-open me-2 text-primary"></i>My Orders</h4>
    <p class="text-muted mb-4">Track all your fish orders in one place.</p>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo htmlspecialchars($_GET['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($selected_order): ?>
        <!-- ORDER DETAIL VIEW -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span><i class="fas fa-receipt me-2"></i>Order #<?php echo $selected_order['id']; ?> – Details</span>
                <a href="my_orders.php" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Back to Orders
                </a>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Order Date:</strong> <?php echo date('d M Y, h:i A', strtotime($selected_order['sale_date'])); ?></p>
                        <p class="mb-1"><strong>Payment:</strong>
                            <span class="badge bg-info text-dark"><?php echo strtoupper($selected_order['payment_method']); ?></span>
                        </p>
                        <?php if ($selected_order['notes']): ?>
                            <p class="mb-1"><strong>Notes:</strong> <?php echo htmlspecialchars($selected_order['notes']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Delivery Name:</strong> <?php echo htmlspecialchars($selected_order['cname'] ?? 'N/A'); ?></p>
                        <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($selected_order['cphone'] ?? 'N/A'); ?></p>
                        <p class="mb-0"><strong>Address:</strong> <?php echo htmlspecialchars($selected_order['caddress'] ?? 'N/A'); ?></p>
                    </div>
                </div>

                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Fish</th>
                            <th>Species</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $idx => $item): ?>
                        <tr>
                            <td><?php echo $idx + 1; ?></td>
                            <td class="fw-semibold"><?php echo htmlspecialchars($item['name']); ?></td>
                            <td class="fst-italic text-muted small"><?php echo htmlspecialchars($item['species'] ?? ''); ?></td>
                            <td class="text-end">&#8377;<?php echo number_format($item['unit_price'], 2); ?></td>
                            <td class="text-center"><?php echo $item['quantity']; ?></td>
                            <td class="text-end fw-semibold">&#8377;<?php echo number_format($item['total_price'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-success">
                            <td colspan="5" class="text-end fw-bold">Grand Total</td>
                            <td class="text-end fw-bold fs-5">&#8377;<?php echo number_format($selected_order['final_amount'], 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    <?php else: ?>
        <!-- ORDERS LIST -->
        <?php if ($orders_res && $orders_res->num_rows > 0): ?>
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Order #</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Payment</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $orders_res->data_seek(0);
                        while ($order = $orders_res->fetch_assoc()):
                            // Count items
                            $ic = $conn->query("SELECT COUNT(*) as c FROM sale_items WHERE sale_id = {$order['id']}")->fetch_assoc()['c'];
                        ?>
                            <tr>
                                <td class="ps-3">
                                    <span class="fw-bold text-primary">#<?php echo $order['id']; ?></span>
                                </td>
                                <td><?php echo date('d M Y, h:i A', strtotime($order['sale_date'])); ?></td>
                                <td><span class="badge bg-secondary"><?php echo $ic; ?> item(s)</span></td>
                                <td>
                                    <?php
                                    $pm_badges = [
                                        'cash'   => 'bg-success',
                                        'upi'    => 'bg-info text-dark',
                                        'card'   => 'bg-primary',
                                        'online' => 'bg-warning text-dark',
                                    ];
                                    $pm_badge = $pm_badges[$order['payment_method']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?php echo $pm_badge; ?>">
                                        <?php echo strtoupper($order['payment_method']); ?>
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    &#8377;<?php echo number_format($order['final_amount'], 2); ?>
                                </td>
                                <td class="text-center">
                                    <a href="my_orders.php?view=<?php echo $order['id']; ?>"
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-box-open text-muted" style="font-size:4rem;opacity:0.3;"></i>
                <h5 class="mt-3 text-muted">You have no orders yet.</h5>
                <a href="shop.php" class="btn btn-primary mt-2">
                    <i class="fas fa-fish me-1"></i>Start Shopping
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<footer class="text-center text-muted py-4 mt-5 border-top bg-white">
    <small>&copy; <?php echo date('Y'); ?> Harini Aquarium &amp; Fish Shop, Cuddalore</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
