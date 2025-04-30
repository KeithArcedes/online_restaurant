<?php
session_start();
require '../dbcon.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to proceed with checkout";
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get customer data
try {
    $customer_stmt = $conn->prepare("SELECT * FROM customers WHERE user_id = ?");
    $customer_stmt->bind_param("i", $user_id);
    $customer_stmt->execute();
    $customer_result = $customer_stmt->get_result();
    $customer = $customer_result->fetch_assoc();
    $customer_stmt->close();

    if (!$customer) {
        throw new Exception("Customer record not found");
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error retrieving customer data: " . $e->getMessage();
    header("Location: checkout_form.php");
    exit();
}

// Validate cart has items
if (empty($_SESSION['cart'])) {
    $_SESSION['error'] = "Your cart is empty";
    header("Location: customer_loggedin.php");
    exit();
}

// Process checkout form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $payment_method = in_array($_POST['payment_method'], ['paymongo', 'cod']) 
        ? $_POST['payment_method'] 
        : null;
    $delivery_address = filter_var($_POST['delivery_address'], FILTER_SANITIZE_STRING);
    $contact_number = filter_var($_POST['contact_number'], FILTER_SANITIZE_STRING);
    $special_instructions = isset($_POST['special_instructions']) 
        ? filter_var($_POST['special_instructions'], FILTER_SANITIZE_STRING) 
        : '';

    // Validate required fields
    if (!$payment_method || empty($delivery_address) || empty($contact_number)) {
        $_SESSION['error'] = "Please fill in all required fields";
        header("Location: checkout_form.php");
        exit();
    }

    try {
        $conn->begin_transaction();

        // Calculate totals from cart
        $cart_items = $_SESSION['cart'];
        $subtotal = 0;
        foreach ($cart_items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $delivery_fee = 50.00;
        $tax = $subtotal * 0.12;
        $total = $subtotal + $delivery_fee + $tax;

        // Create order
        $order_stmt = $conn->prepare("
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
            ) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $initial_status = ($payment_method === 'cod') ? 'pending' : 'processing';
        
        $order_stmt->bind_param(
            "issssdddds",
            $customer['customer_id'],
            $delivery_address,
            $contact_number,
            $special_instructions,
            $payment_method,
            $subtotal,
            $delivery_fee,
            $tax,
            $total,
            $initial_status
        );

        if (!$order_stmt->execute()) {
            throw new Exception("Failed to create order: " . $conn->error);
        }

        $order_id = $conn->insert_id;
        $order_stmt->close();

        // Add order items and clear cart items from database
        $item_stmt = $conn->prepare("
            INSERT INTO order_items (
                order_id,
                menu_id,
                quantity,
                price,
                item_total
            ) VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($cart_items as $item) {
            // Add to order items
            $item_total = $item['price'] * $item['quantity'];
            $item_stmt->bind_param(
                "iiidd",
                $order_id,
                $item['id'],
                $item['quantity'],
                $item['price'],
                $item_total
            );
            if (!$item_stmt->execute()) {
                throw new Exception("Failed to add order item: " . $item['name'] . " - " . $conn->error);
            }
        }
        $item_stmt->close();

        // Handle payment
        if ($payment_method === 'paymongo') {
            $payment_stmt = $conn->prepare("
                INSERT INTO payments (
                    order_id,
                    payment_method,
                    amount_paid,
                    payment_date,
                    status
                ) VALUES (?, ?, ?, NOW(), 'completed')
            ");
            $payment_stmt->bind_param("isd", $order_id, $payment_method, $total);
            
            if (!$payment_stmt->execute()) {
                throw new Exception("Failed to process payment: " . $conn->error);
            }
            $payment_stmt->close();

            // Update order status
            $status_stmt = $conn->prepare("UPDATE orders SET status = 'confirmed' WHERE order_id = ?");
            $status_stmt->bind_param("i", $order_id);
            if (!$status_stmt->execute()) {
                throw new Exception("Failed to update order status: " . $conn->error);
            }
            $status_stmt->close();
        } else {
            // For COD
            $payment_stmt = $conn->prepare("
                INSERT INTO payments (
                    order_id,
                    payment_method,
                    amount_paid,
                    status
                ) VALUES (?, 'cod', 0, 'pending')
            ");
            $payment_stmt->bind_param("i", $order_id);
            
            if (!$payment_stmt->execute()) {
                throw new Exception("Failed to create payment record: " . $conn->error);
            }
            $payment_stmt->close();
        }

        // Clear cart after successful order
        unset($_SESSION['cart']);

        // Delete items from the cart table in the database
        $delete_cart_stmt = $conn->prepare("DELETE FROM cart WHERE customer_id = ?");
        $delete_cart_stmt->bind_param("i", $customer['customer_id']);
        if (!$delete_cart_stmt->execute()) {
            throw new Exception("Failed to clear cart from database");
        }
        $delete_cart_stmt->close();

        // Commit transaction
        $conn->commit();

        // Redirect to order confirmation
        $_SESSION['success'] = "Order placed successfully!";
        header("Location: order_confirmation.php?id=" . $order_id);
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error processing your order: " . $e->getMessage();
        header("Location: checkout_form.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid request method";
    header("Location: checkout_form.php");
    exit();
}
?>