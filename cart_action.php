<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: index.php");
    exit();
}
require_once 'config/db_connect.php';

$action  = $_POST['action'] ?? $_GET['action'] ?? '';
$user_id = (int)$_SESSION['user_id'];

switch ($action) {

    case 'add':
        $fish_id  = (int)($_POST['fish_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));
        if ($fish_id <= 0) {
            header("Location: shop.php?err=Invalid fish selection.");
            exit();
        }
        // Check stock
        $st = $conn->prepare("SELECT stock_quantity, name FROM fish WHERE id = ?");
        $st->bind_param("i", $fish_id);
        $st->execute();
        $fish = $st->get_result()->fetch_assoc();
        if (!$fish || $fish['stock_quantity'] < $quantity) {
            header("Location: shop.php?err=Not enough stock available for " . urlencode($fish['name'] ?? 'this fish'));
            exit();
        }
        // Upsert into cart
        $stmt = $conn->prepare(
            "INSERT INTO cart (user_id, fish_id, quantity)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)"
        );
        $stmt->bind_param("iii", $user_id, $fish_id, $quantity);
        $stmt->execute();
        header("Location: shop.php?msg=" . urlencode($fish['name'] . " added to cart!"));
        exit();

    case 'update':
        $fish_id  = (int)($_POST['fish_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));
        // Check stock
        $st = $conn->prepare("SELECT stock_quantity FROM fish WHERE id = ?");
        $st->bind_param("i", $fish_id);
        $st->execute();
        $stock = $st->get_result()->fetch_assoc()['stock_quantity'] ?? 0;
        if ($quantity > $stock) $quantity = $stock;
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND fish_id = ?");
        $stmt->bind_param("iii", $quantity, $user_id, $fish_id);
        $stmt->execute();
        header("Location: cart.php?msg=Cart updated.");
        exit();

    case 'remove':
        $fish_id = (int)($_POST['fish_id'] ?? $_GET['fish_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND fish_id = ?");
        $stmt->bind_param("ii", $user_id, $fish_id);
        $stmt->execute();
        header("Location: cart.php?msg=Item removed from cart.");
        exit();

    case 'clear':
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        header("Location: cart.php?msg=Cart cleared.");
        exit();

    default:
        header("Location: shop.php");
        exit();
}
?>
