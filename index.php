<?php
session_start();
require_once 'db.php';

// Fetch all events from database
$query = "SELECT * FROM events ORDER BY event_date ASC";
$result = $conn->query($query);

// If user is logged in, fetch all events they have already registered for
$user_registered_events = [];
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $uemail = $_SESSION['user_email'];
    $stmt = $conn->prepare("SELECT id, event_name FROM registrations WHERE user_id = ? OR email = ?");
    $stmt->bind_param("is", $uid, $uemail);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $user_registered_events[$row['event_name']] = $row['id'];
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College Events - Home</title>
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

<!-- Main Container -->
<div class="container">
    <div class="page-header">
        <h1>Upcoming College Events</h1>
        <p>Explore upcoming workshops, hackathons, and seminars. Reserve your spot and track your reservations.</p>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <div style="margin-top: 15px;">
                <a href="my_reservations.php" class="btn btn-secondary btn-sm" style="background-color: #0369a1;">
                    🎟️ View &amp; Track My Reservations &rarr;
                </a>
            </div>
        <?php else: ?>
            <div style="margin-top: 15px; font-size: 0.92rem; color: #475569;">
                Have an account? <a href="login.php" style="color: #2563eb; font-weight: 600;">Log in</a> or 
                <a href="signup.php" style="color: #2563eb; font-weight: 600;">Create an account</a> to easily track your bookings!
            </div>
        <?php endif; ?>
    </div>

    <!-- Events Grid -->
    <div class="events-grid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($event = $result->fetch_assoc()): 
                $is_registered = isset($user_registered_events[$event['name']]);
                $reg_ticket_id = $is_registered ? $user_registered_events[$event['name']] : 0;
            ?>
                <div class="event-card" style="<?php echo $is_registered ? 'border-top: 4px solid #16a34a;' : ''; ?>">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 8px;">
                            <h3 style="margin-bottom: 0;"><?php echo htmlspecialchars($event['name']); ?></h3>
                            <?php if ($is_registered): ?>
                                <span style="background: #dcfce7; color: #166534; font-size: 0.75rem; padding: 3px 8px; border-radius: 4px; font-weight: 700; text-transform: uppercase; white-space: nowrap;">
                                    ✓ Registered
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="event-info">
                            <span>📅 <strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></span>
                            <span>📍 <strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?></span>
                        </div>
                        <p class="event-desc"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                    </div>

                    <div style="margin-top: 15px;">
                        <?php if ($is_registered): ?>
                            <!-- User is already registered for this event -->
                            <div style="display: flex; gap: 8px;">
                                <span class="btn" style="flex: 1; background-color: #16a34a; color: white; cursor: default; text-align: center; border: none; padding: 10px 12px; font-weight: 600; border-radius: 5px;">
                                    ✓ Registered
                                </span>
                                <a href="success.php?id=<?php echo $reg_ticket_id; ?>" class="btn btn-secondary" style="padding: 10px 14px;" title="View your ticket">
                                    Ticket ↗
                                </a>
                            </div>
                        <?php else: ?>
                            <!-- User is not registered yet -->
                            <a href="register.php?event=<?php echo urlencode($event['name']); ?>" class="btn" style="width: 100%;">
                                Register &rarr;
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center; grid-column: 1 / -1; color: #64748b;">No upcoming events found.</p>
        <?php endif; ?>
    </div>
</div>

<footer>
    &copy; <?php echo date('Y'); ?> College Event Registration Platform
</footer>

<script src="script.js"></script>
</body>
</html>
