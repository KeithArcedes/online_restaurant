<?php
session_start();
// Handle JSON requests
if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') === 0) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (is_array($input)) {
        $_POST = $input;
    }
}

require '../../dbcon.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    $menu_id = intval($_POST['menu_id']);
                    $quantity = intval($_POST['quantity'] ?? 1);

                    $stmt = $conn->prepare("SELECT menu_id, name, price, category, image_url FROM menus WHERE menu_id = ? AND avail = 'Available'");
                    $stmt->bind_param("i", $menu_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        $item = $result->fetch_assoc();

                        if (!isset($_SESSION['cart'])) {
                            $_SESSION['cart'] = [];
                        }

                        if (isset($_SESSION['cart'][$menu_id])) {
                            $_SESSION['cart'][$menu_id]['quantity'] += $quantity;
                        } else {
                            $_SESSION['cart'][$menu_id] = [
                                'id' => $item['menu_id'],
                                'name' => $item['name'],
                                'price' => $item['price'],
                                'quantity' => $quantity,
                                'category' => $item['category'],
                                'image_url' => $item['image_url']
                            ];
                        }

                        $response = [
                            'success' => true,
                            'cart_count' => array_sum(array_column($_SESSION['cart'], 'quantity'))
                        ];
                    } else {
                        $response['message'] = 'Item not available';
                    }
                    break;

                case 'update':
                    if (isset($_POST['items'])) {
                        foreach ($_POST['items'] as $item) {
                            $menu_id = intval($item['menu_id']);
                            $quantity = intval($item['quantity']);

                            if (isset($_SESSION['cart'][$menu_id])) {
                                if ($quantity > 0) {
                                    $_SESSION['cart'][$menu_id]['quantity'] = $quantity;
                                } else {
                                    unset($_SESSION['cart'][$menu_id]);
                                }
                            }
                        }
                        $response['success'] = true;
                    }
                    break;

                case 'sync':
                    if (isset($_SESSION['user_id']) && isset($_POST['cart'])) {
                        $user_id = $_SESSION['user_id'];
                        $cartItems = $_POST['cart'];
                        
                        // Get menu details for all cart items
                        $menu_ids = array_map(function($item) { return $item['id']; }, $cartItems);
                        $menu_ids_str = implode(',', array_map('intval', $menu_ids));
                        
                        $menu_stmt = $conn->prepare("SELECT menu_id, name, price, category, image_url FROM menus WHERE menu_id IN ($menu_ids_str)");
                        $menu_stmt->execute();
                        $menu_result = $menu_stmt->get_result();
                        $menu_items = [];
                        while($row = $menu_result->fetch_assoc()) {
                            $menu_items[$row['menu_id']] = $row;
                        }
                        
                        // Update session cart with complete menu details
                        $_SESSION['cart'] = [];
                        foreach ($cartItems as $item) {
                            $menu_id = $item['id'];
                            if (isset($menu_items[$menu_id])) {
                                $_SESSION['cart'][$menu_id] = [
                                    'id' => $menu_id,
                                    'name' => $menu_items[$menu_id]['name'],
                                    'price' => $menu_items[$menu_id]['price'],
                                    'quantity' => $item['quantity'],
                                    'category' => $menu_items[$menu_id]['category'],
                                    'image_url' => $menu_items[$menu_id]['image_url']
                                ];
                            }
                        }
                        
                        // Get customer_id from user_id
                        $cust_stmt = $conn->prepare("SELECT customer_id FROM customers WHERE user_id = ?");
                        $cust_stmt->bind_param("i", $user_id);
                        $cust_stmt->execute();
                        $cust_result = $cust_stmt->get_result();
                        
                        if ($cust_result->num_rows > 0) {
                            $customer = $cust_result->fetch_assoc();
                            $customer_id = $customer['customer_id'];
                            
                            // Loop through each item in the cart and either insert or update in the database
                            foreach ($cartItems as $item) {
                                $menu_id = intval($item['id']);
                                $quantity = intval($item['quantity']);
                        
                                // Check if the item already exists in the DB cart
                                $check = $conn->prepare("SELECT cart_id FROM cart WHERE customer_id = ? AND menu_id = ?");
                                $check->bind_param("ii", $customer_id, $menu_id);
                                $check->execute();
                                $exists = $check->get_result();
                        
                                if ($exists->num_rows > 0) {
                                    // Update quantity if the item exists
                                    $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE customer_id = ? AND menu_id = ?");
                                    $update->bind_param("iii", $quantity, $customer_id, $menu_id);
                                    $update->execute();
                                } else {
                                    // Insert new item if it doesn't exist in the cart
                                    $insert = $conn->prepare("INSERT INTO cart (customer_id, menu_id, quantity) VALUES (?, ?, ?)");
                                    $insert->bind_param("iii", $customer_id, $menu_id, $quantity);
                                    $insert->execute();
                                }
                            }
                    
                            $response['success'] = true;
                        } else {
                            $response['message'] = 'Customer not found';
                        }
                    } else {
                        $response['message'] = 'User not logged in or empty cart';
                    }
                    break;

                case 'remove':
                    $menu_id = intval($_POST['menu_id']);
                    if (isset($_SESSION['cart'][$menu_id])) {
                        unset($_SESSION['cart'][$menu_id]);
                        $response['success'] = true;
                    }
                    break;

                case 'clear':
                    unset($_SESSION['cart']);
                    $response['success'] = true;
                    break;

                default:
                    $response['message'] = 'Invalid action';
                    break;
            }
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $response['success'] = true;
        $response['cart'] = $_SESSION['cart'] ?? [];
        $response['cart_count'] = array_sum(array_column($response['cart'], 'quantity'));
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>
