<?php
require_once '../includes/functions.php';
require_once '../config/db.php';

// Must be after functions.php
ini_set('display_errors', 0);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = $_POST['book_id'] ?? null;
    
    if (!isAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Admin privileges required']);
        exit();
    }

    if (!$book_id) {
        echo json_encode(['success' => false, 'message' => 'Book ID required']);
        exit();
    }

    try {
        // First delete any bookings associated with this book to avoid Foreign Key constraint violations
        $stmtBookings = $pdo->prepare("DELETE FROM bookings WHERE book_id = ?");
        $stmtBookings->execute([$book_id]);

        // Now delete the book
        $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
        $stmt->execute([$book_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Book and associated records deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Book not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
    }
}
?>
