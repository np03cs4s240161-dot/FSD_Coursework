<?php
require_once '../includes/functions.php';
require_once '../config/db.php';
header('Content-Type: application/json');

$keyword = sanitize($_GET['keyword'] ?? '');
$query = "SELECT * FROM categories";
$params = [];

if (!empty($keyword)) {
    $query .= " WHERE category_name LIKE ?";
    $params[] = "%$keyword%";
}

$query .= " ORDER BY category_name ASC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'categories' => $categories]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch categories: ' . $e->getMessage()]);
}
?>
