<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Admins/staff don't have a cart
if (isset($_SESSION['user_id']) && in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: dashboard.php");
    exit();
}

require_once 'config/db_connect.php';
require_once 'includes/error_logger.php';
$page_title   = 'My Cart';
$is_logged_in = isset($_SESSION['user_id']) && ($_SESSION['role'] === 'customer');
$user_id      = $is_logged_in ? (int)$_SESSION['user_id'] : 0;

$cart_items = [];
$total      = 0.0;

if ($is_logged_in) {
    // ── DB cart for logged-in customers ─────────────────────────────────────
    $cart_res = $conn->query("
        SELECT c.fish_id, c.quantity, f.name, f.species, f.selling_price, f.stock_quantity, f.image
        FROM cart c
        JOIN fish f ON c.fish_id = f.id
        WHERE c.user_id = $user_id
        ORDER BY c.added_at ASC
    ");
    while ($row = $cart_res->fetch_assoc()) {
        // Clamp quantity to available stock
        if ($row['quantity'] > $row['stock_quantity']) {
            $row['quantity'] = $row['stock_quantity'];
            $conn->query("UPDATE cart SET quantity = {$row['stock_quantity']} WHERE user_id = $user_id AND fish_id = {$row['fish_id']}");
        }
        $row['subtotal'] = $row['quantity'] * $row['selling_price'];
        $total += $row['subtotal'];
        $cart_items[] = $row;
    }
} else {
    // ── Session cart for guests ──────────────────────────────────────────────
    if (!empty($_SESSION['guest_cart'])) {
        foreach ($_SESSION['guest_cart'] as $fish_id => $quantity) {
            $fish_id  = (int)$fish_id;
            $quantity = max(1, (int)$quantity);

            $fish_res = $conn->query("SELECT id, name, species, selling_price, stock_quantity, image FROM fish WHERE id = $fish_id");
            if (!$fish_res || $fish_res->num_rows === 0) {
                unset($_SESSION['guest_cart'][$fish_id]); // remove orphan
                continue;
            }
            $f = $fish_res->fetch_assoc();

            // Clamp quantity
            if ($quantity > $f['stock_quantity']) {
                $quantity = $f['stock_quantity'];
                $_SESSION['guest_cart'][$fish_id] = $quantity;
            }
            if ($quantity <= 0) {
                unset($_SESSION['guest_cart'][$fish_id]);
                continue;
            }

            $row = [
                'fish_id'        => $fish_id,
                'quantity'       => $quantity,
                'name'           => $f['name'],
                'species'        => $f['species'],
                'selling_price'  => $f['selling_price'],
                'stock_quantity' => $f['stock_quantity'],
                'image'          => $f['image'] ?? '',
                'subtotal'       => $quantity * $f['selling_price'],
            ];
            $total += $row['subtotal'];
            $cart_items[] = $row;
        }
    }
}

include 'includes/customer_header.php';
?>

<div class="container py-4">
    <h4 class="fw-bold mb-1"><i class="fas fa-shopping-cart me-2 text-primary"></i>My Cart</h4>
    <p class="text-muted mb-4">Review your items before placing an order.</p>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo htmlspecialchars($_GET['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart text-muted" style="font-size:4rem;opacity:0.3;"></i>
            <h5 class="mt-3 text-muted">Your cart is empty.</h5>
            <a href="shop.php" class="btn btn-primary mt-2">
                <i class="fas fa-fish me-1"></i>Browse Fish
            </a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Fish</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <?php if (!empty($item['image']) && file_exists('uploads/' . $item['image'])): ?>
                                                <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>"
                                                     style="width:50px;height:50px;object-fit:contain;border-radius:6px;">
                                            <?php else: ?>
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                                                    <i class="fas fa-fish text-info"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($item['name']); ?></div>
                                                <small class="text-muted fst-italic"><?php echo htmlspecialchars($item['species'] ?? ''); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>&#8377;<?php echo number_format($item['selling_price'], 2); ?></td>
                                    <td style="width:130px;">
                                        <form action="cart_action.php" method="POST" class="d-flex gap-1">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="fish_id" value="<?php echo $item['fish_id']; ?>">
                                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>"
                                                   min="1" max="<?php echo $item['stock_quantity']; ?>"
                                                   class="form-control form-control-sm" style="width:65px;">
                                            <button type="submit" class="btn btn-outline-secondary btn-sm" title="Update">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="fw-semibold text-success">&#8377;<?php echo number_format($item['subtotal'], 2); ?></td>
                                    <td>
                                        <form action="cart_action.php" method="POST">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="fish_id" value="<?php echo $item['fish_id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm"
                                                    onclick="return confirm('Remove this item?')">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <a href="shop.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i>Continue Shopping
                    </a>
                    <form action="cart_action.php" method="POST">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="btn btn-outline-danger"
                                onclick="return confirm('Clear all items from cart?')">
                            <i class="fas fa-trash me-1"></i>Clear Cart
                        </button>
                    </form>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="fas fa-receipt me-2"></i>Order Summary
                    </div>
                    <div class="card-body">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="d-flex justify-content-between small mb-1">
                                <span><?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['quantity']; ?></span>
                                <span>&#8377;<?php echo number_format($item['subtotal'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Total</span>
                            <span class="text-success">&#8377;<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="d-grid mt-3">
                            <a href="checkout.php" class="btn btn-success btn-lg fw-semibold">
                                <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                            </a>
                        </div>
                        <?php if (!$is_logged_in): ?>
                            <p class="text-muted small text-center mt-2 mb-0">
                                <i class="fas fa-info-circle me-1"></i>
                                No login required — just fill delivery details at checkout.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<footer class="text-center text-muted py-4 mt-5 border-top bg-white">
    <small>&copy; <?php echo date('Y'); ?> Harini Aquarium &amp; Fish Shop, Cuddalore</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
