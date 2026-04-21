<?php
require_once 'config.php';
session_start();

header('Content-Type: application/json');

/**
 * Handle gallery image operations: list, upload, delete, replace
 * Specifically for the wedding planner portal
 */

// Basic authentication check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$db = getDB();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        $category = $_GET['category'] ?? '';
        $stmt = $db->prepare("SELECT id, category, image_url, title FROM gallery_images WHERE category = ? ORDER BY id DESC");
        $stmt->bind_param('s', $category);
        $stmt->execute();
        $result = $stmt->get_result();
        $images = [];
        while ($row = $result->fetch_assoc()) {
            $images[] = $row;
        }
        echo json_encode(['success' => true, 'images' => $images]);
        break;

    case 'delete':
        // Only wedding planners should be able to delete/replace
        if (($_SESSION['login_type'] ?? '') !== 'planner') {
            echo json_encode(['success' => false, 'message' => 'Only planners can delete images']);
            exit();
        }
        
        $id = $_POST['id'] ?? 0;
        
        // Use a transaction to ensure database consistency
        $db->begin_transaction();
        try {
            // Get image info first
            $stmt = $db->prepare("SELECT image_url FROM gallery_images WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $image = $res->fetch_assoc();
            
            if (!$image) {
                throw new Exception('Image not found');
            }
            
            // Delete from database
            $delStmt = $db->prepare("DELETE FROM gallery_images WHERE id = ?");
            $delStmt->bind_param('i', $id);
            $delStmt->execute();
            
            // Note: In a real app, you would also delete the file from the filesystem:
            // if ($image['image_url'] && file_exists('../' . $image['image_url'])) {
            //     unlink('../' . $image['image_url']);
            // }
            
            $db->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $db->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'replace':
    case 'upload':
        // Only wedding planners can upload/replace
        if (($_SESSION['login_type'] ?? '') !== 'planner') {
            echo json_encode(['success' => false, 'message' => 'Only planners can modify images']);
            exit();
        }

        $id = $_POST['id'] ?? 0; // if replacing
        $category = $_POST['category'] ?? '';
        $title = $_POST['title'] ?? '';
        
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No image uploaded']);
            exit();
        }

        // Handle file upload
        $uploadDir = '../images/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExt = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExt;
        $uploadPath = $uploadDir . $fileName;
        $dbPath = 'images/uploads/' . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            if ($action === 'replace' && $id > 0) {
                // Get old image path if replacing
                $stmt = $db->prepare("SELECT image_url FROM gallery_images WHERE id = ?");
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $res = $stmt->get_result();
                $oldImg = $res->fetch_assoc();
                
                // Update database
                $stmt = $db->prepare("UPDATE gallery_images SET image_url = ?, title = ? WHERE id = ?");
                $stmt->bind_param('ssi', $dbPath, $title, $id);
                $stmt->execute();
                
                // Delete old file if it was custom
                if ($oldImg && strpos($oldImg['image_url'], 'uploads/') !== false) {
                    @unlink('../' . $oldImg['image_url']);
                }
                
                echo json_encode(['success' => true, 'image_url' => $dbPath]);
            } else {
                // New image
                $stmt = $db->prepare("INSERT INTO gallery_images (category, image_url, title) VALUES (?, ?, ?)");
                $stmt->bind_param('sss', $category, $dbPath, $title);
                $stmt->execute();
                echo json_encode(['success' => true, 'id' => $db->insert_id, 'image_url' => $dbPath]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
