<?php
session_start();
require_once 'config/db_connect.php';

if (isset($_POST['save'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("INSERT INTO customers (name, phone, email, address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $phone, $email, $address);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Customer added!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
        $_SESSION['msg_type'] = "danger";
    }
    header("Location: customers.php");
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM customers WHERE id=$id");
    $_SESSION['message'] = "Customer deleted!";
    $_SESSION['msg_type'] = "danger";
    header("Location: customers.php");
}
?>