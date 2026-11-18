<?php
session_start();
require_once 'db.php';

$registration_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($registration_id <= 0) {
    header("Location: index.php");
    exit();
}

// Fetch registration details
$stmt = $conn->prepare("SELECT * FROM registrations WHERE id = ?");
$stmt->bind_param("i", $registration_id);
$stmt->execute();
$result = $stmt->get_result();
$registration = $result->fetch_assoc();
$stmt->close();

if (!$registration) {
    die("Registration record not found.");
}

// Fetch event details (date & venue) if matching event exists
$event_details = null;
$ev_stmt = $conn->prepare("SELECT * FROM events WHERE name = ? LIMIT 1");
$ev_stmt->bind_param("s", $registration['event_name']);
$ev_stmt->execute();
$ev_res = $ev_stmt->get_result();
if ($ev_res && $ev_res->num_rows > 0) {
    $event_details = $ev_res->fetch_assoc();
}
$ev_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Confirmed - CampusEvents</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <a href="index.php" class="logo">🎓 CampusEvents</a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="my_reservations.php" style="color: #93c5fd; font-weight: bold;">My Reservations</a>
            <span style="color: #cbd5e1; font-size: 0.9rem;">👤 <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <a href="logout.php" style="color: #fca5a5;">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="signup.php">Create Account</a>
        <?php endif; ?>
        <a href="admin_login.php" class="btn-admin">Admin Login</a>
    </div>
</nav>

<div class="container">
    <div class="success-card">
        <div class="success-icon">✓</div>
        <h2 style="color: #16a34a; margin-bottom: 8px;">Registration Confirmed!</h2>
        <p style="color: #666;">Thank you, <strong><?php echo htmlspecialchars($registration['name']); ?></strong>! Your event reservation has been saved successfully.</p>

        <table class="summary-table">
            <tr>
                <td class="label">Reservation Ticket #:</td>
                <td><strong>#<?php echo htmlspecialchars($registration['id']); ?></strong></td>
            </tr>
            <tr>
                <td class="label">Attendee Name:</td>
                <td><?php echo htmlspecialchars($registration['name']); ?></td>
            </tr>
            <tr>
                <td class="label">Email Address:</td>
                <td><?php echo htmlspecialchars($registration['email']); ?></td>
            </tr>
            <tr>
                <td class="label">Event Name:</td>
                <td><strong><?php echo htmlspecialchars($registration['event_name']); ?></strong></td>
            </tr>
            <?php if ($event_details): ?>
                <tr>
                    <td class="label">Event Date:</td>
                    <td>📅 <?php echo htmlspecialchars($event_details['event_date']); ?></td>
                </tr>
                <tr>
                    <td class="label">Event Venue:</td>
                    <td>📍 <?php echo htmlspecialchars($event_details['venue']); ?></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td class="label">Date Registered:</td>
                <td><?php echo htmlspecialchars($registration['date_registered']); ?></td>
            </tr>
            <tr>
                <td class="label">Reservation Status:</td>
                <td><span style="background: #dcfce7; color: #166534; padding: 3px 8px; border-radius: 4px; font-weight: bold; font-size: 0.85rem;">✓ Confirmed</span></td>
            </tr>
        </table>

        <div style="margin-top: 25px; display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
            <a href="my_reservations.php" class="btn" style="background-color: #0369a1;">🎟️ Track My Reservations</a>
            <a href="index.php" class="btn btn-secondary">Browse More Events</a>
            <button onclick="window.print()" class="btn btn-outline">Print Ticket</button>
        </div>
    </div>
</div>

<footer>
    &copy; <?php echo date('Y'); ?> College Event Registration Platform
</footer>

</body>
</html>
