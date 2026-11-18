<?php
session_start();
require_once 'db.php';

// If already logged in, redirect to manage events
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_events.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            header("Location: admin_events.php");
            exit();
        } else {
            $error = "Invalid admin username or password.";
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
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">🎓 CampusEvents</a>
    <div class="nav-links">
        <a href="index.php">Home</a>
    </div>
</nav>

<div class="container">
    <div class="form-card" style="max-width: 420px;">
        <h2 style="text-align: center; color: #1e3a8a; margin-bottom: 20px;">Admin Login</h2>

        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="admin_login.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" placeholder="e.g. admin" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn" style="width: 100%; margin-top: 10px;">Login</button>
        </form>

        <div style="margin-top: 20px; padding: 10px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 4px; font-size: 0.85rem; text-align: center; color: #64748b;">
            Default credentials: <strong>admin</strong> / <strong>admin123</strong>
        </div>

        <div style="text-align: center; margin-top: 15px;">
            <a href="index.php" style="color: #666; font-size: 0.9rem;">&larr; Back to Website</a>
        </div>
    </div>
</div>

<footer>
    &copy; <?php echo date('Y'); ?> College Event Registration Platform
</footer>

</body>
</html>
