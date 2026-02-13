<?php
session_start();
require_once 'config/db_connect.php';

if (isset($_POST['save'])) {
    $tank_id = $_POST['tank_id'];
    $activity = $_POST['activity_type'];
    $desc = $_POST['description'];
    $next_date = !empty($_POST['next_date']) ? $_POST['next_date'] : NULL;
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO maintenance_log (tank_id, performed_by, activity_type, description, next_scheduled_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $tank_id, $user_id, $activity, $desc, $next_date);

    if ($stmt->execute()) {

        // If next date is set, create a notification
        if ($next_date) {
            $msg = "Maintenance due for Tank ID $tank_id: $activity";
            $stmt2 = $conn->prepare("INSERT INTO notifications (type, message) VALUES ('maintenance', ?)");
            $stmt2->bind_param("s", $msg);
            $stmt2->execute();
        }

        $_SESSION['message'] = "Maintenance logged successfully";
        $_SESSION['msg_type'] = "success";
    }
    header("Location: maintenance.php");
}
?>