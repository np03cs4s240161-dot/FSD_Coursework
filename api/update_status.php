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
    $new_status = $_POST['status'] ?? null;

    if (!$book_id || !$new_status) {
        echo json_encode(['success' => false, 'message' => 'Missing parameters']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT status FROM books WHERE id = ?");
        $stmt->execute([$book_id]);
        $currentStatus = $stmt->fetchColumn();

        if (!$currentStatus) {
            echo json_encode(['success' => false, 'message' => 'Book not found']);
            exit();
        }

        // --- VALIDATION LOGIC ---

        if (isAdmin()) {
            // Admin can do anything (Approve Pending -> Issued, Reject Pending -> Available, Toggle Available/Issued)
            $stmt = $pdo->prepare("UPDATE books SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $book_id]);
            echo json_encode(['success' => true, 'message' => 'Status updated to ' . $new_status]);
        } else {
            // Patrons can ONLY request 'Available' books
            if ($new_status === 'Pending' && $currentStatus === 'Available') {
                $stmt = $pdo->prepare("UPDATE books SET status = 'Pending' WHERE id = ?");
                $stmt->execute([$book_id]);
                echo json_encode(['success' => true, 'message' => 'Booking request sent for approval']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid action or book not available']);
            }
        }

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
