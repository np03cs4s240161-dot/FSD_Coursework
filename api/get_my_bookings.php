<?php
require_once '../includes/functions.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $query = "SELECT b.*, bk.title, bk.isbn, a.author_name, a.biography, c.category_name
              FROM bookings b 
              JOIN books bk ON b.book_id = bk.id 
              JOIN authors a ON bk.author_id = a.id
              JOIN categories c ON bk.category_id = c.id
              WHERE b.user_id = ?
              ORDER BY b.booking_date DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll();

    echo json_encode(['success' => true, 'bookings' => $bookings]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
