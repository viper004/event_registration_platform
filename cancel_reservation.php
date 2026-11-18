<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];

if ($id > 0) {
    // Secure delete: Only delete if the reservation belongs to this logged-in user
    $stmt = $conn->prepare("DELETE FROM registrations WHERE id = ? AND (user_id = ? OR email = ?)");
    $stmt->bind_param("iis", $id, $user_id, $user_email);
    $stmt->execute();
    $stmt->close();
}

header("Location: my_reservations.php?cancelled=1");
exit();
?>
