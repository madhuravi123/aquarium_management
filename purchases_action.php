<?php
session_start();
require_once 'config/db_connect.php';

if (isset($_POST['save_purchase'])) {
    $supplier_id = $_POST['supplier_id'];
    $purchase_date = $_POST['purchase_date'];
    $total_amount = $_POST['total_amount'];
    $items = $_POST['items'];

    // Start Transaction
    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO purchases (supplier_id, total_amount, purchase_date, status) VALUES (?, ?, ?, 'completed')");
        $stmt->bind_param("ids", $supplier_id, $total_amount, $purchase_date);
        $stmt->execute();
        $purchase_id = $conn->insert_id;

        $stmt_item = $conn->prepare("INSERT INTO purchase_items (purchase_id, fish_id, item_name, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?)");

        foreach ($items as $item) {
            $fish_id = ($item['type'] == 'fish' && !empty($item['fish_id'])) ? $item['fish_id'] : NULL;
            $item_name = ($item['type'] == 'supply') ? $item['item_name'] : NULL;
            $qty = $item['quantity'];
            $price = $item['unit_price'];
            $total = $qty * $price;

            $stmt_item->bind_param("iisidd", $purchase_id, $fish_id, $item_name, $qty, $price, $total);
            $stmt_item->execute();

            // Update Stock
            if ($fish_id) {
                $conn->query("UPDATE fish SET stock_quantity = stock_quantity + $qty WHERE id = $fish_id");
            }
            // Logic for Inventory items could go here if we had an inventory table linked by name, but keeping it simple for now.
        }

        $conn->commit();
        $_SESSION['message'] = "Purchase recorded successfully!";
        $_SESSION['msg_type'] = "success";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['msg_type'] = "danger";
    }

    header("Location: purchases.php");
}
?>