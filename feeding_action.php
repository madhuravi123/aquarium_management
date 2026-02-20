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

if (isset($_POST['log_feed'])) {
    $tank_id = intval($_POST['tank_id']);
    $food = trim($_POST['food_type']);
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO feeding_logs (tank_id, fed_by, food_type) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $tank_id, $user_id, $food);
    $stmt->execute();
    $stmt->close();
    header("Location: feeding.php");
    exit();
}

if (isset($_POST['save_sched'])) {
    $tank_id = intval($_POST['tank_id']);
    $food = trim($_POST['food_type']);
    $time = $_POST['feeding_time'];
    $qty = floatval($_POST['quantity']);

    $stmt = $conn->prepare("INSERT INTO feeding_schedule (tank_id, food_type, feeding_time, quantity_grams) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issd", $tank_id, $food, $time, $qty);
    $stmt->execute();
    $stmt->close();
    header("Location: feeding.php");
    exit();
}

if (isset($_GET['delete_sched'])) {
    $id = intval($_GET['delete_sched']);
    $stmt = $conn->prepare("DELETE FROM feeding_schedule WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: feeding.php");
    exit();
}
?>
