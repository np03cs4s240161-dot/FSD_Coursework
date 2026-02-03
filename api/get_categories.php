<?php
require_once '../includes/functions.php';
require_once '../config/db.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT id, category_name FROM categories ORDER BY category_name ASC");
    $categories = $stmt->fetchAll();
    echo json_encode(['success' => true, 'categories' => $categories]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch categories']);
}
?>
