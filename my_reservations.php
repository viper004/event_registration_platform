<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=my_reservations.php");
    exit();
}

require_once 'db.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

// Fetch all reservations for this logged-in user
$stmt = $conn->prepare("SELECT r.*, e.id AS event_id, e.event_date, e.venue, e.description 
                        FROM registrations r 
                        LEFT JOIN events e ON r.event_name = e.name 
                        WHERE r.user_id = ? OR r.email = ?
                        ORDER BY r.date_registered DESC");
$stmt->bind_param("is", $user_id, $user_email);
$stmt->execute();
$res = $stmt->get_result();
$reservations = [];
$today = date('Y-m-d');
$upcoming_count = 0;

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $reservations[] = $row;
        if (!empty($row['event_date']) && $row['event_date'] >= $today) {
            $upcoming_count++;
        }
    }
}
$stmt->close();
$total_count = count($reservations);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Event Reservations - CampusEvents</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <a href="index.php" class="logo">🎓 CampusEvents</a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="my_reservations.php" style="color: #93c5fd; font-weight: bold;">My Reservations</a>
        <span style="color: #cbd5e1; font-size: 0.9rem;">👤 <?php echo htmlspecialchars($user_name); ?></span>
        <a href="logout.php" style="color: #fca5a5;">Logout</a>
    </div>
</nav>

<div class="container" style="max-width: 1050px;">
    <!-- User Welcome / Overview Banner -->
    <div style="background: white; border-radius: 8px; padding: 25px; margin-bottom: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-top: 4px solid #2563eb;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1 style="color: #0f172a; font-size: 1.8rem; margin-bottom: 6px;">
                    My Event Reservations
                </h1>
                <p style="color: #64748b; font-size: 0.92rem;">
                    Welcome back, <strong><?php echo htmlspecialchars($user_name); ?></strong> (<?php echo htmlspecialchars($user_email); ?>). Track your event tickets and schedules here.
                </p>
            </div>
            <div>
                <a href="index.php" class="btn">+ Explore More Events</a>
            </div>
        </div>

        <!-- Quick Summary Metrics -->
        <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px; padding-top: 15px; border-top: 1px solid #f1f5f9;">
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 20px;">
                <span style="display: block; font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 600;">Total Bookings</span>
                <span style="font-size: 1.4rem; font-weight: bold; color: #1e3a8a;"><?php echo $total_count; ?></span>
            </div>
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 12px 20px;">
                <span style="display: block; font-size: 0.8rem; color: #166534; text-transform: uppercase; font-weight: 600;">Upcoming Events</span>
                <span style="font-size: 1.4rem; font-weight: bold; color: #15803d;"><?php echo $upcoming_count; ?></span>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['cancelled'])): ?>
        <div class="success-msg">Your event reservation has been successfully cancelled.</div>
    <?php endif; ?>

    <!-- Search / Filter Bar -->
    <?php if ($total_count > 0): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 15px;">
            <h2 style="color: #1e293b; font-size: 1.25rem;">Reservation History & Tickets</h2>
            <input type="text" 
                   class="attendee-search-input" 
                   placeholder="🔍 Search my reservations..." 
                   onkeyup="filterAttendeeTable(this, 'myReservationsTable')">
        </div>
    <?php endif; ?>

    <!-- Reservations Table -->
    <div class="table-responsive">
        <table class="admin-table" id="myReservationsTable">
            <thead>
                <tr>
                    <th style="width: 70px;">Ticket #</th>
                    <th>Event Details</th>
                    <th>Date & Venue</th>
                    <th>Booked On</th>
                    <th>Status</th>
                    <th style="text-align: center; width: 170px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($total_count > 0): ?>
                    <?php foreach ($reservations as $row): 
                        $is_upcoming = !empty($row['event_date']) && ($row['event_date'] >= $today);
                    ?>
                        <tr>
                            <td>
                                <strong>#<?php echo htmlspecialchars($row['id']); ?></strong>
                            </td>
                            <td>
                                <strong style="color: #1e293b; font-size: 1rem;"><?php echo htmlspecialchars($row['event_name']); ?></strong>
                                <?php if (!empty($row['description'])): ?>
                                    <p style="font-size: 0.82rem; color: #64748b; margin-top: 3px; max-width: 320px;">
                                        <?php echo htmlspecialchars(substr($row['description'], 0, 80)) . '...'; ?>
                                    </p>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div>📅 <strong><?php echo !empty($row['event_date']) ? htmlspecialchars($row['event_date']) : 'TBA'; ?></strong></div>
                                <div style="font-size: 0.85rem; color: #64748b; margin-top: 3px;">
                                    📍 <?php echo !empty($row['venue']) ? htmlspecialchars($row['venue']) : 'College Campus'; ?>
                                </div>
                            </td>
                            <td>
                                <span style="font-size: 0.88rem; color: #475569;">
                                    <?php echo htmlspecialchars($row['date_registered']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($is_upcoming): ?>
                                    <span style="background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 4px; font-weight: 600; font-size: 0.82rem; display: inline-block;">
                                        ✓ Confirmed
                                    </span>
                                <?php else: ?>
                                    <span style="background: #f1f5f9; color: #64748b; padding: 4px 10px; border-radius: 4px; font-size: 0.82rem; display: inline-block;">
                                        Completed
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <div class="actions" style="justify-content: center;">
                                    <a href="success.php?id=<?php echo $row['id']; ?>" class="btn btn-secondary btn-sm" title="View Ticket & Print Summary">
                                        View Ticket
                                    </a>
                                    <?php if ($is_upcoming): ?>
                                        <a href="cancel_reservation.php?id=<?php echo $row['id']; ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Are you sure you want to cancel your reservation for &quot;<?php echo htmlspecialchars(addslashes($row['event_name'])); ?>&quot;?');"
                                           title="Cancel reservation">
                                            Cancel
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 50px 20px;">
                            <div style="font-size: 2.5rem; margin-bottom: 10px;">🎟️</div>
                            <h3 style="color: #334155; margin-bottom: 6px;">No reservations found yet</h3>
                            <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 20px;">
                                You haven't registered for any events yet. Check out our upcoming college events!
                            </p>
                            <a href="index.php" class="btn">Explore Upcoming Events &rarr;</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<footer>
    &copy; <?php echo date('Y'); ?> College Event Registration Platform
</footer>

<script src="script.js"></script>
</body>
</html>
