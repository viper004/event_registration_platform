<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $event_date = trim($_POST['event_date'] ?? '');
    $venue = trim($_POST['venue'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name) || empty($event_date) || empty($venue) || empty($description)) {
        $error = "Please fill in all event fields.";
    } else {
        $stmt = $conn->prepare("INSERT INTO events (name, event_date, venue, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $event_date, $venue, $description);

        if ($stmt->execute()) {
            header("Location: admin_events.php");
            exit();
        } else {
            $error = "Error adding event: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar" style="background-color: #0f172a;">
    <a href="admin_events.php" class="logo">⚡ CampusEvents Admin</a>
    <div class="nav-links">
        <a href="admin_events.php">Manage Events</a>
        <a href="admin_create_event.php" style="color: #60a5fa;">+ Create Event</a>
        <a href="index.php" target="_blank">View Site ↗</a>
        <a href="admin_logout.php" class="btn-admin" style="background:#ef4444; color:white !important;">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="form-card" style="max-width: 600px;">
        <h2 style="color: #1e3a8a; margin-bottom: 20px; text-align: center;">Create New College Event</h2>

        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="admin_create_event.php" method="POST">
            <div class="form-group">
                <label for="name">Event Name (e.g. Workshop / Hackathon Title):</label>
                <input type="text" id="name" name="name" placeholder="e.g. Python for Data Science Workshop" required>
            </div>

            <div class="form-group">
                <label for="event_date">Event Date:</label>
                <input type="date" id="event_date" name="event_date" required>
            </div>

            <div class="form-group">
                <label for="venue">Venue / Location:</label>
                <input type="text" id="venue" name="venue" placeholder="e.g. Lab 2, Tech Block / Main Auditorium" required>
            </div>

            <div class="form-group">
                <label for="description">Event Description:</label>
                <textarea id="description" name="description" rows="4" placeholder="Briefly describe the event topics, schedule, and prerequisites..." required></textarea>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <button type="submit" class="btn" style="flex: 1;">Save & Publish Event</button>
                <a href="admin_events.php" class="btn btn-secondary" style="padding: 10px 20px;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<footer>
    &copy; <?php echo date('Y'); ?> College Event Registration Platform - Admin Panel
</footer>

</body>
</html>
