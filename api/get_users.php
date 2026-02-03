<?php
require_once '../includes/functions.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$keyword = $_GET['keyword'] ?? '';
$query = "SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id";

if ($keyword) {
    $query .= " WHERE u.username LIKE :kw OR u.email LIKE :kw OR u.first_name LIKE :kw OR u.last_name LIKE :kw";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['kw' => "%$keyword%"]);
} else {
    $stmt = $pdo->query($query);
}

$users = $stmt->fetchAll();

echo json_encode(['success' => true, 'users' => $users]);
