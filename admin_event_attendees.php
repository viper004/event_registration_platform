<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

require_once 'db.php';

$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($event_id <= 0) {
    header("Location: admin_events.php");
    exit();
}

// Fetch event details
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$res = $stmt->get_result();
$event = $res->fetch_assoc();
$stmt->close();

if (!$event) {
    die("Event not found.");
}

// Fetch all registered people for this event
$stmt = $conn->prepare("SELECT * FROM registrations WHERE event_name = ? ORDER BY date_registered DESC");
$stmt->bind_param("s", $event['name']);
$stmt->execute();
$regs_res = $stmt->get_result();
$registrations = [];
if ($regs_res) {
    while ($row = $regs_res->fetch_assoc()) {
        $registrations[] = $row;
    }
}
$stmt->close();

$reg_count = count($registrations);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Title of the new tab is the Event Name as requested -->
    <title><?php echo htmlspecialchars($event['name']); ?> - Registered Attendees</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navbar -->
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
    <!-- Event Banner Card -->
    <div style="background: white; border-radius: 8px; padding: 25px; margin-bottom: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-top: 4px solid #2563eb;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px;">
            <div>
                <span style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 4px; font-weight: 600; font-size: 0.8rem; text-transform: uppercase;">
                    Event #<?php echo htmlspecialchars($event['id']); ?>
                </span>
                <h1 style="color: #0f172a; font-size: 1.85rem; margin-top: 8px; margin-bottom: 8px;">
                    <?php echo htmlspecialchars($event['name']); ?>
                </h1>
                <div style="display: flex; gap: 20px; flex-wrap: wrap; color: #475569; font-size: 0.95rem; margin-bottom: 12px;">
                    <span>📅 <strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></span>
                    <span>📍 <strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?></span>
                    <span>👥 <strong>Total Registered:</strong> <?php echo $reg_count; ?> participant(s)</span>
                </div>
                <p style="color: #64748b; font-size: 0.92rem; line-height: 1.5; max-width: 800px;">
                    <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                </p>
            </div>
            <div>
                <a href="admin_events.php" class="btn btn-secondary btn-sm">&larr; Back to Events</a>
            </div>
        </div>
    </div>

    <!-- Attendees Table Section -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 15px;">
        <div>
            <h2 style="color: #1e293b; font-size: 1.35rem;">List of Registered People</h2>
            <p style="color: #64748b; font-size: 0.88rem;">All participants registered for this event.</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <?php if ($reg_count > 0): ?>
                <input type="text" 
                       class="attendee-search-input" 
                       placeholder="🔍 Search by name or email..." 
                       onkeyup="filterAttendeeTable(this, 'attendeesTable')">
                <button type="button" class="btn btn-outline btn-sm" onclick="window.print()">
                    🖨️ Print List
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-responsive">
        <table class="admin-table" id="attendeesTable">
            <thead>
                <tr>
                    <th style="width: 70px;">Reg ID</th>
                    <th>Participant Name</th>
                    <th>Email Address</th>
                    <th>Date Registered</th>
                    <th style="text-align: center; width: 140px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($reg_count > 0): ?>
                    <?php foreach ($registrations as $reg): ?>
                        <tr>
                            <td><strong>#<?php echo htmlspecialchars($reg['id']); ?></strong></td>
                            <td>
                                <strong><?php echo htmlspecialchars($reg['name']); ?></strong>
                            </td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($reg['email']); ?>" style="color: #2563eb; text-decoration: none;">
                                    <?php echo htmlspecialchars($reg['email']); ?>
                                </a>
                            </td>
                            <td>📅 <?php echo htmlspecialchars($reg['date_registered']); ?></td>
                            <td style="text-align: center;">
                                <div class="actions" style="justify-content: center;">
                                    <a href="admin_view.php?id=<?php echo $reg['id']; ?>&event_id=<?php echo $event['id']; ?>" class="btn btn-secondary btn-sm" title="View card">
                                        View
                                    </a>
                                    <a href="admin_delete.php?id=<?php echo $reg['id']; ?>&event_id=<?php echo $event['id']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirmDelete('registration for <?php echo htmlspecialchars(addslashes($reg['name'])); ?>');"
                                       title="Delete registration">
                                        Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px 20px; color: #64748b;">
                            ℹ️ No people have registered for <strong><?php echo htmlspecialchars($event['name']); ?></strong> yet.
                            <div style="margin-top: 10px;">
                                <a href="register.php?event=<?php echo urlencode($event['name']); ?>" target="_blank" class="btn btn-sm btn-info">
                                    Open Registration Page &rarr;
                                </a>
                            </div>
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
