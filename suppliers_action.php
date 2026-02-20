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
    $contact = trim($_POST['contact_person'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    $stmt = $conn->prepare("INSERT INTO suppliers (name, contact_person, phone, email, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $contact, $phone, $email, $address);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Supplier added!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error adding supplier.";
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
    header("Location: suppliers.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM suppliers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['message'] = "Supplier deleted!";
    $_SESSION['msg_type'] = "warning";
    header("Location: suppliers.php");
    exit();
}
?>
