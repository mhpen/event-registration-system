<?php
require_once '../config/dbconn.php';

try {
    // Create participants table
    $conn->exec("CREATE TABLE IF NOT EXISTS participants (
        participant_id INT PRIMARY KEY AUTO_INCREMENT,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        status ENUM('active', 'inactive') DEFAULT 'active' NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Create events table
    $conn->exec("CREATE TABLE IF NOT EXISTS events (
        event_id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(100) NOT NULL,
        description TEXT,
        event_date DATETIME NOT NULL,
        location VARCHAR(200),
        status ENUM('active', 'cancelled') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Create registrations table
    $conn->exec("CREATE TABLE IF NOT EXISTS registrations (
        registration_id INT PRIMARY KEY AUTO_INCREMENT,
        participant_id INT,
        event_id INT,
        registration_code VARCHAR(50) UNIQUE,
        status ENUM('registered', 'cancelled') DEFAULT 'registered',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (participant_id) REFERENCES participants(participant_id),
        FOREIGN KEY (event_id) REFERENCES events(event_id)
    )");

    // Create checkins table
    $conn->exec("CREATE TABLE IF NOT EXISTS checkins (
        checkin_id INT PRIMARY KEY AUTO_INCREMENT,
        participant_id INT,
        event_id INT,
        checkin_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('checked_in', 'pending') DEFAULT 'checked_in',
        notes TEXT,
        FOREIGN KEY (participant_id) REFERENCES participants(participant_id),
        FOREIGN KEY (event_id) REFERENCES events(event_id)
    )");

    echo "Database tables created successfully";
} catch(PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}
?> 