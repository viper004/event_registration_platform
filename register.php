<?php
session_start();
require_once 'db.php';

$prefilled_event = isset($_GET['event']) ? trim($_GET['event']) : (isset($_POST['event']) ? trim($_POST['event']) : '');

// Require user to be logged in to register and track reservations
if (!isset($_SESSION['user_id'])) {
    $redirect_url = 'register.php' . (!empty($prefilled_event) ? '?event=' . urlencode($prefilled_event) : '');
    header("Location: login.php?redirect=" . urlencode($redirect_url));
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

$error = '';
$is_already_registered = false;
$already_registered_id = null;

// Check upfront if user is already registered for this event
if (!empty($prefilled_event)) {
    $check_stmt = $conn->prepare("SELECT id FROM registrations WHERE (user_id = ? OR email = ?) AND event_name = ? LIMIT 1");
    $check_stmt->bind_param("iss", $user_id, $user_email, $prefilled_event);
    $check_stmt->execute();
    $check_res = $check_stmt->get_result();
    if ($check_res && $check_res->num_rows > 0) {
        $existing = $check_res->fetch_assoc();
        $is_already_registered = true;
        $already_registered_id = $existing['id'];
    }
    $check_stmt->close();
}

// Fetch available events for fallback dropdown if no event is prefilled
$events_list = [];
$ev_query = $conn->query("SELECT name FROM events ORDER BY event_date ASC");
if ($ev_query) {
    while ($row = $ev_query->fetch_assoc()) {
        $events_list[] = $row['name'];
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? $user_name);
    $email = trim($_POST['email'] ?? $user_email);
    $event = trim($_POST['event'] ?? '');

    // Validation 1: No empty fields
    if (empty($name) || empty($email) || empty($event)) {
        $error = "All fields are required. Please fill in your name, email, and event.";
    } 
    // Validation 2: Valid email format
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } 
    else {
        // Validation 3: Check for duplicate registration for the same event
        $check_stmt = $conn->prepare("SELECT id FROM registrations WHERE (user_id = ? OR email = ?) AND event_name = ? LIMIT 1");
        $check_stmt->bind_param("iss", $user_id, $email, $event);
        $check_stmt->execute();
        $check_res = $check_stmt->get_result();

        if ($check_res && $check_res->num_rows > 0) {
            $existing = $check_res->fetch_assoc();
            $is_already_registered = true;
            $already_registered_id = $existing['id'];
            $error = "You are already registered for this event! You cannot register more than once.";
        } else {
            // Save the registration linked to the logged-in user
            $stmt = $conn->prepare("INSERT INTO registrations (user_id, name, email, event_name) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $user_id, $name, $email, $event);

            if ($stmt->execute()) {
                $registration_id = $stmt->insert_id;
                header("Location: success.php?id=" . $registration_id);
                exit();
            } else {
                $error = "Error saving registration: " . $conn->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration - CampusEvents</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <a href="index.php" class="logo">🎓 CampusEvents</a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="my_reservations.php">My Reservations</a>
        <span style="color: #cbd5e1; font-size: 0.9rem;">👤 <?php echo htmlspecialchars($user_name); ?></span>
        <a href="logout.php" style="color: #fca5a5;">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="form-card">
        <h2 style="text-align: center; color: #1e3a8a; margin-bottom: 6px;">Event Registration</h2>
        <p style="text-align: center; color: #64748b; font-size: 0.9rem; margin-bottom: 20px;">
            Reserve your seat. Your ticket will be saved to your account.
        </p>

        <?php if ($is_already_registered): ?>
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 16px; margin-bottom: 20px; text-align: center;">
                <div style="font-size: 1.5rem; margin-bottom: 5px;">✓</div>
                <h3 style="color: #166534; font-size: 1.1rem; margin-bottom: 6px;">You are already registered!</h3>
                <p style="color: #475569; font-size: 0.9rem; margin-bottom: 15px;">
                    You have already booked a reservation for <strong><?php echo htmlspecialchars($prefilled_event); ?></strong>.
                </p>
                <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                    <a href="success.php?id=<?php echo $already_registered_id; ?>" class="btn" style="background-color: #16a34a; font-size: 0.9rem;">
                        View Your Ticket #<?php echo $already_registered_id; ?> &rarr;
                    </a>
                    <a href="my_reservations.php" class="btn btn-secondary" style="font-size: 0.9rem;">
                        My Reservations
                    </a>
                </div>
            </div>
        <?php elseif (!empty($error)): ?>
            <div class="error-msg">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!$is_already_registered): ?>
            <form action="register.php" method="POST" id="registrationForm">
                <!-- Name Field -->
                <div class="form-group">
                    <label for="name">Your Full Name:</label>
                    <input type="text" id="name" name="name" 
                           value="<?php echo htmlspecialchars($user_name); ?>" required>
                </div>

                <!-- Email Field -->
                <div class="form-group">
                    <label for="email">Email Address (linked to your account):</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($user_email); ?>" required readonly style="background-color: #f8fafc; color: #475569;">
                </div>

                <!-- Event Field (Auto-filled) -->
                <div class="form-group">
                    <label for="event">Event Name:</label>
                    <?php if (!empty($prefilled_event)): ?>
                        <input type="text" id="event" name="event" value="<?php echo htmlspecialchars($prefilled_event); ?>" readonly required>
                    <?php else: ?>
                        <select id="event" name="event" required>
                            <option value="">-- Select an Event --</option>
                            <?php foreach ($events_list as $ev_name): ?>
                                <option value="<?php echo htmlspecialchars($ev_name); ?>">
                                    <?php echo htmlspecialchars($ev_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn" style="width: 100%; margin-top: 10px;">Confirm & Submit Reservation</button>
                <div style="text-align: center; margin-top: 15px;">
                    <a href="index.php" style="color: #64748b; font-size: 0.9rem;">&larr; Back to Events List</a>
                </div>
            </form>
        <?php else: ?>
            <div style="text-align: center; margin-top: 15px;">
                <a href="index.php" style="color: #64748b; font-size: 0.9rem;">&larr; Back to Events List</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer>
    &copy; <?php echo date('Y'); ?> College Event Registration Platform
</footer>

<script src="script.js"></script>
</body>
</html>
