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

if (isset($_POST['save'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    $stmt = $conn->prepare("INSERT INTO customers (name, phone, email, address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $phone, $email, $address);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Customer added!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error adding customer.";
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
    header("Location: customers.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['message'] = "Customer deleted!";
    $_SESSION['msg_type'] = "warning";
    header("Location: customers.php");
    exit();
}
?>
