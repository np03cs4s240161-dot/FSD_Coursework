<?php
require_once '../includes/functions.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

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
        $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
        $stmt->execute([$book_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Book deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Book not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete book as it might be linked to other records']);
    }
}
?>
