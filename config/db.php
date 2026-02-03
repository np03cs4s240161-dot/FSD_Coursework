<?php
$db_host = 'localhost';
$db_name = 'np03cs4s240161';
$db_user = 'np03cs4s240161';
$db_pass = 'YXTRdLGSsM';
// $db_name = 'luminalib_db';
// $db_user = 'root';
// $db_pass = '';

try {
    // 1. Initial connection to MySQL (without selecting a DB)
    $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // 2. Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // 3. Select the database
    $pdo->exec("USE `$db_name`");

    // 4. Create Tables if they don't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        role_name VARCHAR(50) NOT NULL UNIQUE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50),
        last_name VARCHAR(50),
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100),
        phone VARCHAR(20),
        password_hash VARCHAR(255) NOT NULL,
        role_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (role_id) REFERENCES roles(id)
    )");

    // Migration: Ensure all columns exist (if table already existed without them)
    $stmt = $pdo->query("DESCRIBE users");
    $existing_cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('email', $existing_cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN email VARCHAR(100) AFTER username");
    }
    if (!in_array('first_name', $existing_cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN first_name VARCHAR(50) AFTER id");
    }
    if (!in_array('last_name', $existing_cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN last_name VARCHAR(50) AFTER first_name");
    }
    if (!in_array('phone', $existing_cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) AFTER email");
    }
    if (!in_array('created_at', $existing_cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS authors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        author_name VARCHAR(100) NOT NULL,
        biography TEXT
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_name VARCHAR(50) NOT NULL UNIQUE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS books (
        id INT AUTO_INCREMENT PRIMARY KEY,
        isbn VARCHAR(20) NOT NULL UNIQUE,
        title VARCHAR(255) NOT NULL,
        author_id INT,
        category_id INT,
        publish_year INT,
        image_path VARCHAR(255),
        status ENUM('Available', 'Pending', 'Issued') DEFAULT 'Available',
        FOREIGN KEY (author_id) REFERENCES authors(id),
        FOREIGN KEY (category_id) REFERENCES categories(id)
    )");

    $stmt = $pdo->query("DESCRIBE books");
    $book_cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('image_path', $book_cols)) {
        $pdo->exec("ALTER TABLE books ADD COLUMN image_path VARCHAR(255) AFTER publish_year");
    }
    // Update status column to include Pending if it was old style
    $pdo->exec("ALTER TABLE books MODIFY COLUMN status ENUM('Available', 'Pending', 'Issued') DEFAULT 'Available'");

    $pdo->exec("CREATE TABLE IF NOT EXISTS bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        book_id INT,
        booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('Pending', 'Approved', 'Rejected', 'Returned') DEFAULT 'Pending',
        purpose VARCHAR(100),
        duration VARCHAR(50),
        message TEXT,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (book_id) REFERENCES books(id)
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        action VARCHAR(255),
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    // 5. Seed initial data (Check if roles exist first)
    $stmt = $pdo->query("SELECT COUNT(*) FROM roles");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO roles (role_name) VALUES ('Admin'), ('Patron')");
        
        // Default Admin: admin / password123
        $adminHash = '$2y$10$b047vYt29he4TsJALaaS/OtwEw1EEEhfqeeIkqvnZI8E0u/41iJ9i';
        $pdo->exec("INSERT INTO users (username, password_hash, role_id) VALUES 
                   ('admin', '$adminHash', 1),
                   ('patron', '$adminHash', 2)");

        $pdo->exec("INSERT INTO categories (category_name) VALUES ('Science Fiction'), ('Technology'), ('History'), ('Philosophy')");
        $pdo->exec("INSERT INTO authors (author_name, biography) VALUES ('Isaac Asimov', 'Author of Foundation series'), ('Walter Isaacson', 'Biographer of many greats')");
        $pdo->exec("INSERT INTO books (isbn, title, author_id, category_id, publish_year, status) VALUES 
                   ('978-0553293357', 'Foundation', 1, 1, 1951, 'Available'),
                   ('978-1501163401', 'Leonardo da Vinci', 2, 3, 2017, 'Available')");
    }

    // Set final PDO attributes for the rest of the app
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch (PDOException $e) {
    die("Database Initialization Failed: " . $e->getMessage());
}
