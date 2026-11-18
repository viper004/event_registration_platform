<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

require_once 'db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM registrations WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
if ($event_id > 0) {
    header("Location: admin_events.php?open=" . $event_id);
} else {
    header("Location: admin_events.php");
}
exit();
?>
