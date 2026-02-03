<?php
require_once '../includes/functions.php';
require_once '../config/db.php';

// Must be after functions.php
ini_set('display_errors', 0);

header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = $_POST['book_id'] ?? null;
    $isbn = sanitize($_POST['isbn'] ?? '');
    $title = sanitize($_POST['title'] ?? '');
    $author_name = sanitize($_POST['author_name'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $publish_year = $_POST['publish_year'] ?? null;

    if (!$book_id || !$isbn || !$title || !$author_name) {
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

    try {
        // Fetch current book for activity log and old image
        $stmt = $pdo->prepare("SELECT image_path FROM books WHERE id = ?");
        $stmt->execute([$book_id]);
        $current_book = $stmt->fetch();
        $image_path = $current_book['image_path'] ?? null;

        // Handle new image upload
        if (isset($_FILES['book_image'])) {
            if ($_FILES['book_image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['book_image'];
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                
                if (in_array(strtolower($ext), $allowed)) {
                    $filename = uniqid('book_') . '.' . $ext;
                    $destination = '../uploads/' . $filename;
                    
                    // Ensure uploads directory exists
                    if (!is_dir('../uploads')) {
                        mkdir('../uploads', 0777, true);
                    }

                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        // Delete old image if exists
                        if ($image_path && file_exists('../' . $image_path)) {
                            unlink('../' . $image_path);
                        }
                        $image_path = 'uploads/' . $filename;
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to upload new image. Check folder permissions.']);
                        exit();
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: ' . implode(', ', $allowed)]);
                    exit();
                }
            } elseif ($_FILES['book_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $err = $_FILES['book_image']['error'];
                $msg = 'Update upload error: ' . $err;
                if ($err === UPLOAD_ERR_INI_SIZE) $msg = 'File exceeds upload_max_filesize';
                echo json_encode(['success' => false, 'message' => $msg]);
                exit();
            }
        }

        $stmt = $pdo->prepare("UPDATE books SET isbn = ?, title = ?, author_id = ?, category_id = ?, publish_year = ?, image_path = ? WHERE id = ?");
        $stmt->execute([$isbn, $title, $author_id, $category_id, $publish_year, $image_path, $book_id]);
        
        logActivity($pdo, $_SESSION['user_id'], "Updated book: $title");
        
        echo json_encode(['success' => true, 'message' => 'Book updated successfully']);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'message' => 'ISBN already exists for another book']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    }
}
?>
