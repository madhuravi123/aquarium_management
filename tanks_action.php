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
    $capacity = floatval($_POST['capacity']);
    $water_type = $_POST['water_type'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO tanks (name, capacity, water_type, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdss", $name, $capacity, $water_type, $status);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Tank added successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error adding tank.";
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
    header("Location: tanks.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM tanks WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['message'] = "Tank deleted!";
    $_SESSION['msg_type'] = "warning";
    header("Location: tanks.php");
    exit();
}

if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $capacity = floatval($_POST['capacity']);
    $water_type = $_POST['water_type'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE tanks SET name=?, capacity=?, water_type=?, status=? WHERE id=?");
    $stmt->bind_param("sdssi", $name, $capacity, $water_type, $status, $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Tank updated successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error updating tank.";
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
    header("Location: tanks.php");
    exit();
}
?>
