<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Admins/staff have no cart — redirect them to their dashboard
if (isset($_SESSION['user_id']) && in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: dashboard.php");
    exit();
}

require_once 'config/db_connect.php';
require_once 'includes/error_logger.php';

$action       = $_POST['action'] ?? $_GET['action'] ?? '';
$is_logged_in = isset($_SESSION['user_id']) && ($_SESSION['role'] === 'customer');
$user_id      = $is_logged_in ? (int)$_SESSION['user_id'] : 0;

// CSRF validation for all state-changing POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
}

// Ensure guest cart array exists
if (!isset($_SESSION['guest_cart'])) {
    $_SESSION['guest_cart'] = [];
}

switch ($action) {

    // ── ADD TO CART ──────────────────────────────────────────────────────────
    case 'add':
        $fish_id  = (int)($_POST['fish_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        if ($fish_id <= 0) {
            header("Location: shop.php?err=Invalid fish selection.");
            exit();
        }

        // Verify stock
        $st = $conn->prepare("SELECT stock_quantity, name FROM fish WHERE id = ?");
        $st->bind_param("i", $fish_id);
        $st->execute();
        $fish = $st->get_result()->fetch_assoc();
        $st->close();

        if (!$fish || $fish['stock_quantity'] < $quantity) {
            $fname = $fish['name'] ?? 'this fish';
            header("Location: shop.php?err=" . urlencode("Not enough stock for $fname."));
            exit();
        }

        if ($is_logged_in) {
            // DB cart for logged-in customers
            $stmt = $conn->prepare(
                "INSERT INTO cart (user_id, fish_id, quantity)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)"
            );
            $stmt->bind_param("iii", $user_id, $fish_id, $quantity);
            $stmt->execute();
            $stmt->close();
        } else {
            // Session cart for guests
            $current = $_SESSION['guest_cart'][$fish_id] ?? 0;
            $_SESSION['guest_cart'][$fish_id] = min($current + $quantity, $fish['stock_quantity']);
        }

        header("Location: shop.php?msg=" . urlencode($fish['name'] . " added to cart!"));
        exit();

    // ── UPDATE QUANTITY ──────────────────────────────────────────────────────
    case 'update':
        $fish_id  = (int)($_POST['fish_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        $st = $conn->prepare("SELECT stock_quantity FROM fish WHERE id = ?");
        $st->bind_param("i", $fish_id);
        $st->execute();
        $stock = (int)($st->get_result()->fetch_assoc()['stock_quantity'] ?? 0);
        $st->close();
        if ($quantity > $stock) $quantity = $stock;

        if ($is_logged_in) {
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND fish_id = ?");
            $stmt->bind_param("iii", $quantity, $user_id, $fish_id);
            $stmt->execute();
            $stmt->close();
        } else {
            if (array_key_exists($fish_id, $_SESSION['guest_cart'])) {
                $_SESSION['guest_cart'][$fish_id] = $quantity;
            }
        }

        header("Location: cart.php?msg=Cart updated.");
        exit();

    // ── REMOVE ITEM ──────────────────────────────────────────────────────────
    case 'remove':
        $fish_id = (int)($_POST['fish_id'] ?? $_GET['fish_id'] ?? 0);

        if ($is_logged_in) {
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND fish_id = ?");
            $stmt->bind_param("ii", $user_id, $fish_id);
            $stmt->execute();
            $stmt->close();
        } else {
            unset($_SESSION['guest_cart'][$fish_id]);
        }

        header("Location: cart.php?msg=Item removed from cart.");
        exit();

    // ── CLEAR CART ───────────────────────────────────────────────────────────
    case 'clear':
        if ($is_logged_in) {
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
        } else {
            $_SESSION['guest_cart'] = [];
        }

        header("Location: cart.php?msg=Cart cleared.");
        exit();

    default:
        header("Location: shop.php");
        exit();
}
?>
