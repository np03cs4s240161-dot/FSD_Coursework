<?php
require_once '../includes/functions.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = $_POST['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Missing booking ID']);
    exit;
}

try {
    // Check if the booking exists, belongs to the user, and is 'Approved'
    $stmt = $pdo->prepare("SELECT status FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    $booking = $stmt->fetch();

    if (!$booking) {
        echo json_encode(['success' => false, 'message' => 'Booking not found or access denied']);
        exit;
    }

    if ($booking['status'] !== 'Approved') {
        echo json_encode(['success' => false, 'message' => 'Only approved bookings can be put back']);
        exit;
    }

    // Update status to 'Returning'
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'Returning' WHERE id = ?");
    $stmt->execute([$id]);

    logActivity($pdo, $user_id, "Requested putback for booking ID: $id");

    echo json_encode(['success' => true, 'message' => 'Putback request sent successfully. Waiting for admin confirmation.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
