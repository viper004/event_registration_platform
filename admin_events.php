<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

require_once 'db.php';

// Fetch all events along with their registration counts
$query = "SELECT e.*, 
          (SELECT COUNT(*) FROM registrations r WHERE r.event_name = e.name) AS reg_count
          FROM events e 
          ORDER BY e.event_date ASC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navbar (No Registrations tab) -->
<nav class="navbar" style="background-color: #0f172a;">
    <a href="admin_events.php" class="logo">⚡ CampusEvents Admin</a>
    <div class="nav-links">
        <a href="admin_events.php" style="color: #60a5fa; font-weight: 600;">Manage Events</a>
        <a href="admin_create_event.php">+ Create Event</a>
        <a href="index.php" target="_blank">View Site ↗</a>
        <a href="admin_logout.php" class="btn-admin" style="background:#ef4444; color:white !important;">Logout</a>
    </div>
</nav>

<div class="container" style="max-width: 1100px;">
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h1 style="color: #0f172a; font-size: 1.85rem; margin-bottom: 5px;">Manage College Events</h1>
            <p style="color: #64748b;">View and manage all active workshops, hackathons, and seminars.</p>
        </div>
        <a href="admin_create_event.php" class="btn">+ Add New Event</a>
    </div>

    <!-- Events Table -->
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Event Name</th>
                    <th>Event Date</th>
                    <th>Venue</th>
                    <th>Registrations</th>
                    <th style="text-align: center; width: 220px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#<?php echo htmlspecialchars($row['id']); ?></strong></td>
                            <td>
                                <strong style="color: #1e293b; font-size: 1rem;">
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </strong>
                                <p style="font-size: 0.82rem; color: #64748b; margin-top: 4px; max-width: 380px;">
                                    <?php echo htmlspecialchars(substr($row['description'], 0, 95)) . (strlen($row['description']) > 95 ? '...' : ''); ?>
                                </p>
                            </td>
                            <td>📅 <?php echo htmlspecialchars($row['event_date']); ?></td>
                            <td>📍 <?php echo htmlspecialchars($row['venue']); ?></td>
                            <td>
                                <!-- Static Registered Count (No drop down option) -->
                                <?php if ($row['reg_count'] > 0): ?>
                                    <span style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 0.85rem; display: inline-block;">
                                        👥 <?php echo $row['reg_count']; ?> registered
                                    </span>
                                <?php else: ?>
                                    <span style="background: #f1f5f9; color: #94a3b8; padding: 4px 10px; border-radius: 4px; font-size: 0.85rem; display: inline-block;">
                                        0 registered
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <div class="actions" style="justify-content: center;">
                                    <!-- View button: opens a new tab with the title of the event name and displays attendee details -->
                                    <a href="admin_event_attendees.php?id=<?php echo $row['id']; ?>" 
                                       target="_blank" 
                                       class="btn btn-info btn-sm" 
                                       title="Open attendee details in a new tab">
                                        View Attendees
                                    </a>

                                    <!-- Delete Event button -->
                                    <a href="admin_delete_event.php?id=<?php echo $row['id']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirmDelete('event and all its registrations');"
                                       title="Delete this event">
                                        Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 35px 20px; color: #888;">
                            No events found. Click "+ Add New Event" above to create one.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<footer>
    &copy; <?php echo date('Y'); ?> College Event Registration Platform - Admin Panel
</footer>

<script src="script.js"></script>
</body>
</html>
