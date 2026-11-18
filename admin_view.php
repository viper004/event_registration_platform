<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

require_once 'db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: admin_dashboard.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM registrations WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$reg = $res->fetch_assoc();
$stmt->close();

if (!$reg) {
    die("Registration record not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Registration #<?php echo $reg['id']; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar" style="background-color: #0f172a;">
    <a href="admin_events.php" class="logo">⚡ CampusEvents Admin</a>
    <div class="nav-links">
        <a href="admin_events.php" style="color: #60a5fa;">Manage Events</a>
        <a href="admin_create_event.php">+ Create Event</a>
        <a href="index.php" target="_blank">View Site ↗</a>
        <a href="admin_logout.php" class="btn-admin" style="background:#ef4444; color:white !important;">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="form-card" style="max-width: 600px;">
        <h2 style="color: #1e3a8a; margin-bottom: 20px; text-align: center;">Registration Details</h2>

        <table class="summary-table">
            <tr>
                <td class="label">Registration ID:</td>
                <td><strong>#<?php echo htmlspecialchars($reg['id']); ?></strong></td>
            </tr>
            <tr>
                <td class="label">Student Name:</td>
                <td><strong><?php echo htmlspecialchars($reg['name']); ?></strong></td>
            </tr>
            <tr>
                <td class="label">Email Address:</td>
                <td><a href="mailto:<?php echo htmlspecialchars($reg['email']); ?>"><?php echo htmlspecialchars($reg['email']); ?></a></td>
            </tr>
            <tr>
                <td class="label">Event Name:</td>
                <td><?php echo htmlspecialchars($reg['event_name']); ?></td>
            </tr>
            <tr>
                <td class="label">Date Registered:</td>
                <td><?php echo htmlspecialchars($reg['date_registered']); ?></td>
            </tr>
        </table>

        <div style="margin-top: 25px; display: flex; justify-content: space-between;">
            <?php 
                $back_event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
                if ($back_event_id > 0): 
            ?>
                <a href="admin_event_attendees.php?id=<?php echo $back_event_id; ?>" class="btn btn-secondary">&larr; Back to Attendees</a>
            <?php else: ?>
                <a href="admin_events.php" class="btn btn-secondary">&larr; Back to Manage Events</a>
            <?php endif; ?>
            <a href="admin_delete.php?id=<?php echo $reg['id']; ?><?php echo ($back_event_id > 0) ? '&event_id=' . $back_event_id : ''; ?>" 
               class="btn btn-danger" 
               onclick="return confirmDelete('registration');">
               Delete Registration
            </a>
        </div>
    </div>
</div>

<footer>
    &copy; <?php echo date('Y'); ?> College Event Registration Platform - Admin Panel
</footer>

<script src="script.js"></script>
</body>
</html>
