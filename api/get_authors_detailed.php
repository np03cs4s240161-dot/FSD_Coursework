<?php
require_once '../includes/functions.php';
require_once '../config/db.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT * FROM authors ORDER BY author_name ASC");
    $authors = $stmt->fetchAll();
    echo json_encode(['success' => true, 'authors' => $authors]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch authors']);
}
?>
