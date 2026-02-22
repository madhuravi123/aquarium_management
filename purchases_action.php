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
    // Always combine the chosen date with the current time of recording.
    $purchase_date_raw = trim($_POST['purchase_date'] ?? date('Y-m-d'));
    $purchase_date = $purchase_date_raw . ' ' . date('H:i:s');
    $total_amount = floatval($_POST['total_amount']);
    $items = $_POST['items'] ?? [];
    $is_restock = isset($_POST['is_restock']) && $_POST['is_restock'] == '1';
    $restock_fish_id = isset($_POST['restock_fish_id']) ? intval($_POST['restock_fish_id']) : null;

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
        
        // If this was a restock from reports, redirect back to reports
        if ($is_restock) {
            // Get updated stock for the restocked fish
            $stock_check = $conn->query("SELECT name, stock_quantity FROM fish WHERE id = $restock_fish_id");
            if ($stock_check && $fish_data = $stock_check->fetch_assoc()) {
                $_SESSION['message'] = "Restocked {$fish_data['name']} successfully! New stock: {$fish_data['stock_quantity']}";
            }
            header("Location: reports.php");
            exit();
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['msg_type'] = "danger";
    }

    header("Location: purchases.php");
    exit();
}
?>
