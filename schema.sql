CREATE DATABASE IF NOT EXISTS luminalib_db;
USE luminalib_db;

-- Roles table
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
);

-- Users table
CREATE TABLE users (
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
);

-- Authors table
CREATE TABLE authors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    author_name VARCHAR(100) NOT NULL,
    biography TEXT
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL UNIQUE
);

-- Books table
CREATE TABLE books (
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
);

-- Bookings table
CREATE TABLE bookings (
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
);

-- Logs table
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255),
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Sample Data
INSERT INTO roles (role_name) VALUES ('Admin'), ('Patron');

-- Default Admin: admin / password123 (hashed: $2y$10$4bt11v06OHvwYDm4pNlfau8aE5KNDVRehYWHFWMzl2m66LFmfOrHK)
-- Note: In a real app, we'd hash this. For the mockup, I'll use a known hash.
INSERT INTO users (username, password_hash, role_id) VALUES 
('admin', '$2y$10$4bt11v06OHvwYDm4pNlfau8aE5KNDVRehYWHFWMzl2m66LFmfOrHK', 1),
('patron', '$2y$10$4bt11v06OHvwYDm4pNlfau8aE5KNDVRehYWHFWMzl2m66LFmfOrHK', 2);

INSERT INTO categories (category_name) VALUES ('Science Fiction'), ('Technology'), ('History'), ('Philosophy');
INSERT INTO authors (author_name, biography) VALUES ('Isaac Asimov', 'Author of Foundation series'), ('Walter Isaacson', 'Biographer of many greats');
INSERT INTO books (isbn, title, author_id, category_id, publish_year, status) VALUES 
('978-0553293357', 'Foundation', 1, 1, 1951, 'Available'),
('978-1501163401', 'Leonardo da Vinci', 2, 3, 2017, 'Available');
