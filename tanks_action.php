<?php
session_start();
require_once 'config/db_connect.php';

if (isset($_POST['save'])) {
    $name = $_POST['name'];
    $capacity = $_POST['capacity'];
    $water_type = $_POST['water_type'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO tanks (name, capacity, water_type, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdss", $name, $capacity, $water_type, $status);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Tank added successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
        $_SESSION['msg_type'] = "danger";
    }
    header("Location: tanks.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM tanks WHERE id=$id");
    $_SESSION['message'] = "Tank deleted!";
    $_SESSION['msg_type'] = "danger";
    header("Location: tanks.php");
    exit();
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $capacity = $_POST['capacity'];
    $water_type = $_POST['water_type'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE tanks SET name=?, capacity=?, water_type=?, status=? WHERE id=?");
    $stmt->bind_param("sdssi", $name, $capacity, $water_type, $status, $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Tank updated successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
        $_SESSION['msg_type'] = "danger";
    }
    header("Location: tanks.php");
    exit();
}
?>