<?php
require_once '../includes/functions.php';
require_once '../config/db.php';
header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$name = sanitize($_POST['author_name'] ?? '');
$bio = sanitize($_POST['biography'] ?? '');

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Name is required']);
    exit();
}

try {
    $stmt->execute([$name, $bio]);
    logActivity($pdo, $_SESSION['user_id'], "Added author: $name");
    echo json_encode(['success' => true, 'message' => 'Author added successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to save author']);
}
?>
