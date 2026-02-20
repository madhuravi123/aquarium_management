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

if (isset($_POST['save_sale'])) {
    $customer_id = !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : NULL;
    $sale_date = $_POST['sale_date'];
    $payment_method = $_POST['payment_method'];
    $total_amount = floatval($_POST['total_amount']);
    $items = $_POST['items'] ?? [];

    if (empty($items) || $total_amount <= 0) {
        $_SESSION['message'] = "Please add at least one item to the sale.";
        $_SESSION['msg_type'] = "danger";
        header("Location: sales_new.php");
        exit();
    }

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO sales (customer_id, total_amount, final_amount, payment_method, sale_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iddss", $customer_id, $total_amount, $total_amount, $payment_method, $sale_date);
        $stmt->execute();
        $sale_id = $conn->insert_id;
        $stmt->close();

        $stmt_item = $conn->prepare("INSERT INTO sale_items (sale_id, fish_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
        $stmt_stock = $conn->prepare("UPDATE fish SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?");

        foreach ($items as $item) {
            if (empty($item['fish_id'])) continue;
            $fish_id = intval($item['fish_id']);
            $qty = intval($item['quantity']);
            $price = floatval($item['unit_price']);
            $total = $qty * $price;

            $stmt_item->bind_param("iiidd", $sale_id, $fish_id, $qty, $price, $total);
            $stmt_item->execute();

            // Decrease stock safely
            $stmt_stock->bind_param("iii", $qty, $fish_id, $qty);
            $stmt_stock->execute();

            if ($stmt_stock->affected_rows == 0) {
                throw new Exception("Insufficient stock for fish ID: $fish_id");
            }
        }
        $stmt_item->close();
        $stmt_stock->close();

        // Generate low stock notifications
        $low = $conn->query("SELECT id, name, stock_quantity FROM fish WHERE stock_quantity <= 5 AND stock_quantity > 0");
        while ($row = $low->fetch_assoc()) {
            $msg = "Low stock alert: " . $row['name'] . " - only " . $row['stock_quantity'] . " remaining";
            $check = $conn->prepare("SELECT id FROM notifications WHERE message = ? AND is_read = 0");
            $check->bind_param("s", $msg);
            $check->execute();
            if ($check->get_result()->num_rows == 0) {
                $ins = $conn->prepare("INSERT INTO notifications (type, message) VALUES ('low_stock', ?)");
                $ins->bind_param("s", $msg);
                $ins->execute();
                $ins->close();
            }
            $check->close();
        }

        $conn->commit();
        $_SESSION['message'] = "Sale completed successfully!";
        $_SESSION['msg_type'] = "success";
        header("Location: sales.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['msg_type'] = "danger";
        header("Location: sales_new.php");
        exit();
    }
}
?>
