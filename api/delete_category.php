<?php
require_once '../includes/functions.php';
require_once '../config/db.php';
header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID is required']);
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    logActivity($pdo, $_SESSION['user_id'], "Deleted category ID: $id");
    echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to delete category. It might be linked to books.']);
}
