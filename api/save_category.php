<?php
require_once '../includes/functions.php';
require_once '../config/db.php';
header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$id = $_POST['id'] ?? null;
$name = sanitize($_POST['category_name'] ?? '');

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Name is required']);
    exit();
}

try {
    if ($id) {
        $stmt = $pdo->prepare("UPDATE categories SET category_name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
        logActivity($pdo, $_SESSION['user_id'], "Updated category ID $id to: $name");
        echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
    } else {
        $stmt = $pdo->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->execute([$name]);
        logActivity($pdo, $_SESSION['user_id'], "Added category: $name");
        echo json_encode(['success' => true, 'message' => 'Category added successfully']);
    }
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode(['success' => false, 'message' => 'Category already exists']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}
?>
