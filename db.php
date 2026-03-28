<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'fitness_db');

// Create connection using mysqli
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database Connection Failed: ' . $conn->connect_error]));
}

// Set charset
$conn->set_charset('utf8mb4');

// Create tables if they don't exist
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$sql_fitness = "CREATE TABLE IF NOT EXISTS fitness_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    steps INT DEFAULT 0,
    calories INT DEFAULT 0,
    water FLOAT DEFAULT 0,
    date DATE NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

$sql_goals = "CREATE TABLE IF NOT EXISTS goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    steps_goal INT DEFAULT 10000,
    calories_goal INT DEFAULT 500,
    water_goal FLOAT DEFAULT 2,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

// Execute table creation
$conn->query($sql_users);
$conn->query($sql_fitness);
$conn->query($sql_goals);

// Check login session
function checkLogin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Redirect if already logged in
function redirectIfLogged() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['user_id'])) {
        header('Location: dashboard.php');
        exit;
    }
}
?>
