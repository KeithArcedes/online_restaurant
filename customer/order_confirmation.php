<?php
session_start();

// Check if order was successful
if (!isset($_SESSION['order_success']) || !$_SESSION['order_success']['status']) {
    header("Location: customer_loggedin.php");
    exit();
}

$order_id = $_SESSION['order_success']['order_id'];
$total = $_SESSION['order_success']['total'];
$payment_method = $_SESSION['order_success']['payment_method'];

// Clear the success message after displaying it
unset($_SESSION['order_success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link href="../assets/styles/styles.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0"><i class="fas fa-check-circle me-2"></i>Order Confirmed</h3>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                        </div>
                        <h4 class="mb-3">Thank you for your order!</h4>
                        <p class="lead">Your order #<?= htmlspecialchars($order_id) ?> has been placed successfully.</p>
                        
                        <div class="alert alert-success">
                            <h5><i class="fas fa-info-circle me-2"></i>Order Details</h5>
                            <p class="mb-1"><strong>Order Number:</strong> #<?= htmlspecialchars($order_id) ?></p>
                            <p class="mb-1"><strong>Total Amount:</strong> ₱<?= number_format($total, 2) ?></p>
                            <p class="mb-1"><strong>Payment Method:</strong> 
                                <?= $payment_method === 'cod' ? 'Cash on Delivery' : 'Credit/Debit Card' ?>
                            </p>
                        </div>
                        
                        <p>We've sent a confirmation to your email. You'll receive another notification when your order is on its way.</p>
                        
                        <div class="mt-4">
                            <a href="customer_loggedin.php" class="btn btn-primary">
                                <i class="fas fa-utensils me-2"></i>Back to Menu
                            </a>
                            <a href="order_history.php" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-history me-2"></i>View Order History
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>