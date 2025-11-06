<?php
if (session_status() == PHP_SESSION_NONE) {
    date_default_timezone_set('Europe/Istanbul');
    session_start();
    
}

// Error reporting for development - should be disabled in production
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
$host = "localhost";
$user = "root"; // Default XAMPP username
$pass = ""; // Default empty password
$dbname = "okul1";

// Create database connection using PDO for better security
try {
    // Create PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    
    // Set error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // For backward compatibility with existing code
    $conn = $pdo;
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Security functions
function secure_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Generate random salt
function generate_salt($length = 16) {
    return bin2hex(random_bytes($length));
}

// Hash password with salt
function hash_password($password, $salt) {
    return hash('sha512', $password . $salt);
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION["user_id"]);
}

// Check if user has admin role
function is_admin() {
    return isset($_SESSION["user_role"]) && $_SESSION["user_role"] == "admin";
}

// Check if user has teacher role
function is_teacher() {
    return isset($_SESSION["user_role"]) && $_SESSION["user_role"] == "teacher";
}

// Check if user has student role
function is_student() {
    return isset($_SESSION["user_role"]) && $_SESSION["user_role"] == "student";
}

// Redirect to login if not logged in
function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit;
    }
}

// Redirect to login if not admin
function require_admin() {
    require_login();
    if (!is_admin()) {
        header("Location: login.php");
        exit;
    }
}

// Redirect to login if not teacher
function require_teacher() {
    require_login();
    if (!is_teacher()) {
        header("Location: login.php");
        exit;
    }
}

// Redirect to login if not student
function require_student() {
    require_login();
    if (!is_student()) {
        header("Location: login.php");
        exit;
    }
}

// Escape function for safe output
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
