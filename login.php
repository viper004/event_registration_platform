<?php
session_start();
require_once 'db.php';

$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : (isset($_POST['redirect']) ? $_POST['redirect'] : 'my_reservations.php');
// Sanitize redirect
if (strpos($redirect, 'http') === 0 || strpos($redirect, '//') === 0) {
    $redirect = 'my_reservations.php';
}

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header("Location: " . $redirect);
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            $user = $res->fetch_assoc();
            // Verify password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                // Associate any registrations done under this email to this user_id
                $upd_stmt = $conn->prepare("UPDATE registrations SET user_id = ? WHERE email = ? AND user_id IS NULL");
                $upd_stmt->bind_param("is", $user['id'], $user['email']);
                $upd_stmt->execute();
                $upd_stmt->close();

                header("Location: " . $redirect);
                exit();
            } else {
                $error = "Invalid password. Please try again.";
            }
        } else {
            $error = "No account found with this email address.";
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
    <title>User Login - CampusEvents</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <a href="index.php" class="logo">🎓 CampusEvents</a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="login.php" style="color: #93c5fd; font-weight: bold;">Login</a>
        <a href="signup.php">Create Account</a>
        <a href="admin_login.php" class="btn-admin">Admin Login</a>
    </div>
</nav>

<div class="container">
    <div class="form-card" style="max-width: 440px;">
        <h2 style="text-align: center; color: #1e3a8a; margin-bottom: 6px;">Student / User Login</h2>
        <p style="text-align: center; color: #64748b; font-size: 0.9rem; margin-bottom: 22px;">
            Log in to manage and track your event reservations.
        </p>

        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['registered'])): ?>
            <div class="success-msg">Registration completed! Please log in with your email and password.</div>
        <?php endif; ?>

        <?php if (!empty($_GET['redirect']) && strpos($_GET['redirect'], 'register.php') !== false): ?>
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 10px 14px; margin-bottom: 18px; font-size: 0.88rem; color: #1e40af;">
                ℹ️ Please log in to complete your event registration and track it on your account.
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">

            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" placeholder="e.g. rahul@gmail.com" 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn" style="width: 100%; margin-top: 10px;">Login &rarr;</button>

            <div style="text-align: center; margin-top: 20px; font-size: 0.92rem; color: #475569;">
                Don't have an account yet? <a href="signup.php<?php echo !empty($redirect) ? '?redirect=' . urlencode($redirect) : ''; ?>" style="color: #2563eb; font-weight: 600;">Create an account</a>
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
