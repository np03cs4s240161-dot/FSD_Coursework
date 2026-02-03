<?php
// Debug Logging
function debugLog($msg) {
    error_log("SaveBook API: " . $msg);
}

// Disable outputting errors to screen (prevents JSON break)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

try {
    debugLog("--------------------------------------------------");
    debugLog("Request received: " . $_SERVER['REQUEST_URI']);

    // verify paths
    if (!file_exists('../includes/functions.php')) throw new Exception("functions.php not found");
    require_once '../includes/functions.php';

    if (!file_exists('../config/db.php')) throw new Exception("db.php not found");
    require_once '../config/db.php';

    // Must disable display_errors AFTER including functions.php because functions.php enables it!
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    error_reporting(E_ALL);

    if (!isset($pdo)) {
        throw new Exception("PDO connection variable not set");
    }

    if (!isLoggedIn()) {
        debugLog("User not logged in");
        echo json_encode(['success' => false, 'message' => 'Unauthorized: Not logged in']);
        exit();
    }
    
    if (!isAdmin()) {
        debugLog("User is not admin. Role: " . ($_SESSION['role'] ?? 'None'));
        echo json_encode(['success' => false, 'message' => 'Unauthorized: Admin access required']);
        exit();
    }
    
    debugLog("Auth success. User: " . $_SESSION['username']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        debugLog("POST Data: " . print_r($_POST, true));

        $isbn = sanitize($_POST['isbn'] ?? '');
        $title = sanitize($_POST['title'] ?? '');
        $author_name = sanitize($_POST['author_name'] ?? '');
        $category_id = $_POST['category_id'] ?? null;
        $publish_year = $_POST['publish_year'] ?? null;
        $image_path = null;

        if (!$isbn || !$title || !$author_name || !$category_id) {
            debugLog("Missing required fields");
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit();
        }

        // Numeric casting
        $category_id = intval($category_id);
        $publish_year = intval($publish_year);

        // Author Logic
        debugLog("Processing Author: $author_name");
        $authorStmt = $pdo->prepare("SELECT id FROM authors WHERE author_name = ?");
        $authorStmt->execute([$author_name]);
        $author = $authorStmt->fetch();
        
        if ($author) {
            $author_id = $author['id'];
            debugLog("Found existing author ID: $author_id");
        } else {
            debugLog("Creating new author");
            $insertAuthor = $pdo->prepare("INSERT INTO authors (author_name) VALUES (?)");
            try {
                $insertAuthor->execute([$author_name]);
                $author_id = $pdo->lastInsertId();
                debugLog("Created author ID: $author_id");
            } catch (PDOException $ae) {
                debugLog("Author creation failed: " . $ae->getMessage());
                throw new Exception("Failed to create author: " . $ae->getMessage());
            }
        }

        // Image Upload
        if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            debugLog("Image upload detected");
            if ($_FILES['book_image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['book_image'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                
                if (in_array($ext, $allowed)) {
                    $filename = uniqid('book_') . '.' . $ext;
                    // Ensure uploads dir exists
                    $uploadDir = '../uploads/';
                    if (!is_dir($uploadDir)) {
                        debugLog("Creating uploads directory");
                        if (!mkdir($uploadDir, 0777, true)) {
                            throw new Exception("Failed to create uploads directory");
                        }
                    }
                    
                    $destination = $uploadDir . $filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        $image_path = 'uploads/' . $filename;
                        debugLog("Image moved to: $image_path");
                    } else {
                        debugLog("move_uploaded_file failed");
                        throw new Exception("Failed to move uploaded file");
                    }
                } else {
                    throw new Exception("Invalid file type: $ext");
                }
            } else {
                debugLog("Upload error code: " . $_FILES['book_image']['error']);
                throw new Exception("Image upload error code: " . $_FILES['book_image']['error']);
            }
        }

        // Save Book
        debugLog("Inserting book...");
        $stmt = $pdo->prepare("INSERT INTO books (isbn, title, author_id, category_id, publish_year, image_path, status) VALUES (?, ?, ?, ?, ?, ?, 'Available')");
        
        try {
            $stmt->execute([$isbn, $title, $author_id, $category_id, $publish_year, $image_path]);
            $bookId = $pdo->lastInsertId();
            debugLog("Book inserted successfully. ID: $bookId");
            
            logActivity($pdo, $_SESSION['user_id'], "Added book: $title");
            
            echo json_encode(['success' => true, 'message' => 'Book added successfully']);
        } catch (PDOException $e) {
            debugLog("Insert Exception: " . $e->getMessage());
            if ($e->getCode() == 23000) {
                echo json_encode(['success' => false, 'message' => 'ISBN already exists']);
            } else {
                throw $e;
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid Request Method']);
    }

} catch (Throwable $e) {
    debugLog("CRITICAL ERROR: " . $e->getMessage());
    debugLog("Trace: " . $e->getTraceAsString());
    echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
}
