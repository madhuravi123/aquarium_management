<?php
session_start();
require_once 'config/db_connect.php';

if (isset($_POST['save'])) {
    $name = $_POST['name'];
    $contact = $_POST['contact_person'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("INSERT INTO suppliers (name, contact_person, phone, email, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $contact, $phone, $email, $address);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Supplier added!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
        $_SESSION['msg_type'] = "danger";
    }
    header("Location: suppliers.php");
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM suppliers WHERE id=$id");
    $_SESSION['message'] = "Supplier deleted!";
    $_SESSION['msg_type'] = "danger";
    header("Location: suppliers.php");
}
?>