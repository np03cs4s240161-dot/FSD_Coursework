<?php
require_once '../includes/functions.php';
require_once '../config/db.php';
header('Content-Type: application/json');

try {
    // Books per Category
    $categoryData = $pdo->query("
        SELECT c.category_name, COUNT(b.id) as count 
        FROM categories c 
        LEFT JOIN books b ON c.id = b.category_id 
        GROUP BY c.id
    ")->fetchAll();

    // Availability ratio
    $availability = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM books 
        GROUP BY status
    ")->fetchAll();

    echo json_encode([
        'success' => true,
        'categories' => $categoryData,
        'availability' => $availability
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch stats']);
}
?>
