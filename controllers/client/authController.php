<?php
session_start();
require_once '../../config/dbconn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT * FROM participants WHERE email = ?");
        $stmt->execute([$email]);
        $participant = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($participant && password_verify($password, $participant['password'])) {
            $_SESSION['participant'] = true;
            $_SESSION['participant_id'] = $participant['participant_id'];
            $_SESSION['participant_name'] = $participant['first_name'] . ' ' . $participant['last_name'];
            
            header('Location: ../../views/client/dashboard.php');
            exit();
        } else {
            header('Location: ../../views/client/login.php?error=1');
            exit();
        }
    } catch(PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        header('Location: ../../views/client/login.php?error=2');
        exit();
    }
}
?> 