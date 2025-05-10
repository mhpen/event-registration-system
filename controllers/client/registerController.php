<?php
session_start();
require_once '../../config/dbconn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Validate inputs
    if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($password)) {
        header('Location: ../../views/client/register.php?error=3'); // Empty fields
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ../../views/client/register.php?error=4'); // Invalid email
        exit();
    }

    // Check if passwords match
    if ($password !== $confirmPassword) {
        header('Location: ../../views/client/register.php?error=5'); // Passwords don't match
        exit();
    }

    // Validate phone number (must be numeric and at least 10 digits)
    if (!preg_match('/^[0-9]{10,}$/', $phone)) {
        header('Location: ../../views/client/register.php?error=6'); // Invalid phone
        exit();
    }

    try {
        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT participant_id FROM participants WHERE email = ?");
        $checkStmt->execute([$email]);
        if ($checkStmt->fetch()) {
            header('Location: ../../views/client/register.php?error=1'); // Email exists
            exit();
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new participant
        $stmt = $conn->prepare("
            INSERT INTO participants 
                (first_name, last_name, email, phone, password, registered_at) 
            VALUES 
                (?, ?, ?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute([
            $firstName,
            $lastName,
            $email,
            $phone,
            $hashedPassword
        ]);
        
        if ($result) {
            $_SESSION['participant'] = true;
            $_SESSION['participant_id'] = $conn->lastInsertId();
            $_SESSION['participant_name'] = $firstName . ' ' . $lastName;
            
            // Log the registration
            error_log("New participant registered: {$email}");
            
            header('Location: ../../views/client/dashboard.php?success=1');
            exit();
        } else {
            error_log("Failed to insert new participant: " . print_r($stmt->errorInfo(), true));
            header('Location: ../../views/client/register.php?error=2'); // Database error
            exit();
        }
    } catch(PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        header('Location: ../../views/client/register.php?error=2');
        exit();
    }
}
?> 