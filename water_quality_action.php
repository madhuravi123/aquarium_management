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
    $tank_id = intval($_POST['tank_id']);
    $ph = floatval($_POST['ph_level']);
    $temp = floatval($_POST['temperature']);
    $ammonia = floatval($_POST['ammonia'] ?? 0);
    $nitrite = floatval($_POST['nitrite'] ?? 0);
    $nitrate = floatval($_POST['nitrate'] ?? 0);
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO water_quality (tank_id, recorded_by, ph_level, temperature, ammonia_level, nitrite_level, nitrate_level) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiddddd", $tank_id, $user_id, $ph, $temp, $ammonia, $nitrite, $nitrate);

    if ($stmt->execute()) {
        // Update tank current stats
        $stmt2 = $conn->prepare("UPDATE tanks SET ph_level=?, temperature=? WHERE id=?");
        $stmt2->bind_param("ddi", $ph, $temp, $tank_id);
        $stmt2->execute();
        $stmt2->close();

        $_SESSION['message'] = "Water quality recorded!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error recording water quality.";
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
    header("Location: water_quality.php");
    exit();
}
?>
