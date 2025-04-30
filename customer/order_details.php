<?php
require_once '../dbcon.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Get order ID from URL
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    $_SESSION['error'] = "Invalid order ID";
    header("Location: orders.php");
    exit();
}

// Get customer ID from user ID
$user_id = $_SESSION['user_id'];
$customer_stmt = $conn->prepare("SELECT customer_id FROM customers WHERE user_id = ?");
$customer_stmt->bind_param("i", $user_id);
$customer_stmt->execute();
$customer_result = $customer_stmt->get_result();
$customer = $customer_result->fetch_assoc();
$customer_stmt->close();

if (!$customer) {
    $_SESSION['error'] = "Customer record not found";
    header("Location: ../index.php");
    exit();
}

// Fetch order details (verify it belongs to this customer)
$order_stmt = $conn->prepare("
    SELECT o.*, c.first_name, c.last_name, 
           p.payment_method AS payment_method_from_payments, p.amount_paid
    FROM orders o
    JOIN customers c ON o.customer_id = c.customer_id
    LEFT JOIN payments p ON o.order_id = p.order_id
    WHERE o.order_id = ? AND o.customer_id = ?
");

$order_stmt->bind_param("ii", $order_id, $customer['customer_id']);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$order = $order_result->fetch_assoc();
$order_stmt->close();

if (!$order) {
    $_SESSION['error'] = "Order not found or doesn't belong to you";
    header("Location: orders.php");
    exit();
}

// Check if the order can be canceled (status should be 'pending' or 'confirmed')
if ($order['status'] !== 'pending' && $order['status'] !== 'confirmed') {
    $_SESSION['error'] = "Order cannot be canceled as it is already " . $order['status'];
    header("Location: orders.php");
    exit();
}

// Cancel order logic
if (isset($_POST['cancel_order']) && $_POST['cancel_order'] === 'yes') {
    // Only attempt to update if the current status isn't already 'cancelled'
    if ($order['status'] === 'cancelled') {
        $_SESSION['error'] = "Order has already been cancelled.";
        header("Location: orders.php");
        exit();
    }

    // Update the order status to 'cancelled'
    $update_stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE order_id = ?");
    $update_stmt->bind_param("i", $order_id);
    $update_stmt->execute();
    $update_stmt->close();

    // Fetch the updated order details to confirm the status change
    $order_stmt = $conn->prepare("SELECT status FROM orders WHERE order_id = ?");
    $order_stmt->bind_param("i", $order_id);
    $order_stmt->execute();
    $order_result = $order_stmt->get_result();
    $updated_order = $order_result->fetch_assoc();
    $order_stmt->close();

    // Check if the status has been updated to 'cancelled'
    if ($updated_order && $updated_order['status'] === 'cancelled') {
        $_SESSION['success'] = "Order cancelled successfully.";
    } else {
        $_SESSION['error'] = "Failed to cancel the order. Please try again.";
    }

    header("Location: orders.php");
    exit();
}

// Fetch order items
$items_stmt = $conn->prepare("
    SELECT oi.*, m.name AS item_name, m.image_url 
    FROM order_items oi
    LEFT JOIN menus m ON oi.menu_id = m.menu_id
    WHERE oi.order_id = ?
");

$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$items_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?= $order['order_id'] ?> - Food Eatery</title>
    <!-- Bootstrap CSS -->
    <link href="../assets/styles/styles.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #e74c3c;
            --secondary-color: #2c3e50;
            --accent-color: #f39c12;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f9f9;
        }
        
        .order-header {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .order-item {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .order-summary {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
        }
        
        .product-img {
            min-width: 80px;
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 0.35em 0.65em;
        }
        
        .timeline {
            position: relative;
            padding-left: 1.5rem;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #e9ecef;
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -1.5rem;
            top: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--primary-color);
            border: 2px solid white;
        }
    </style>
</head>
<body>

    <!-- Main Content -->
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-12">
                <a href="orders.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Orders
                </a>
            </div>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="order-header">
            <div class="row">
                <div class="col-md-6">
                    <h3 class="mb-3">Order #<?= $order['order_id'] ?></h3>
                    <p class="mb-1"><strong>Status:</strong> 
                        <span class="badge status-badge bg-<?php 
                            switch($order['status']) {
                                case 'completed': echo 'success'; break;
                                case 'confirmed': echo 'primary'; break;
                                case 'processing': echo 'warning'; break;
                                case 'pending': echo 'secondary'; break;
                                case 'cancelled': echo 'danger'; break;
                                default: echo 'secondary';
                            }
                        ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </p>
                    <p class="mb-1"><strong>Order Date:</strong> <?= date('F j, Y \a\t g:i A', strtotime($order['order_date'])) ?></p>
                    <p class="mb-1"><strong>Payment Method:</strong> <?= ucfirst($order['payment_method'] ?? 'N/A') ?></p>
                    <p class="mb-0"><strong>Payment Status:</strong> <?= ucfirst($order['payment_status'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-6">
                    <h5 class="mb-3">Customer Information</h5>
                    <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></p>
                    <p class="mb-1"><strong>Contact:</strong> <?= htmlspecialchars($order['phone'] ?? 'N/A') ?></p>
                    <p class="mb-0"><strong>Address:</strong> <?= nl2br(htmlspecialchars($order['delivery_address'])) ?></p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i> Order Items</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($items as $item): ?>
                            <div class="order-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                        class="product-img" 
                                        alt="<?= htmlspecialchars($item['item_name']) ?>"
                                        onerror="this.src='../assets/imgs/default-food.jpg'">
                                    <div class="ms-3">
                                        <h6 class="mb-1"><?= htmlspecialchars($item['item_name']) ?></h6>
                                        <p class="mb-0 text-muted">₱<?= number_format($item['price'], 2) ?></p>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <p class="mb-1"><?= $item['quantity'] ?> × ₱<?= number_format($item['price'], 2) ?></p>
                                    <p class="mb-0 fw-bold">₱<?= number_format($item['item_total'], 2) ?></p>
                                </div>
                            </div>

                        <?php endforeach; ?>
                        
                        <?php if (!empty($order['special_instructions'])): ?>
                            <div class="mt-4">
                                <h6><i class="fas fa-sticky-note me-2"></i> Special Instructions</h6>
                                <p class="text-muted"><?= nl2br(htmlspecialchars($order['special_instructions'])) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i> Order Timeline</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <h6>Order Placed</h6>
                                <p class="text-muted mb-0"><?= date('F j, Y \a\t g:i A', strtotime($order['order_date'])) ?></p>
                            </div>
                            
                            <?php if ($order['status'] === 'confirmed' || $order['status'] === 'processing' || $order['status'] === 'completed'): ?>
                                <div class="timeline-item">
                                    <h6>Order Confirmed</h6>
                                    <p class="text-muted mb-0">
                                        <?= $order['status'] === 'pending' ? 'Pending confirmation' : 
                                           date('F j, Y \a\t g:i A', strtotime($order['order_date'] . ' +15 minutes')) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($order['status'] === 'processing' || $order['status'] === 'completed'): ?>
                                <div class="timeline-item">
                                    <h6>Preparing Your Order</h6>
                                    <p class="text-muted mb-0">
                                        <?= date('F j, Y \a\t g:i A', strtotime($order['order_date'] . ' +30 minutes')) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($order['status'] === 'completed'): ?>
                                <div class="timeline-item">
                                    <h6>Order Completed</h6>
                                    <p class="text-muted mb-0">
                                        <?= date('F j, Y \a\t g:i A', strtotime($order['order_date'] . ' +1 hour')) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($order['status'] === 'cancelled'): ?>
                                <div class="timeline-item">
                                    <h6>Order Cancelled</h6>
                                    <p class="text-muted mb-0">
                                        <?= date('F j, Y \a\t g:i A', strtotime($order['order_date'] . ' +15 minutes')) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="order-summary">
                    <h5 class="mb-4"><i class="fas fa-receipt me-2"></i> Order Summary</h5>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>₱<?= number_format($order['subtotal'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Delivery Fee:</span>
                            <span>₱<?= number_format($order['delivery_fee'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (12%):</span>
                            <span>₱<?= number_format($order['tax'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2 mb-3">
                            <span class="fw-bold">Total:</span>
                            <span class="fw-bold">₱<?= number_format($order['total_amount'], 2) ?></span>
                        </div>
                    </div>
                    
                    <?php if ($order['payment_method'] && $order['amount_paid']): ?>
                        <div class="mb-3">
                            <h6><i class="fas fa-credit-card me-2"></i> Payment Details</h6>
                            <p class="mb-1"><strong>Method:</strong> <?= ucfirst($order['payment_method']) ?></p>
                            <p class="mb-1"><strong>Amount Paid:</strong> ₱<?= number_format($order['amount_paid'], 2) ?></p>
                            <p class="mb-0"><strong>Status:</strong> <?= ucfirst($order['status']) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($order['status'] === 'pending' || $order['status'] === 'confirmed'): ?>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                            <input type="hidden" name="cancel_order" value="yes">
                            <button class="btn btn-danger w-100 mt-3">
                                <i class="fas fa-times-circle me-2"></i> Cancel Order
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

 

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cancel order button
        const cancelBtn = document.getElementById('cancelOrderBtn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to cancel this order?')) {
                    fetch('cancel_order.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `order_id=<?= $order['order_id'] ?>`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Order cancelled successfully');
                            window.location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while cancelling the order');
                    });
                }
            });
        }
    });
    </script>
</body>
</html>