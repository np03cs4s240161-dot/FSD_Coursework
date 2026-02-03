<?php
require_once '../includes/functions.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isbn = sanitize($_POST['isbn'] ?? '');
    $title = sanitize($_POST['title'] ?? '');
    $author_name = sanitize($_POST['author_name'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $publish_year = $_POST['publish_year'] ?? null;
    $image_path = null;

    if (!$isbn || !$title || !$author_name || !$category_id) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit();
    }

    // Ensure numeric fields are correctly handled
    $category_id = !empty($category_id) ? (int)$category_id : null;
    $publish_year = !empty($publish_year) ? (int)$publish_year : null;

    // Handle author - check if exists, if not create new
    try {
        $authorStmt = $pdo->prepare("SELECT id FROM authors WHERE author_name = ?");
        $authorStmt->execute([$author_name]);
        $author = $authorStmt->fetch();
        
        if ($author) {
            $author_id = $author['id'];
        } else {
            // Create new author
            $insertAuthor = $pdo->prepare("INSERT INTO authors (author_name) VALUES (?)");
            $insertAuthor->execute([$author_name]);
            $author_id = $pdo->lastInsertId();
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error handling author: ' . $e->getMessage()]);
        exit();
    }

    // Handle image upload
    if (isset($_FILES['book_image'])) {
        if ($_FILES['book_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['book_image'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array(strtolower($ext), $allowed)) {
                $filename = uniqid('book_') . '.' . $ext;
                $destination = '../uploads/' . $filename;
                
                // Check if uploads directory is writable
                if (!is_dir('../uploads')) {
                    mkdir('../uploads', 0777, true);
                }
                
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $image_path = 'uploads/' . $filename;
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file. Check folder permissions.']);
                    exit();
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: ' . implode(', ', $allowed)]);
                exit();
            }
        } elseif ($_FILES['book_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $err = $_FILES['book_image']['error'];
            $msg = 'Upload error code: ' . $err;
            if ($err === UPLOAD_ERR_INI_SIZE) $msg = 'File exceeds upload_max_filesize in php.ini';
            if ($err === UPLOAD_ERR_FORM_SIZE) $msg = 'File exceeds MAX_FILE_SIZE in HTML form';
            if ($err === UPLOAD_ERR_PARTIAL) $msg = 'File was only partially uploaded';
            
            echo json_encode(['success' => false, 'message' => $msg]);
            exit();
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO books (isbn, title, author_id, category_id, publish_year, image_path, status) VALUES (?, ?, ?, ?, ?, ?, 'Available')");
        $stmt->execute([$isbn, $title, $author_id, $category_id, $publish_year, $image_path]);
        
        logActivity($pdo, $_SESSION['user_id'], "Added book: $title");
        
        echo json_encode(['success' => true, 'message' => 'Book added successfully']);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'message' => 'ISBN already exists']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
}
?>
