<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host     = 'localhost';
$port     = '3308'; // Yoo WAMP kee 3308 irratti ka'aa jiru 3308 fayyadami
$db       = 'jobportal';
$user     = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function checkRole($required_role) {
    if (!isLoggedIn()) {
        header("Location: ../login.php");
        exit();
    }
    if ($_SESSION['role'] !== $required_role) {
        header("Location: ../index.php");
        exit();
    }
}
?>