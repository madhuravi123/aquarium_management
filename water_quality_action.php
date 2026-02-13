<?php
session_start();
require_once 'config/db_connect.php';

if (isset($_POST['save'])) {
    $tank_id = $_POST['tank_id'];
    $ph = $_POST['ph_level'];
    $temp = $_POST['temperature'];
    $ammonia = $_POST['ammonia'];
    $nitrite = $_POST['nitrite'];
    $nitrate = $_POST['nitrate'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO water_quality (tank_id, recorded_by, ph_level, temperature, ammonia_level, nitrite_level, nitrate_level) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiddddd", $tank_id, $user_id, $ph, $temp, $ammonia, $nitrite, $nitrate);

    if ($stmt->execute()) {
        // Update tank current stats
        $stmt2 = $conn->prepare("UPDATE tanks SET ph_level=?, temperature=? WHERE id=?");
        $stmt2->bind_param("ddi", $ph, $temp, $tank_id);
        $stmt2->execute();

        $_SESSION['message'] = "Water quality recorded!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
        $_SESSION['msg_type'] = "danger";
    }
    header("Location: water_quality.php");
}
?>