<?php
session_start();
require_once 'config/db_connect.php';

if (isset($_POST['log_feed'])) {
    $tank_id = $_POST['tank_id'];
    $food = $_POST['food_type'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO feeding_logs (tank_id, fed_by, food_type) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $tank_id, $user_id, $food);
    $stmt->execute();
    header("Location: feeding.php");
}

if (isset($_POST['save_sched'])) {
    $tank_id = $_POST['tank_id'];
    $food = $_POST['food_type'];
    $time = $_POST['feeding_time'];
    $qty = $_POST['quantity'];

    $stmt = $conn->prepare("INSERT INTO feeding_schedule (tank_id, food_type, feeding_time, quantity_grams) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issd", $tank_id, $food, $time, $qty);
    $stmt->execute();
    header("Location: feeding.php");
}

if (isset($_GET['delete_sched'])) {
    $id = $_GET['delete_sched'];
    $conn->query("DELETE FROM feeding_schedule WHERE id=$id");
    header("Location: feeding.php");
}
?>