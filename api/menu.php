<?php
require_once '../dbcon.php';
require_once '../auth.php';

header('Content-Type: application/json');

// Verify admin access
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        // Handle menu item addition
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = $_POST['price'] ?? 0;
            $category = $_POST['category'] ?? 'main';
            $avail = $_POST['avail'] ?? 'main';
            
            // Handle file upload
            $imageUrl = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/menu/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $imageUrl = '../uploads/menu/' . $fileName;
                }
            }
            
            $stmt = $conn->prepare("INSERT INTO menus (name, description, price, image_url, category, avail) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdsss", $name, $description, $price, $imageUrl, $category, $avail);
            
            if ($stmt->execute()) {
                $_SESSION['alert'] = 'Menu item added successfully';
                header('Location: ../admin/admin_dashboard.php');
                exit;
            } else {
                $_SESSION['alert'] = 'Failed to add menu item';
                header('Location: ../admin/admin_dashboard.php');
                exit;
            }
            
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;
        

        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = $_POST['id'] ?? 0;
                $name = $_POST['name'] ?? '';
                $description = $_POST['description'] ?? '';
                $price = $_POST['price'] ?? 0;
                $category = $_POST['category'] ?? 'main';
                $avail = isset($_POST['avail']) && $_POST['avail'] === 'Available' ? 'Available' : 'NotAvailable';
        
                $imageUrl = ''; // default empty
                // Get current image
                $stmt = $conn->prepare("SELECT image_url FROM menus WHERE menu_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows === 1) {
                    $item = $result->fetch_assoc();
                    $imageUrl = $item['image_url'];
                }
        
                // Upload new image if provided
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../uploads/menu/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
                    $targetPath = $uploadDir . $fileName;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                        // Delete old image if needed
                        if (!empty($imageUrl) && file_exists('../' . $imageUrl)) {
                            unlink('../' . $imageUrl);
                        }
                        $imageUrl = '../uploads/menu/' . $fileName;
                    }
                }
        
                if (!empty($imageUrl)) {
                    // Update with image
                    $stmt = $conn->prepare("UPDATE menus SET name=?, description=?, price=?, category=?, avail=?, image_url=? WHERE menu_id=?");
                    $stmt->bind_param("ssdsssi", $name, $description, $price, $category, $avail, $imageUrl, $id);
                } else {
                    // Update without image
                    $stmt = $conn->prepare("UPDATE menus SET name=?, description=?, price=?, category=?, avail=? WHERE menu_id=?");
                    $stmt->bind_param("ssdssi", $name, $description, $price, $category, $avail, $id);
                }
                
        
                if ($stmt->execute()) {
                    $_SESSION['alert'] = 'Menu item updated successfully';
                    header('Location: ../admin/admin_dashboard.php');
                    exit;
                } else {
                    $_SESSION['alert'] = 'Failed to update menu item';
                    header('Location: ../admin/admin_dashboard.php');
                    exit;
                }
                $stmt->close();
            }
            break;
        
                case 'delete':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['menu_id'])) {
                        $id = intval($_POST['menu_id']);
                    
                    // Debug: Log the ID being deleted
                    error_log("Attempting to delete menu item with ID: " . $id);
                    
                    $stmt = $conn->prepare("DELETE FROM menus WHERE menu_id = ?");
                    if (!$stmt) {
                        error_log("Prepare failed: " . $conn->error);
                        $_SESSION['alert'] = 'Database error';
                        header("Location: ../admin/admin_dashboard.php");
                        exit;
                    }
                    
                    $stmt->bind_param("i", $id);
                    if ($stmt->execute()) {
                        error_log("Delete query executed, affected rows: " . $stmt->affected_rows);
                        if ($stmt->affected_rows > 0) {
                            $_SESSION['alert'] = 'Menu item deleted successfully';
                        } else {
                            $_SESSION['alert'] = 'No menu item found with that ID';
                        }
                    } else {
                        error_log("Execute failed: " . $stmt->error);
                        $_SESSION['alert'] = 'Failed to delete menu item: ' . $stmt->error;
                    }
                    
                    $stmt->close();
                    header("Location: ../admin/admin_dashboard.php");
                    exit;
                }
                break;
        
        
    case 'get':
        // Get single menu item
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $id = $_GET['id'] ?? 0;
            
            $stmt = $conn->prepare("SELECT * FROM resys_menus WHERE menu_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $item = $result->fetch_assoc();
                echo json_encode(['success' => true, 'data' => $item]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Menu item not found']);
            }
            $stmt->close();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;
        
    case 'list':
        // Get all menu items
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $query = "SELECT * FROM resys_menus ORDER BY name ASC";
            $result = $conn->query($query);
            
            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
            
            echo json_encode(['success' => true, 'data' => $items]);
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
?>