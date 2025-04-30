<?php
session_start();
require '../dbcon.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized');
    }

    $user_id = $_SESSION['user_id'];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Place new order
        if (!empty($_SESSION['cart'])) {
            $conn->begin_transaction();
            
            try {
                // Calculate totals
                $subtotal = 0;
                foreach ($_SESSION['cart'] as $item) {
                    $subtotal += $item['price'] * $item['quantity'];
                }
                $delivery_fee = 20.00;
                $tax = $subtotal * 0.12;
                $total = $subtotal + $delivery_fee + $tax;
                
                // Create order
                $stmt = $conn->prepare("
                    INSERT INTO orders (
                        customer_id, 
                        order_date, 
                        delivery_address, 
                        contact_number, 
                        special_instructions, 
                        payment_method, 
                        subtotal, 
                        delivery_fee, 
                        tax, 
                        total_amount, 
                        status
                    ) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, 'processing')
                ");
                
                $stmt->bind_param(
                    "issssdddd", 
                    $user_id,
                    $_POST['address'],
                    $_POST['phone'],
                    $_POST['note'],
                    $_POST['payment_method'],
                    $subtotal,
                    $delivery_fee,
                    $tax,
                    $total
                );
                
                if (!$stmt->execute()) {
                    throw new Exception('Failed to create order');
                }
                
                $order_id = $conn->insert_id;
                
                // Add order items
                $item_stmt = $conn->prepare("
                    INSERT INTO order_items (
                        order_id, 
                        menu_id, 
                        quantity, 
                        price
                    ) VALUES (?, ?, ?, ?)
                ");
                
                foreach ($_SESSION['cart'] as $item) {
                    $item_stmt->bind_param(
                        "iiid", 
                        $order_id,
                        $item['id'],
                        $item['quantity'],
                        $item['price']
                    );
                    if (!$item_stmt->execute()) {
                        throw new Exception('Failed to add order items');
                    }
                }
                
                // Handle payment
                if ($_POST['payment_method'] === 'paymongo') {
                    // In real app, integrate with PayMongo API
                    // For demo, we'll just simulate success
                    $payment_stmt = $conn->prepare("UPDATE orders SET status = 'paid' WHERE order_id = ?");
                    $payment_stmt->bind_param("i", $order_id);
                    $payment_stmt->execute();
                }
                
                // Clear cart
                unset($_SESSION['cart']);
                
                $conn->commit();
                
                $response = [
                    'success' => true,
                    'order_id' => $order_id,
                    'redirect' => "order_confirmation.php?id=$order_id"
                ];
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
        } else {
            $response['message'] = 'Cart is empty';
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get order history
        $stmt = $conn->prepare("
            SELECT o.order_id, o.order_date, o.total_amount, o.status 
            FROM orders o
            JOIN customers c ON o.customer_id = c.customer_id
            JOIN users u ON c.user_id = u.user_id
            WHERE u.user_id = ?
            ORDER BY o.order_date DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $response = [
            'success' => true,
            'orders' => $result->fetch_all(MYSQLI_ASSOC)
        ];
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>