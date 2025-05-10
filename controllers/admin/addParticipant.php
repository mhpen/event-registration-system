<?php
session_start();
require_once '../../config/dbconn.php';
require_once 'getDefaultPassword.php';

if (!isset($_SESSION['admin'])) {
    header('Location: ../../views/admin/adminLogin.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if email already exists
        $checkEmail = $conn->prepare("SELECT COUNT(*) FROM participants WHERE email = :email");
        $checkEmail->execute([':email' => $_POST['email']]);
        if ($checkEmail->fetchColumn() > 0) {
            header('Location: ../../views/admin/participants.php?error=2'); // Email already exists
            exit();
        }

        $defaultPassword = generateDefaultPassword();
        $hashedPassword = hashPassword($defaultPassword);

        $stmt = $conn->prepare("
            INSERT INTO participants (first_name, last_name, email, phone, status, password)
            VALUES (:firstName, :lastName, :email, :phone, 'active', :password)
        ");
        
        $stmt->execute([
            ':firstName' => $_POST['firstName'],
            ':lastName' => $_POST['lastName'],
            ':email' => $_POST['email'],
            ':phone' => $_POST['phone'],
            ':password' => $hashedPassword
        ]);
        
        // You might want to send an email to the participant with their default password
        // sendWelcomeEmail($_POST['email'], $defaultPassword);
        
        header('Location: ../../views/admin/participants.php?success=1');
    } catch(PDOException $e) {
        header('Location: ../../views/admin/participants.php?error=1');
    }
} else {
    header('Location: ../../views/admin/participants.php');
}
?> 