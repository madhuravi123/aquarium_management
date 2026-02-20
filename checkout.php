<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: index.php");
    exit();
}
require_once 'config/db_connect.php';
$page_title = 'Checkout';

$user_id = (int)$_SESSION['user_id'];

// Fetch cart
$cart_res = $conn->query("
    SELECT c.fish_id, c.quantity, f.name, f.selling_price, f.stock_quantity
    FROM cart c JOIN fish f ON c.fish_id = f.id
    WHERE c.user_id = $user_id
");
$cart_items = [];
$total = 0.0;
while ($row = $cart_res->fetch_assoc()) {
    $row['subtotal'] = $row['quantity'] * $row['selling_price'];
    $total += $row['subtotal'];
    $cart_items[] = $row;
}

if (empty($cart_items)) {
    header("Location: cart.php?msg=Your cart is empty.");
    exit();
}

// Fetch user profile for pre-fill
$user_res = $conn->query("SELECT full_name, phone, address FROM users WHERE id = $user_id");
$user_profile = $user_res->fetch_assoc();

// Handle order placement
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delivery_name    = trim($_POST['delivery_name'] ?? '');
    $delivery_phone   = trim($_POST['delivery_phone'] ?? '');
    $delivery_address = trim($_POST['delivery_address'] ?? '');
    $payment_method   = $_POST['payment_method'] ?? 'cash';
    $notes            = trim($_POST['notes'] ?? '');

    if (!$delivery_name || !$delivery_phone || !$delivery_address) {
        $error = 'Please fill in all delivery details.';
    } elseif (!in_array($payment_method, ['cash', 'card', 'online', 'upi'])) {
        $error = 'Invalid payment method selected.';
    } else {
        $conn->begin_transaction();
        try {
            // Find or create customer record
            $stmt = $conn->prepare("SELECT id FROM customers WHERE phone = ?");
            $stmt->bind_param("s", $delivery_phone);
            $stmt->execute();
            $cust = $stmt->get_result()->fetch_assoc();

            if ($cust) {
                $customer_id = $cust['id'];
                // Update customer name/address if changed
                $upd = $conn->prepare("UPDATE customers SET name = ?, address = ? WHERE id = ?");
                $upd->bind_param("ssi", $delivery_name, $delivery_address, $customer_id);
                $upd->execute();
            } else {
                $ins = $conn->prepare("INSERT INTO customers (name, phone, address) VALUES (?, ?, ?)");
                $ins->bind_param("sss", $delivery_name, $delivery_phone, $delivery_address);
                $ins->execute();
                $customer_id = $conn->insert_id;
            }

            // Insert sale
            $sale_stmt = $conn->prepare(
                "INSERT INTO sales (customer_id, user_id, total_amount, tax_amount, final_amount, payment_method, notes)
                 VALUES (?, ?, ?, 0.00, ?, ?, ?)"
            );
            $sale_stmt->bind_param("iiddss", $customer_id, $user_id, $total, $total, $payment_method, $notes);
            $sale_stmt->execute();
            $sale_id = $conn->insert_id;

            // Insert sale items and decrement stock
            foreach ($cart_items as $item) {
                // Re-check stock
                $stk = $conn->query("SELECT stock_quantity FROM fish WHERE id = {$item['fish_id']}")->fetch_assoc();
                if (!$stk || $stk['stock_quantity'] < $item['quantity']) {
                    throw new Exception("Insufficient stock for: " . $item['name']);
                }

                $si = $conn->prepare(
                    "INSERT INTO sale_items (sale_id, fish_id, quantity, unit_price, total_price) VALUES (?,?,?,?,?)"
                );
                $si->bind_param("iiidd", $sale_id, $item['fish_id'], $item['quantity'], $item['selling_price'], $item['subtotal']);
                $si->execute();

                // Decrement stock
                $conn->query("UPDATE fish SET stock_quantity = stock_quantity - {$item['quantity']} WHERE id = {$item['fish_id']}");

                // Low-stock notification
                $new_stock = $stk['stock_quantity'] - $item['quantity'];
                if ($new_stock <= 5) {
                    $msg = "Low stock alert: {$item['name']} – only $new_stock remaining";
                    $conn->query("INSERT INTO notifications (type, message) VALUES ('low_stock', '" . $conn->real_escape_string($msg) . "')");
                }
            }

            // Clear cart
            $conn->query("DELETE FROM cart WHERE user_id = $user_id");

            $conn->commit();
            header("Location: my_orders.php?success=Order+placed+successfully!+Your+order+ID+is+%23$sale_id");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Order failed: ' . $e->getMessage();
        }
    }
}

include 'includes/customer_header.php';
?>

<div class="container py-4">
    <h4 class="fw-bold mb-1"><i class="fas fa-credit-card me-2 text-primary"></i>Checkout</h4>
    <p class="text-muted mb-4">Complete your delivery details and confirm order.</p>

    <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
    <div class="row g-4">
        <!-- Delivery Details -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fas fa-map-marker-alt me-2"></i>Delivery Details
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="delivery_name" class="form-control" required
                               value="<?php echo htmlspecialchars($user_profile['full_name'] ?? $_SESSION['full_name'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" name="delivery_phone" class="form-control" required
                               value="<?php echo htmlspecialchars($user_profile['phone'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Delivery Address <span class="text-danger">*</span></label>
                        <textarea name="delivery_address" class="form-control" rows="3" required><?php echo htmlspecialchars($user_profile['address'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold"><i class="fas fa-money-bill-wave me-1"></i>Payment Method <span class="text-danger">*</span></label>
                        <div class="row g-2 mt-1">
                            <div class="col-6 col-md-3">
                                <input type="radio" class="btn-check" name="payment_method" id="pm_cash" value="cash" checked>
                                <label class="btn btn-outline-secondary w-100" for="pm_cash">
                                    <i class="fas fa-money-bill-wave d-block mb-1"></i>Cash
                                </label>
                            </div>
                            <div class="col-6 col-md-3">
                                <input type="radio" class="btn-check" name="payment_method" id="pm_upi" value="upi">
                                <label class="btn btn-outline-secondary w-100" for="pm_upi">
                                    <i class="fas fa-qrcode d-block mb-1"></i>UPI
                                </label>
                            </div>
                            <div class="col-6 col-md-3">
                                <input type="radio" class="btn-check" name="payment_method" id="pm_card" value="card">
                                <label class="btn btn-outline-secondary w-100" for="pm_card">
                                    <i class="fas fa-credit-card d-block mb-1"></i>Card
                                </label>
                            </div>
                            <div class="col-6 col-md-3">
                                <input type="radio" class="btn-check" name="payment_method" id="pm_online" value="online">
                                <label class="btn btn-outline-secondary w-100" for="pm_online">
                                    <i class="fas fa-globe d-block mb-1"></i>Online
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Special Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Any special instructions for your order..."></textarea>
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
                                <small class="text-muted">&#8377;<?php echo number_format($item['selling_price'], 2); ?> × <?php echo $item['quantity']; ?></small>
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
                        <strong>Safe &amp; Secure</strong> – Your order is handled with care by our team.
                        Live fish are packed with oxygen and delivered same-day within Cuddalore.
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
