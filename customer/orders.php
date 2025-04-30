<?php
require_once '../dbcon.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
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

// Fetch orders for this customer
$order_stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, o.total_amount, o.status, 
           COUNT(oi.order_item_id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    WHERE o.customer_id = ?
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
");
$order_stmt->bind_param("i", $customer['customer_id']);
$order_stmt->execute();
$orders = $order_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$order_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Food Eatery</title>
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
        
        .order-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
            height: 100%;
        }
        
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .order-card .card-body {
            padding: 1.5rem;
        }
        
        .order-card .badge {
            font-size: 0.8rem;
            padding: 0.35em 0.65em;
        }
        
        .empty-orders {
            padding: 3rem 0;
        }
        
        .empty-orders i {
            opacity: 0.5;
        }
    </style>
</head>
<body>
   

    <!-- Main Content -->
    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-history me-2"></i> My Orders</h2>
                    <a href="customer_loggedin.php" class="btn btn-outline-primary">
                        <i class="fas fa-utensils me-2"></i> Order Now
                    </a>
                </div>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($orders)): ?>
                    <div class="row">
                        <?php foreach ($orders as $order): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card order-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-2">
                                            <h5 class="card-title mb-0">Order #<?= htmlspecialchars($order['order_id']) ?></h5>
                                            <span class="badge bg-<?php 
                                                switch($order['status']) {
                                                    case 'completed': echo 'success'; break;
                                                    case 'confirmed': echo 'primary'; break;
                                                    case 'processing': echo 'warning'; break;
                                                    case 'pending': echo 'warning'; break;
                                                    case 'cancelled': echo 'danger'; break;
                                                    default: echo 'secondary';
                                                }
                                            ?>">
                                                <?= ucfirst(htmlspecialchars($order['status'])) ?>
                                            </span>
                                        </div>
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-calendar-alt me-2"></i>
                                            <?= date('F j, Y \a\t g:i A', strtotime($order['order_date'])) ?>
                                        </p>
                                        <p class="mb-2">
                                            <i class="fas fa-box-open me-2"></i>
                                            <?= $order['item_count'] ?> item<?= $order['item_count'] > 1 ? 's' : '' ?>
                                        </p>
                                        <p class="fw-bold mb-0">
                                            <i class="fas fa-receipt me-2"></i>
                                            ₱<?= number_format($order['total_amount'], 2) ?>
                                        </p>
                                        <div class="d-grid mt-3">
                                            <a href="order_details.php?id=<?= $order['order_id'] ?>" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye me-2"></i> View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center empty-orders">
                        <i class="fas fa-history fa-4x text-muted mb-4"></i>
                        <h4 class="mb-3">No Orders Yet</h4>
                        <p class="text-muted mb-4">You haven't placed any orders yet. Browse our menu to get started!</p>
                        <a href="customer_loggedin.php" class="btn btn-primary">
                            <i class="fas fa-utensils me-2"></i> Order Now
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

  
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>