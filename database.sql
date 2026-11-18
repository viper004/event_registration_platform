-- Database: event_db
CREATE DATABASE IF NOT EXISTS event_db;
USE event_db;

-- Table: events
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    event_date DATE NOT NULL,
    description TEXT NOT NULL,
    venue VARCHAR(255) NOT NULL
);

-- Table: registrations
CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    event_name VARCHAR(255) NOT NULL,
    date_registered TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: admin
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Insert sample events
INSERT INTO events (id, name, event_date, description, venue) VALUES
(1, 'Web Development Workshop', '2026-11-25', 'Learn hands-on HTML, CSS, JavaScript, and PHP to build real-world web applications.', 'Lab 3, CS Block'),
(2, 'College Hackathon 2026', '2026-12-02', 'A 24-hour coding challenge to build innovative software solutions. Prizes and certificates for winners.', 'Main Auditorium'),
(3, 'AI & Machine Learning Seminar', '2026-12-10', 'Introduction to Machine Learning, neural networks, and applications in industry by expert guest speakers.', 'Seminar Hall 1')
ON DUPLICATE KEY UPDATE name=name;

-- Insert default admin: username = admin, password = admin123
INSERT INTO admin (id, username, password) VALUES
(1, 'admin', 'admin123')
ON DUPLICATE KEY UPDATE username=username;

-- Insert sample users (password: user123)
INSERT INTO users (id, name, email, password) VALUES
(1, 'Rahul Sharma', 'rahul@gmail.com', '$2y$10$Lu6Vmkur2XVfa2Re8wgSkOzdoHUJEhUvyR017qC1ru1zMNH1aWe2W'),
(2, 'Sneha Patel', 'sneha@gmail.com', '$2y$10$Lu6Vmkur2XVfa2Re8wgSkOzdoHUJEhUvyR017qC1ru1zMNH1aWe2W')
ON DUPLICATE KEY UPDATE name=name;

-- Insert sample registrations
INSERT INTO registrations (id, user_id, name, email, event_name, date_registered) VALUES
(1, 1, 'Rahul Sharma', 'rahul@gmail.com', 'Web Development Workshop', '2026-11-15 10:30:00'),
(2, 2, 'Sneha Patel', 'sneha@gmail.com', 'College Hackathon 2026', '2026-11-16 14:15:00')
ON DUPLICATE KEY UPDATE name=name;
