<?php
require_once '../includes/functions.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$keyword = $_GET['keyword'] ?? '';
$category_id = $_GET['category'] ?? '';
$year = $_GET['year'] ?? '';

$sql = "SELECT b.*, a.author_name, a.biography, c.category_name 
        FROM books b 
        LEFT JOIN authors a ON b.author_id = a.id 
        LEFT JOIN categories c ON b.category_id = c.id 
        WHERE 1=1";

$params = [];

if (!empty($keyword)) {
    $sql .= " AND (b.title LIKE ? OR b.isbn LIKE ?)";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
}

if (!empty($category_id)) {
    $sql .= " AND b.category_id = ?";
    $params[] = $category_id;
}

if (!empty($year)) {
    $sql .= " AND b.publish_year <= ?";
    $params[] = $year;
}

$sql .= " ORDER BY b.id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $books = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'books' => $books
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Search failed: ' . $e->getMessage()
    ]);
}
?>
