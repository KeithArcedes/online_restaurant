<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Food Eatery</title>
    <link href="../assets/styles/styles.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .cart-item-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        .payment-method {
            cursor: pointer;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        .payment-method:hover, .payment-method.active {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }
        .payment-method.active {
            background-color: #e7f1ff;
        }
        .card-details {
            display: none;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-top: 10px;
        }
        .sticky-summary {
            position: -webkit-sticky;
            position: sticky;
            top: 20px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row">
            <!-- Delivery & Payment Info Column -->
            <div class="col-md-8">
                <a href="customer_loggedin.php" class="btn btn-outline-secondary mb-4">
                    <i class="fas fa-arrow-left me-2"></i>Back to Menu
                </a>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <h2 class="mb-4">Checkout</h2>

                <!-- Delivery Information -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-truck me-2"></i>Delivery Information</h5>
                    </div>
                    <div class="card-body">
                    <?php
                        session_start();
                        include '../dbcon.php';

                        // Check if user_id is set in the session
                        if (isset($_SESSION['user_id'])) {
                            $user_id = $_SESSION['user_id'];

                            // Query to get customer details using the user_id
                            $query = "SELECT * FROM customers WHERE user_id = ?";
                            
                            // Prepare statement
                            if ($stmt = $conn->prepare($query)) {
                                // Bind the user_id parameter to the query
                                $stmt->bind_param("i", $user_id);
                                
                                // Execute the query
                                $stmt->execute();
                                
                                // Get the result
                                $result = $stmt->get_result();
                                
                                // Fetch the customer details
                                $customer = $result->fetch_assoc();
                                
                                // Close the statement
                                $stmt->close();
                            } else {
                                // Handle errors with the prepared statement
                                $customer = null;
                            }
                        } else {
                            // Handle case when user_id is not set in the session
                            $customer = null;
                        }

                        // Check if the cart is not empty
                        if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
                            $cart_items = $_SESSION['cart'];
                            $item_count = count($cart_items);

                            // Calculate the subtotal
                            $subtotal = 0;
                            foreach ($cart_items as $item) {
                                $subtotal += $item['price'] * $item['quantity'];
                            }

                            // Define a fixed delivery fee (you can adjust this as per your logic)
                            $delivery_fee = 50.00; // Example delivery fee

                            // Calculate tax (12% of subtotal)
                            $tax = $subtotal * 0.12;

                            // Calculate total
                            $total = $subtotal + $delivery_fee + $tax;
                        } else {
                            // If there are no items in the cart, set defaults
                            $cart_items = [];
                            $item_count = 0;
                            $subtotal = 0;
                            $delivery_fee = 0;
                            $tax = 0;
                            $total = 0;
                        }

                        // Display error message if exists
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show">
            '.$_SESSION['error'].'
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['error']);
}
                        ?>

                    <form id="checkoutForm" method="POST" action="checkout.php">
                        <!-- Customer Full Name (Read-Only) -->
                        <div class="mb-3">
                            <label for="fullName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="fullName" 
                                value="<?= htmlspecialchars($customer['first_name'] . ' ' . htmlspecialchars($customer['last_name'])) ?>" readonly>
                        </div>
                        
                        <!-- Delivery Address -->
                        <div class="mb-3">
                            <label for="deliveryAddress" class="form-label">Delivery Address*</label>
                            <textarea class="form-control" id="deliveryAddress" name="delivery_address" required rows="3"><?= htmlspecialchars($customer['address'] ?? '') ?></textarea>
                        </div>

                        <!-- Contact Number -->
                        <div class="mb-3">
                            <label for="contactNumber" class="form-label">Contact Number*</label>
                            <input type="tel" class="form-control" id="contactNumber" name="contact_number" 
                                value="<?= htmlspecialchars($customer['phone'] ?? '') ?>" required>
                        </div>

                        <!-- Special Instructions -->
                        <div class="mb-3">
                            <label for="specialInstructions" class="form-label">Special Instructions</label>
                            <textarea class="form-control" id="specialInstructions" name="special_instructions" rows="2" placeholder="Any special delivery instructions..."></textarea>
                        </div>

                        <!-- Payment Method -->
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Method</h5>
                            </div>
                            <div class="card-body">
                                <!-- PayMongo Payment Option -->
                                <div class="payment-method active" id="paymongoMethod">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" 
                                            id="paymongoOption" value="paymongo" checked>
                                        <label class="form-check-label fw-bold" for="paymongoOption">
                                            <i class="fab fa-cc-visa me-2"></i>Credit/Debit Card (PayMongo)
                                        </label>
                                    </div>
                                    <div id="cardDetails" class="card-details mt-3">
                                        <div class="mb-3">
                                            <label for="cardNumber" class="form-label">Card Number* (16 digit PIN)</label>
                                            <input type="text" class="form-control" id="cardNumber" 
                                                placeholder="1234 5678 9012 3456" pattern="\d{16}" 
                                                title="Please enter a 16-digit card number" required>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="expiryDate" class="form-label">Expiry Date*</label>
                                                <input type="text" class="form-control" id="expiryDate" 
                                                    placeholder="MM/YY" pattern="\d{2}/\d{2}" 
                                                    title="Please enter in MM/YY format" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="cvv" class="form-label">CVV*</label>
                                                <input type="text" class="form-control" id="cvv" 
                                                    placeholder="123" pattern="\d{3}" 
                                                    title="Please enter a 3-digit CVV" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="cardName" class="form-label">Name on Card*</label>
                                            <input type="text" class="form-control" id="cardName" 
                                                value="<?= htmlspecialchars($customer['first_name'] . ' ' . htmlspecialchars($customer['last_name'])) ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Cash on Delivery Option -->
                                <div class="payment-method" id="codMethod">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" 
                                            id="codOption" value="cod">
                                        <label class="form-check-label fw-bold" for="codOption">
                                            <i class="fas fa-money-bill-wave me-2"></i>Cash on Delivery (COD)
                                        </label>
                                    </div>
                                    <div class="mt-2 text-muted">
                                        <small>Pay with cash when your order arrives</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                                    <!-- Order Summary Column -->
                                <div class="col-md-6">
                                    <div class="card shadow-sm sticky-summary">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <h6>Items (<?= $item_count ?>)</h6>
                                                <div class="list-group mb-3">
                                                    <?php foreach ($cart_items as $item): ?>
                                                        <div class="list-group-item">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div class="d-flex align-items-center">
                                                                    <img src="<?= htmlspecialchars($item['image_url'] ?? '') ?>" 
                                                                        class="cart-item-img me-3" alt="<?= htmlspecialchars($item['name']) ?>">
                                                                    <div>
                                                                        <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                                                        <small class="text-muted"><?= htmlspecialchars($item['category'] ?? 'No category') ?></small>
                                                                    </div>
                                                                </div>
                                                                <div class="text-end">
                                                                    <div class="fw-bold">₱<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                                                                    <small class="text-muted"><?= $item['quantity'] ?> × ₱<?= number_format($item['price'], 2) ?></small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3 border-top pt-3">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Subtotal:</span>
                                                    <span>₱<?= number_format($subtotal, 2) ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Delivery Fee:</span>
                                                    <span>₱<?= number_format($delivery_fee, 2) ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Tax (12%):</span>
                                                    <span>₱<?= number_format($tax, 2) ?></span>
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between fw-bold fs-5 border-top pt-3 mb-4">
                                                <span>Total:</span>
                                                <span>₱<?= number_format($total, 2) ?></span>
                                            </div>
                                            
                                            <button type="submit" form="checkoutForm" class="btn btn-primary w-100 py-2">
                                                <i class="fas fa-check-circle me-2"></i>Place Order
                                            </button>
                                        </div>
                                    </div>
                                </div>
                    </form>
                </div>
            </div>


        </div>
    </div>
    <script>
document.addEventListener("DOMContentLoaded", function () {
    const paymongoOption = document.getElementById("paymongoOption");
    const codOption = document.getElementById("codOption");
    const cardDetails = document.getElementById("cardDetails");

    // Select card input fields
    const cardInputs = cardDetails.querySelectorAll("input");

    function togglePaymentFields() {
        if (paymongoOption.checked) {
            cardDetails.style.display = "block";
            cardInputs.forEach(input => input.required = true);
        } else {
            cardDetails.style.display = "none";
            cardInputs.forEach(input => input.required = false);
        }
    }

    paymongoOption.addEventListener("change", togglePaymentFields);
    codOption.addEventListener("change", togglePaymentFields);

    togglePaymentFields(); // Initial check
});
</script>

   
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
