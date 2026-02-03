<?php
require_once '../includes/functions.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$query = "SELECT l.*, u.username FROM logs l JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC";
$stmt = $pdo->query($query);
$logs = $stmt->fetchAll();

echo json_encode(['success' => true, 'logs' => $logs]);
