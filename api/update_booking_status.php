<?php
require_once '../includes/functions.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = $_POST['id'] ?? null;
$status = $_POST['status'] ?? null;

if (!$id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Update booking status
    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    // If approved, update book status to Issued
    if ($status === 'Approved') {
        $stmt = $pdo->prepare("SELECT book_id FROM bookings WHERE id = ?");
        $stmt->execute([$id]);
        $book_id = $stmt->fetchColumn();

        $stmt = $pdo->prepare("UPDATE books SET status = 'Issued' WHERE id = ?");
        $stmt->execute([$book_id]);
        
        logActivity($pdo, $_SESSION['user_id'], "Approved booking ID: $id");
    } elseif ($status === 'Returned') {
        $stmt = $pdo->prepare("SELECT book_id FROM bookings WHERE id = ?");
        $stmt->execute([$id]);
        $book_id = $stmt->fetchColumn();

        $stmt = $pdo->prepare("UPDATE books SET status = 'Available' WHERE id = ?");
        $stmt->execute([$book_id]);

        logActivity($pdo, $_SESSION['user_id'], "Confirmed putback for booking ID: $id");
    } else {
        logActivity($pdo, $_SESSION['user_id'], "Updated booking ID: $id to $status");
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => "Booking $status successfully"]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
