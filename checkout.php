<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Admins/staff don't checkout via the customer panel
if (isset($_SESSION['user_id']) && in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: dashboard.php");
    exit();
}

require_once 'config/db_connect.php';
require_once 'includes/error_logger.php';
$page_title = 'Checkout';

$is_logged_in = isset($_SESSION['user_id']) && ($_SESSION['role'] === 'customer');
$user_id      = $is_logged_in ? (int)$_SESSION['user_id'] : null;

// ── BUILD CART ITEMS ──────────────────────────────────────────────────────────
$cart_items = [];
$total      = 0.0;

if ($is_logged_in) {
    // DB cart for logged-in customers
    $cart_res = $conn->query("
        SELECT c.fish_id, c.quantity, f.name, f.selling_price, f.stock_quantity
        FROM cart c JOIN fish f ON c.fish_id = f.id
        WHERE c.user_id = $user_id
    ");
    while ($row = $cart_res->fetch_assoc()) {
        $row['subtotal'] = $row['quantity'] * $row['selling_price'];
        $total += $row['subtotal'];
        $cart_items[] = $row;
    }
} else {
    // Session cart for guests
    if (!empty($_SESSION['guest_cart'])) {
        foreach ($_SESSION['guest_cart'] as $fish_id => $quantity) {
            $fish_id  = (int)$fish_id;
            $quantity = max(1, (int)$quantity);
            $f = $conn->query(
                "SELECT id AS fish_id, name, selling_price, stock_quantity FROM fish WHERE id = $fish_id"
            )->fetch_assoc();
            if ($f) {
                $quantity      = min($quantity, (int)$f['stock_quantity']);
                if ($quantity <= 0) continue;
                $f['quantity'] = $quantity;
                $f['subtotal'] = $quantity * $f['selling_price'];
                $total += $f['subtotal'];
                $cart_items[] = $f;
            }
        }
    }
}

if (empty($cart_items)) {
    header("Location: cart.php?msg=Your cart is empty.");
    exit();
}

// Pre-fill delivery fields for logged-in customers
$prefill = ['name' => '', 'phone' => '', 'address' => ''];
if ($is_logged_in) {
    $u = $conn->query("SELECT full_name, phone, address FROM users WHERE id = $user_id")->fetch_assoc();
    $prefill['name']    = $u['full_name'] ?? $_SESSION['full_name'] ?? '';
    $prefill['phone']   = $u['phone']     ?? '';
    $prefill['address'] = $u['address']   ?? '';
}

// ── HANDLE ORDER PLACEMENT ────────────────────────────────────────────────────
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $delivery_name    = trim($_POST['delivery_name']    ?? '');
    $delivery_phone   = trim($_POST['delivery_phone']   ?? '');
    $delivery_address = trim($_POST['delivery_address'] ?? '');
    $delivery_city    = trim($_POST['delivery_city']    ?? '');
    $delivery_pincode = trim($_POST['delivery_pincode'] ?? '');
    $payment_method   = $_POST['payment_method']        ?? 'cash';
    $notes            = trim($_POST['notes']            ?? '');

    if (!$delivery_name || !$delivery_phone || !$delivery_address) {
        $error = 'Please fill in your name, phone number and address.';
    } elseif (!in_array($payment_method, ['cash', 'card', 'online', 'upi'])) {
        $error = 'Invalid payment method selected.';
    } else {
        $conn->begin_transaction();
        try {
            // Find or create customer record by phone
            $stmt = $conn->prepare("SELECT id FROM customers WHERE phone = ?");
            $stmt->bind_param("s", $delivery_phone);
            $stmt->execute();
            $cust = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($cust) {
                $customer_id = (int)$cust['id'];
                $upd = $conn->prepare(
                    "UPDATE customers SET name = ?, address = ?, city = ?, pincode = ? WHERE id = ?"
                );
                $upd->bind_param("ssssi", $delivery_name, $delivery_address, $delivery_city, $delivery_pincode, $customer_id);
                $upd->execute();
                $upd->close();
            } else {
                $ins = $conn->prepare(
                    "INSERT INTO customers (name, phone, address, city, pincode) VALUES (?, ?, ?, ?, ?)"
                );
                $ins->bind_param("sssss", $delivery_name, $delivery_phone, $delivery_address, $delivery_city, $delivery_pincode);
                $ins->execute();
                $customer_id = (int)$conn->insert_id;
                $ins->close();
            }

            // Insert sale record (user_id is NULL for guests)
            $sale_stmt = $conn->prepare(
                "INSERT INTO sales (customer_id, user_id, total_amount, tax_amount, final_amount, payment_method, notes)
                 VALUES (?, ?, ?, 0.00, ?, ?, ?)"
            );
            $sale_stmt->bind_param("iiddss", $customer_id, $user_id, $total, $total, $payment_method, $notes);
            $sale_stmt->execute();
            $sale_id = (int)$conn->insert_id;
            $sale_stmt->close();

            // Insert sale items and decrement stock
            foreach ($cart_items as $item) {
                $stk = $conn->query(
                    "SELECT stock_quantity FROM fish WHERE id = {$item['fish_id']}"
                )->fetch_assoc();
                if (!$stk || (int)$stk['stock_quantity'] < $item['quantity']) {
                    throw new Exception("Insufficient stock for: " . $item['name']);
                }

                $si = $conn->prepare(
                    "INSERT INTO sale_items (sale_id, fish_id, quantity, unit_price, total_price) VALUES (?,?,?,?,?)"
                );
                $si->bind_param("iiidd", $sale_id, $item['fish_id'], $item['quantity'], $item['selling_price'], $item['subtotal']);
                $si->execute();
                $si->close();

                $conn->query(
                    "UPDATE fish SET stock_quantity = stock_quantity - {$item['quantity']} WHERE id = {$item['fish_id']}"
                );

                // Low-stock notification
                $new_stock = (int)$stk['stock_quantity'] - $item['quantity'];
                if ($new_stock <= 5) {
                    $msg = "Low stock alert: {$item['name']} – only $new_stock remaining";
                    $conn->query(
                        "INSERT INTO notifications (type, message) VALUES ('low_stock', '" . $conn->real_escape_string($msg) . "')"
                    );
                }
            }

            // Clear cart
            if ($is_logged_in) {
                $conn->query("DELETE FROM cart WHERE user_id = $user_id");
            } else {
                $_SESSION['guest_cart'] = [];
            }

            $conn->commit();

            // Redirect with success
            if ($is_logged_in) {
                header("Location: my_orders.php?success=" . urlencode("Order placed! Your order ID is #$sale_id"));
            } else {
                header("Location: shop.php?msg=" . urlencode("Order placed! ID #$sale_id. Thank you, $delivery_name!"));
            }
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            log_error("Checkout failed: " . $e->getMessage(), __FILE__, __LINE__);
            $error = 'Order could not be placed: ' . htmlspecialchars($e->getMessage()) . '. Please try again.';
        }
    }
}

