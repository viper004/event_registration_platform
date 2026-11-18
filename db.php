<?php
// XAMPP Default Database Configuration
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "event_db";

// Connect to MySQL server
$conn = @new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("<div style='font-family:Arial,sans-serif;padding:20px;color:#721c24;background:#f8d7da;border:1px solid #f5c6cb;border-radius:5px;max-width:600px;margin:30px auto;'>
        <h3 style='margin-top:0;'>MySQL Connection Failed</h3>
        <p>" . $conn->connect_error . "</p>
        <p><strong>Fix:</strong> Open your <strong>XAMPP Control Panel</strong> and make sure <strong>MySQL</strong> is started.</p>
    </div>");
}

// Select database, or auto-create and import schema if not found
if (!$conn->select_db($dbname)) {
    $conn->query("CREATE DATABASE IF NOT EXISTS `$dbname`");
    $conn->select_db($dbname);

    $sqlFile = __DIR__ . '/database.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        $conn->multi_query($sql);
        while ($conn->next_result()) {;} // flush multi queries
    }
}

// Auto-migration: Ensure users table and user_id column exist
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
$col_check = $conn->query("SHOW COLUMNS FROM registrations LIKE 'user_id'");
if ($col_check && $col_check->num_rows == 0) {
    $conn->query("ALTER TABLE registrations ADD COLUMN user_id INT NULL AFTER id");
}
?>
