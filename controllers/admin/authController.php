<?php
session_start();
require_once '../../config/dbconn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // Debug log
        error_log("Login attempt for email: " . $email);

        $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ? AND password = ?");
        $stmt->execute([$email, $password]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Debug log
        error_log("Query result: " . print_r($admin, true));
        
        if ($admin && isset($admin['admin_id'])) {
            error_log("Admin logged in successfully with ID: " . $admin['admin_id']);
            
            $_SESSION['admin'] = true;
            $_SESSION['admin_id'] = $admin['admin_id'];
            
            // Debug log
            error_log("Session data: " . print_r($_SESSION, true));
            
            header('Location: ../../views/admin/dashboard.php');
            exit();
        } else {
            error_log("Login failed - Invalid credentials or missing admin_id");
            header('Location: ../../views/admin/adminLogin.php?error=1');
            exit();
        }
    } catch(PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        header('Location: ../../views/admin/adminLogin.php?error=2');
        exit();
    }
}
?>