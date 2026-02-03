<?php
require_once '../includes/functions.php';
require_once '../config/db.php';
header('Content-Type: application/json');

if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID is required']);
    exit;
}

if ($id == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'You cannot delete yourself']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    logActivity($pdo, $_SESSION['user_id'], "Deleted user ID: $id");
    echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
}
