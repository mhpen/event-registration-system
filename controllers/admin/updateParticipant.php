<?php
session_start();
require_once '../../config/dbconn.php';

if (!isset($_SESSION['admin'])) {
    header('Location: ../../views/admin/adminLogin.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $conn->prepare("
            UPDATE participants 
            SET first_name = :firstName,
                last_name = :lastName,
                email = :email,
                phone = :phone
            WHERE participant_id = :id
        ");
        
        $stmt->execute([
            ':firstName' => $_POST['firstName'],
            ':lastName' => $_POST['lastName'],
            ':email' => $_POST['email'],
            ':phone' => $_POST['phone'],
            ':id' => $_POST['participant_id']
        ]);
        
        header('Location: ../../views/admin/participants.php?success=1');
    } catch(PDOException $e) {
        header('Location: ../../views/admin/participants.php?error=1');
    }
} else {
    header('Location: ../../views/admin/participants.php');
}
?> 