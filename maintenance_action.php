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
    $activity = trim($_POST['activity_type']);
    $desc = trim($_POST['description'] ?? '');
    $next_date = !empty($_POST['next_date']) ? $_POST['next_date'] : NULL;
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO maintenance_log (tank_id, performed_by, activity_type, description, next_scheduled_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $tank_id, $user_id, $activity, $desc, $next_date);

    if ($stmt->execute()) {
        if ($next_date) {
            $msg = "Maintenance due for Tank ID $tank_id: $activity on $next_date";
            $stmt2 = $conn->prepare("INSERT INTO notifications (type, message) VALUES ('maintenance', ?)");
            $stmt2->bind_param("s", $msg);
            $stmt2->execute();
            $stmt2->close();
        }

        $_SESSION['message'] = "Maintenance logged successfully";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error logging maintenance.";
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
    header("Location: maintenance.php");
    exit();
}
?>
