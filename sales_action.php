<?php
session_start();
require_once 'config/db_connect.php';

if (isset($_POST['save_sale'])) {
    $customer_id = !empty($_POST['customer_id']) ? $_POST['customer_id'] : NULL;
    $sale_date = $_POST['sale_date'];
    $payment_method = $_POST['payment_method'];
    $total_amount = $_POST['total_amount'];
    $items = $_POST['items'];

    // Start Transaction
    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO sales (customer_id, total_amount, final_amount, payment_method, sale_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iddss", $customer_id, $total_amount, $total_amount, $payment_method, $sale_date);
        $stmt->execute();
        $sale_id = $conn->insert_id;

        $stmt_item = $conn->prepare("INSERT INTO sale_items (sale_id, fish_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");

        foreach ($items as $item) {
            $fish_id = $item['fish_id'];
            $qty = $item['quantity'];
            $price = $item['unit_price'];
            $total = $qty * $price;

            $stmt_item->bind_param("iiidd", $sale_id, $fish_id, $qty, $price, $total);
            $stmt_item->execute();

            // Decrease Stock
            $conn->query("UPDATE fish SET stock_quantity = stock_quantity - $qty WHERE id = $fish_id");
        }

        $conn->commit();
        $_SESSION['message'] = "Sale completed successfully!";
        $_SESSION['msg_type'] = "success";
        header("Location: sales.php"); // Or redirect to invoice print
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['msg_type'] = "danger";
        header("Location: sales_new.php");
    }
}
?>