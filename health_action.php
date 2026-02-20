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
    $fish_id = !empty($_POST['fish_id']) ? intval($_POST['fish_id']) : NULL;
    $tank_id = !empty($_POST['tank_id']) ? intval($_POST['tank_id']) : NULL;
    $disease = trim($_POST['disease_name']);
    $symptoms = trim($_POST['symptoms'] ?? '');
    $treatment = trim($_POST['treatment'] ?? '');

    $stmt = $conn->prepare("INSERT INTO fish_health (fish_id, tank_id, disease_name, symptoms, treatment) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $fish_id, $tank_id, $disease, $symptoms, $treatment);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Health issue recorded";
        $_SESSION['msg_type'] = "warning";
    } else {
        $_SESSION['message'] = "Error recording health issue.";
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
    header("Location: fish_health.php");
    exit();
}

if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $status = $_POST['status'];
    $treatment = trim($_POST['treatment'] ?? '');

    $stmt = $conn->prepare("UPDATE fish_health SET status=?, treatment=? WHERE id=?");
    $stmt->bind_param("ssi", $status, $treatment, $id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Status updated";
    $_SESSION['msg_type'] = "success";
    header("Location: fish_health.php");
    exit();
}
?>
