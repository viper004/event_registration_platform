<?php
session_start();
require_once 'db.php';

// If already logged in as a user, redirect
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : (isset($_POST['redirect']) ? $_POST['redirect'] : 'my_reservations.php');
// Sanitize redirect: only allow relative URLs to prevent open redirects
if (strpos($redirect, 'http') === 0 || strpos($redirect, '//') === 0) {
    $redirect = 'my_reservations.php';
}

if (isset($_SESSION['user_id'])) {
    header("Location: " . $redirect);
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email is already registered
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "An account with this email address already exists. Please log in.";
        } else {
            // Hash password and insert user
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $ins_stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $ins_stmt->bind_param("sss", $name, $email, $hashed);

            if ($ins_stmt->execute()) {
                $user_id = $ins_stmt->insert_id;
                // Auto-login newly created user
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;

                // Also associate any pre-existing registrations with this email to this new user_id
                $upd_stmt = $conn->prepare("UPDATE registrations SET user_id = ? WHERE email = ? AND user_id IS NULL");
                $upd_stmt->bind_param("is", $user_id, $email);
                $upd_stmt->execute();
                $upd_stmt->close();

                header("Location: " . $redirect);
                exit();
            } else {
                $error = "Error creating account: " . $conn->error;
            }
            $ins_stmt->close();
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
    <title>Create Account - CampusEvents</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <a href="index.php" class="logo">🎓 CampusEvents</a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="login.php">Login</a>
        <a href="signup.php" style="color: #93c5fd; font-weight: bold;">Create Account</a>
        <a href="admin_login.php" class="btn-admin">Admin Login</a>
    </div>
</nav>

<div class="container">
    <div class="form-card" style="max-width: 480px;">
        <h2 style="text-align: center; color: #1e3a8a; margin-bottom: 6px;">Create Your Account</h2>
        <p style="text-align: center; color: #64748b; font-size: 0.9rem; margin-bottom: 22px;">
            Sign up to reserve seats for college events and track your reservations anytime.
        </p>

        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="signup.php" method="POST">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">

            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" placeholder="e.g. Rahul Sharma" 
                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required autofocus>
            </div>

            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" placeholder="e.g. rahul@gmail.com" 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password (min 6 characters):</label>
                <input type="password" id="password" name="password" placeholder="Create a strong password" minlength="6" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-type your password" minlength="6" required>
            </div>

            <button type="submit" class="btn" style="width: 100%; margin-top: 10px;">Create Account &rarr;</button>

            <div style="text-align: center; margin-top: 20px; font-size: 0.92rem; color: #475569;">
                Already have an account? <a href="login.php<?php echo !empty($redirect) ? '?redirect=' . urlencode($redirect) : ''; ?>" style="color: #2563eb; font-weight: 600;">Log in here</a>
            </div>

            <div style="text-align: center; margin-top: 15px;">
                <a href="index.php" style="color: #64748b; font-size: 0.88rem;">&larr; Back to Events</a>
            </div>
        </form>
    </div>
</div>

<footer>
    &copy; <?php echo date('Y'); ?> College Event Registration Platform
</footer>

</body>
</html>
