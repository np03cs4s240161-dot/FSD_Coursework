<?php
require_once '../includes/functions.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$book_id = $_POST['book_id'] ?? null;

if (!$book_id) {
    echo json_encode(['success' => false, 'message' => 'Missing book ID']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Check if book is still available
    $stmt = $pdo->prepare("SELECT status FROM books WHERE id = ? FOR UPDATE");
    $stmt->execute([$book_id]);
    $status = $stmt->fetchColumn();

    if ($status !== 'Available') {
        throw new Exception("Book is no longer available");
    }

    // Create booking
    $purpose = sanitize($_POST['purpose'] ?? '');
    $duration = sanitize($_POST['duration'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    $stmt = $pdo->prepare("INSERT INTO bookings (user_id, book_id, status, purpose, duration, message) VALUES (?, ?, 'Pending', ?, ?, ?)");
    $stmt->execute([$user_id, $book_id, $purpose, $duration, $message]);

    // Update book status to Pending
    $stmt = $pdo->prepare("UPDATE books SET status = 'Pending' WHERE id = ?");
    $stmt->execute([$book_id]);

    logActivity($pdo, $user_id, "Requested book ID: $book_id for $purpose");

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Booking request sent successfully']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
