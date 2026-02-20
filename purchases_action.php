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

if (isset($_POST['save_purchase'])) {
    $supplier_id = intval($_POST['supplier_id']);
    $purchase_date = $_POST['purchase_date'];
    $total_amount = floatval($_POST['total_amount']);
    $items = $_POST['items'] ?? [];

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO purchases (supplier_id, total_amount, purchase_date, status) VALUES (?, ?, ?, 'completed')");
        $stmt->bind_param("ids", $supplier_id, $total_amount, $purchase_date);
        $stmt->execute();
        $purchase_id = $conn->insert_id;
        $stmt->close();

        $stmt_item = $conn->prepare("INSERT INTO purchase_items (purchase_id, fish_id, item_name, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_stock = $conn->prepare("UPDATE fish SET stock_quantity = stock_quantity + ? WHERE id = ?");

        foreach ($items as $item) {
            $fish_id = (!empty($item['type']) && $item['type'] == 'fish' && !empty($item['fish_id'])) ? intval($item['fish_id']) : NULL;
            $item_name = (!empty($item['type']) && $item['type'] == 'supply' && !empty($item['item_name'])) ? trim($item['item_name']) : NULL;
            $qty = intval($item['quantity']);
            $price = floatval($item['unit_price']);
            $total = $qty * $price;

            $stmt_item->bind_param("iisidd", $purchase_id, $fish_id, $item_name, $qty, $price, $total);
            $stmt_item->execute();

            // Update fish stock
            if ($fish_id) {
                $stmt_stock->bind_param("ii", $qty, $fish_id);
                $stmt_stock->execute();
            }
        }
        $stmt_item->close();
        $stmt_stock->close();

        $conn->commit();
        $_SESSION['message'] = "Purchase recorded successfully!";
        $_SESSION['msg_type'] = "success";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['msg_type'] = "danger";
    }

    header("Location: purchases.php");
    exit();
}
?>