include 'includes/customer_header.php';
?>

<div class="container py-4">
    <h4 class="fw-bold mb-1"><i class="fas fa-credit-card me-2 text-primary"></i>Checkout</h4>
    <p class="text-muted mb-4">
        Complete your delivery details and confirm your order.
        <?php if (!$is_logged_in): ?>
            <span class="badge bg-info text-dark ms-1">
                <i class="fas fa-user-check me-1"></i>Guest Checkout — No account required
            </span>
        <?php endif; ?>
    </p>

    <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
    <?php echo csrf_field(); ?>
    <div class="row g-4">
        <!-- Delivery Details -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fas fa-map-marker-alt me-2"></i>Delivery Details
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="delivery_name" class="form-control" required
                                   value="<?php echo htmlspecialchars($_POST['delivery_name'] ?? $prefill['name']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" name="delivery_phone" class="form-control" required
                                   placeholder="10-digit mobile number"
                                   value="<?php echo htmlspecialchars($_POST['delivery_phone'] ?? $prefill['phone']); ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Street / Area <span class="text-danger">*</span></label>
                            <input type="text" name="delivery_address" class="form-control" required
                                   placeholder="Door no., Street, Area"
                                   value="<?php echo htmlspecialchars($_POST['delivery_address'] ?? $prefill['address']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">City</label>
                            <input type="text" name="delivery_city" class="form-control"
                                   placeholder="e.g. Cuddalore"
                                   value="<?php echo htmlspecialchars($_POST['delivery_city'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Pincode</label>
                            <input type="text" name="delivery_pincode" class="form-control"
                                   placeholder="e.g. 607001" pattern="[0-9]{6}" title="6-digit pincode"
                                   value="<?php echo htmlspecialchars($_POST['delivery_pincode'] ?? ''); ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-money-bill-wave me-1"></i>Payment Method <span class="text-danger">*</span>
                            </label>
                            <div class="row g-2 mt-1">
                                <?php
                                $pm_sel  = $_POST['payment_method'] ?? 'cash';
                                $methods = [
                                    'cash'   => ['icon' => 'money-bill-wave', 'label' => 'Cash'],
                                    'upi'    => ['icon' => 'qrcode',          'label' => 'UPI'],
                                    'card'   => ['icon' => 'credit-card',     'label' => 'Card'],
                                    'online' => ['icon' => 'globe',           'label' => 'Online'],
                                ];
                                foreach ($methods as $val => $m):
                                ?>
                                <div class="col-6 col-md-3">
                                    <input type="radio" class="btn-check" name="payment_method"
                                           id="pm_<?php echo $val; ?>" value="<?php echo $val; ?>"
                                           <?php echo $pm_sel === $val ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-secondary w-100" for="pm_<?php echo $val; ?>">
                                        <i class="fas fa-<?php echo $m['icon']; ?> d-block mb-1"></i>
                                        <?php echo $m['label']; ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Special Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="2"
                                      placeholder="Any special instructions for your order..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white fw-bold">
                    <i class="fas fa-receipt me-2"></i>Order Summary
                </div>
                <div class="card-body">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <div>
                                <div class="fw-semibold"><?php echo htmlspecialchars($item['name']); ?></div>
                                <small class="text-muted">
                                    &#8377;<?php echo number_format($item['selling_price'], 2); ?>
                                    &times; <?php echo $item['quantity']; ?>
                                </small>
                            </div>
                            <span class="fw-semibold">&#8377;<?php echo number_format($item['subtotal'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>

                    <div class="d-flex justify-content-between mt-2 mb-1">
                        <span class="text-muted">Sub-total</span>
                        <span>&#8377;<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Tax (GST)</span>
                        <span class="text-success">&#8377;0.00</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
                        <span>Grand Total</span>
                        <span class="text-success">&#8377;<?php echo number_format($total, 2); ?></span>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg fw-semibold">
                            <i class="fas fa-check-circle me-2"></i>Place Order
                        </button>
                        <a href="cart.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Cart
                        </a>
                    </div>
                </div>
            </div>

            <div class="card mt-3 border-0 bg-light">
                <div class="card-body py-2 px-3">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt me-1 text-success"></i>
                        <strong>Safe &amp; Secure</strong> — Live fish packed with oxygen,
                        delivered same-day within Cuddalore.
                    </small>
                </div>
            </div>
        </div>
    </div>
    </form>
</div>

<footer class="text-center text-muted py-4 mt-5 border-top bg-white">
    <small>&copy; <?php echo date('Y'); ?> Harini Aquarium &amp; Fish Shop, Cuddalore</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
