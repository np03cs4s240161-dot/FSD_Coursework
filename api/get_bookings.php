<?php
require_once '../includes/functions.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$query = "SELECT b.*, u.username, bk.title 
          FROM bookings b 
          JOIN users u ON b.user_id = u.id 
          JOIN books bk ON b.book_id = bk.id 
          ORDER BY b.booking_date DESC";
$stmt = $pdo->query($query);
$bookings = $stmt->fetchAll();

echo json_encode(['success' => true, 'bookings' => $bookings]);
