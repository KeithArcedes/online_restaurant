<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth.php");
    exit();
}

require '../dbcon.php';

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT u.user_id, u.email, u.role, c.first_name, c.last_name 
    FROM users u 
    LEFT JOIN customers c ON u.user_id = c.user_id 
    WHERE u.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Get order history
$order_stmt = $conn->prepare("
    SELECT order_id, order_date, total_amount, status 
    FROM orders 
    WHERE customer_id = ? 
    ORDER BY order_date DESC 
    LIMIT 3
");
$order_stmt->bind_param("i", $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$orders = $order_result->fetch_all(MYSQLI_ASSOC);
$order_stmt->close();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add to cart functionality
    if (isset($_POST['add_to_cart'])) {
        $menu_id = intval($_POST['menu_id']);
        $quantity = intval($_POST['quantity']);
        
        // Check if item exists in database
        $check_stmt = $conn->prepare("SELECT menu_id, name, price FROM menus WHERE menu_id = ? AND avail = 'Available'");
        $check_stmt->bind_param("i", $menu_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $item = $check_result->fetch_assoc();
            
            // Initialize cart if not exists
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            // Add item to cart or update quantity
            if (isset($_SESSION['cart'][$menu_id])) {
                $_SESSION['cart'][$menu_id]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$menu_id] = [
                    'id' => $item['menu_id'],
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $quantity
                ];
            }
            
            $_SESSION['alert'] = 'Item added to cart';
        }
        $check_stmt->close();
    }
    
    // Checkout functionality
    if (isset($_POST['place_order'])) {
        if (!empty($_SESSION['cart'])) {
            // Calculate order totals
            $subtotal = 0;
            foreach ($_SESSION['cart'] as $item) {
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
                ) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            
            $order_stmt->bind_param(
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
            
            if ($order_stmt->execute()) {
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
                    $item_stmt->execute();
                }
                
                // Clear cart
                unset($_SESSION['cart']);
                
                // Handle payment (simplified)
                if ($_POST['payment_method'] === 'paymongo') {
                    // In a real app, integrate with PayMongo API here
                    // This is just a simulation
                    $payment_success = true;
                    
                    if ($payment_success) {
                        // Update order status to paid
                        $update_stmt = $conn->prepare("UPDATE orders SET status = 'paid' WHERE order_id = ?");
                        $update_stmt->bind_param("i", $order_id);
                        $update_stmt->execute();
                        $update_stmt->close();
                    }
                }
                
                $_SESSION['alert'] = 'Order placed successfully!';
                header("Location: order_confirmation.php?id=$order_id");
                exit();
            } else {
                $_SESSION['alert'] = 'Error placing order: ' . $conn->error;
            }
            
            $order_stmt->close();
            $item_stmt->close();
        }
    }
    
    // Update cart quantities
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantity'] as $menu_id => $quantity) {
            $quantity = intval($quantity);
            if ($quantity > 0) {
                $_SESSION['cart'][$menu_id]['quantity'] = $quantity;
            } else {
                unset($_SESSION['cart'][$menu_id]);
            }
        }
        $_SESSION['alert'] = 'Cart updated';
    }
    
    // Remove from cart
    if (isset($_POST['remove_from_cart']) && isset($_POST['menu_id'])) {
        $menu_id = intval($_POST['menu_id']);
        if (isset($_SESSION['cart'][$menu_id])) {
            unset($_SESSION['cart'][$menu_id]);
            $_SESSION['alert'] = 'Item removed from cart';
        }
    }

    // Split the orders into recent (first 3) and more
    $recent_orders = array_slice($orders, 0, 3);
    $more_orders = array_slice($orders, 3);
        
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Get cart item count for display
$cart_count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Our Restaurant</title>
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
        
        .navbar-brand img {
            height: 40px;
        }
        
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            margin-bottom: 30px;
        }
        
        .welcome-card {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .menu-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .menu-card:hover {
            transform: translateY(-10px);
        }
        
        .menu-card img {
            height: 200px;
            object-fit: cover;
        }
        
        .menu-card .card-body {
            padding: 20px;
        }
        
        .menu-card .price {
            color: var(--primary-color);
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
        }
        
        .cart-sidebar {
            position: fixed;
            top: 0;
            right: -400px;
            width: 400px;
            height: 100vh;
            background-color: white;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
            transition: right 0.3s;
            z-index: 1050;
            padding: 20px;
            overflow-y: auto;
        }
        
        .cart-sidebar.open {
            right: 0;
        }
        
        .cart-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            display: none;
        }
        
        .cart-overlay.show {
            display: block;
        }
        
        .cart-item-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .order-card {
            border-left: 4px solid var(--primary-color);
            margin-bottom: 15px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        @media (max-width: 768px) {
            .cart-sidebar {
                width: 100%;
            }
        }

        /* Toast notification styles */
.toast {
    min-width: 250px;
    z-index: 1100;
}

/* Cart sidebar transition */
.cart-sidebar {
    transition: right 0.3s ease-out;
}

/* Overlay transition */
.cart-overlay {
    transition: opacity 0.3s ease-out;
    opacity: 0;
    pointer-events: none;
}

.cart-overlay.show {
    opacity: 1;
    pointer-events: all;
}
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top py-3">
        <div class="container">
            <a class="navbar-brand" href="#">
                <span>LOGO</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item mt-2">
                        <a class="nav-link" href="#menu">Menu</a>
                    </li>
                    <li class="nav-item mt-2">
                        <a class="nav-link" href="#orders">My Orders</a>
                    </li>
              
                    <li class="nav-item mt-2">
                        <button class="nav-link btn btn-link position-relative" id="cartButton">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cartCount">
                                0
                            </span>
                        </button>
                    </li>
                    <li class="nav-item dropdown ps-3">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                       
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['first_name'].' '.$user['last_name']); ?>&background=random" class="user-avatar me-1">
                            <?php echo htmlspecialchars($user['first_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="orders.php"><i class="fas fa-history me-2"></i>Order History</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Welcome Back, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
            <p class="lead mb-5">Ready to order your favorite dishes again?</p>
            <a href="#menu" class="btn btn-primary btn-lg px-4 me-2">Order Now</a>
            <a href="#orders" class="btn btn-outline-light btn-lg px-4">View Orders</a>
        </div>
    </section>

    <!-- Welcome Section -->
    <section class="container">
        <div class="welcome-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3>Hello, <?php echo htmlspecialchars($user['first_name']); ?>!</h3>
                    <p class="mb-0">We're glad to see you again. Check out our new menu items or continue with your recent order.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['first_name'].'+'.$user['last_name']); ?>&size=120&background=random" class="user-avatar" style="width: 80px; height: 80px;">
                </div>
            </div>
        </div>
    </section>

    <!-- Menu Section -->
    <section id="menu" class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Our Menu</h2>
        
        <?php
        // Fetch menu categories from database
        $category_stmt = $conn->prepare("SELECT DISTINCT category FROM menus WHERE avail = 'Available'");
        $category_stmt->execute();
        $category_result = $category_stmt->get_result();
        $categories = $category_result->fetch_all(MYSQLI_ASSOC);
        $category_stmt->close();
        ?>
        
        <div class="row">
            <!-- Category Filters -->
            <div class="col-12 mb-4">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-secondary active filter-btn" data-category="all">All</button>
                    <?php foreach ($categories as $cat): ?>
                        <button type="button" class="btn btn-outline-secondary filter-btn" data-category="<?php echo htmlspecialchars($cat['category']); ?>">
                            <?php echo htmlspecialchars($cat['category']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Menu Items -->
            <?php
            // Fetch all available menu items
            $menu_stmt = $conn->prepare("
                SELECT menu_id, name, description, price, image_url, category 
                FROM menus 
                WHERE avail = 'Available'
                ORDER BY category, name
            ");
            $menu_stmt->execute();
            $menu_result = $menu_stmt->get_result();
            $menu_items = $menu_result->fetch_all(MYSQLI_ASSOC);
            $menu_stmt->close();
            
            if (count($menu_items) > 0): 
                foreach ($menu_items as $item): 
                    $image_url = !empty($item['image_url']) ? $item['image_url'] : 
                        'https://via.placeholder.com/300x200?text=No+Image';
                    ?>
                    <div class="col-md-4 mb-4 menu-item" data-category="<?php echo htmlspecialchars($item['category']); ?>">
                        <div class="menu-card card">
                            <img src="<?php echo htmlspecialchars($image_url); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($item['description']); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="price">₱<?php echo number_format($item['price'], 2); ?></span>
                                    <button class="btn btn-primary btn-sm add-to-cart" 
                                        data-id="<?php echo $item['menu_id']; ?>"
                                        data-name="<?php echo htmlspecialchars($item['name']); ?>"
                                        data-price="<?php echo $item['price']; ?>"
                                        data-image="<?php echo htmlspecialchars($image_url); ?>">
                                        Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; 
            else: ?>
                <div class="col-12 text-center py-4">
                    <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No menu items available at the moment</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
    <!-- Recent Orders Section -->
    <section id="orders" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Your Recent Orders</h2>
            
            <?php if (!empty($orders)): ?>
                <div class="row">
                    <?php foreach ($orders as $order): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card order-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <h5 class="card-title mb-0">Order #<?php echo htmlspecialchars($order['order_id']); ?></h5>
                                        <span class="badge bg-<?php 
                                            switch($order['status']) {
                                                case 'completed': echo 'success'; break;
                                                case 'processing': echo 'warning'; break;
                                                case 'cancelled': echo 'danger'; break;
                                                default: echo 'secondary';
                                            }
                                        ?>">
                                            <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                                        </span>
                                    </div>
                                    <p class="text-muted mb-2">
                                        <?php echo date('M j, Y', strtotime($order['order_date'])); ?>
                                    </p>
                                    <p class="fw-bold mb-0">₱<?php echo number_format($order['total_amount'], 2); ?></p>
                                    <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-outline-primary mt-2">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-3">
                    <a href="orders.php" class="btn btn-primary">View All Orders</a>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                    <p class="text-muted">You haven't placed any orders yet</p>
                    <a href="#menu" class="btn btn-primary">Order Now</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

 <!-- Shopping Cart Sidebar -->
<div class="cart-sidebar" id="cartSidebar">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Your Order</h4>
        <button class="btn-close" id="closeCart"></button>
    </div>

    <div id="cartItems">
        <!-- Cart items will be added here -->
        <div class="text-center py-4">
            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
            <p class="text-muted">Your cart is empty</p>
        </div>
    </div>

    <div class="cart-summary mt-4 pt-4 border-top">
        <div class="d-flex justify-content-between mb-2">
            <span>Subtotal:</span>
            <span id="cartSubtotal">₱0.00</span>
        </div>
        <div class="d-flex justify-content-between mb-2">
            <span>Delivery Fee:</span>
            <span id="cartDelivery">₱50.00</span>
        </div>
        <div class="d-flex justify-content-between mb-3">
            <span>Tax:</span>
            <span id="cartTax">₱0.00</span>
        </div>
        <div class="d-flex justify-content-between fw-bold fs-5">
            <span>Total:</span>
            <span id="cartTotal">₱0.00</span>
        </div>

        <!-- Hidden Checkout Form -->
        <form id="checkoutForm" action="checkout_form.php" method="POST">
            <!-- Hidden input will store the cart data -->
            <input type="hidden" name="cart_data" id="cartDataInput">
            <button type="submit" class="btn btn-primary w-100 mt-4" id="checkoutBtn" disabled>
                Proceed to Checkout
            </button>
        </form>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkoutForm = document.getElementById('checkoutForm');
            const cartDataInput = document.getElementById('cartDataInput');
            
            // When form is submitted
            checkoutForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get cart data from session or wherever it's stored
                const cartData = <?php echo json_encode($_SESSION['cart'] ?? []); ?>;
                
                // Set the cart data in the hidden input
                cartDataInput.value = JSON.stringify(cartData);
                
                // Submit the form
                this.submit();
            });
        });
        </script>

        
    </div>
</div>

<div class="cart-overlay" id="cartOverlay"></div>


    <!-- Bootstrap JS Bundle with Popper -->
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
    // Category filtering functionality
    document.querySelectorAll('.filter-btn').forEach(button => {
        button.addEventListener('click', function() {
            // Update active button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            
            // Filter items
            const category = this.dataset.category;
            document.querySelectorAll('.menu-item').forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
    </script>
<script>
let cart = JSON.parse(localStorage.getItem('cart')) || [];

const cartButton = document.getElementById('cartButton');
const cartSidebar = document.getElementById('cartSidebar');
const cartOverlay = document.getElementById('cartOverlay');
const closeCart = document.getElementById('closeCart');
const cartItems = document.getElementById('cartItems');
const cartCount = document.getElementById('cartCount');
const cartSubtotal = document.getElementById('cartSubtotal');
const cartTotal = document.getElementById('cartTotal');
const checkoutBtn = document.getElementById('checkoutBtn');

// Toggle cart
function toggleCart(show = true) {
    if (show) {
        cartSidebar.classList.add('open');
        cartOverlay.classList.add('show');
    } else {
        cartSidebar.classList.remove('open');
        cartOverlay.classList.remove('show');
    }
    updateCart();
}

cartButton.addEventListener('click', () => toggleCart(true));
closeCart.addEventListener('click', () => toggleCart(false));
cartOverlay.addEventListener('click', () => toggleCart(false));

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('add-to-cart') || e.target.closest('.add-to-cart')) {
        const button = e.target.classList.contains('add-to-cart') ? e.target : e.target.closest('.add-to-cart');
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');
        const price = parseFloat(button.getAttribute('data-price'));
        const image = button.getAttribute('data-image');

        const existingItem = cart.find(item => item.id === id);
        if (existingItem) {
            existingItem.quantity++;
        } else {
            cart.push({ id, name, price, image, quantity: 1 });
        }

        localStorage.setItem('cart', JSON.stringify(cart));
        toggleCart(true);
        showToast(name + " was added to your cart");
    }
});

function showToast(message) {
    const notification = document.createElement('div');
    notification.className = 'position-fixed bottom-0 end-0 p-3';
    notification.innerHTML = `
        <div class="toast show" role="alert">
            <div class="toast-header bg-success text-white">
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">${message}</div>
        </div>`;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

function updateCart() {
    cartCount.textContent = cart.reduce((total, item) => total + item.quantity, 0);
    if (cart.length === 0) {
        cartItems.innerHTML = `<div class="text-center py-4"><p class="text-muted">Your cart is empty</p></div>`;
        checkoutBtn.disabled = true;
        return;
    }

    let itemsHTML = '';
    let subtotal = 0;

    cart.forEach(item => {
        subtotal += item.price * item.quantity;
        itemsHTML += `
            <div class="cart-item mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <img src="${item.image}" class="cart-item-img me-3" alt="${item.name}">
                        <div>
                            <h6 class="mb-1">${item.name}</h6>
                            <p class="mb-0 text-muted">₱${item.price.toFixed(2)}</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <button class="btn btn-sm btn-outline-secondary decrease-quantity" data-id="${item.id}">-</button>
                        <span class="mx-2">${item.quantity}</span>
                        <button class="btn btn-sm btn-outline-secondary increase-quantity" data-id="${item.id}">+</button>
                        <button class="btn btn-sm btn-danger ms-2 remove-item" data-id="${item.id}"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>`;
    });

    cartItems.innerHTML = itemsHTML;
    const tax = subtotal * 0.12;
    const total = subtotal + 50 + tax;

    cartSubtotal.textContent = `₱${subtotal.toFixed(2)}`;
    document.getElementById('cartTax').textContent = `₱${tax.toFixed(2)}`;
    cartTotal.textContent = `₱${total.toFixed(2)}`;
    checkoutBtn.disabled = false;

    document.querySelectorAll('.decrease-quantity').forEach(button => {
        button.addEventListener('click', () => {
            const id = button.getAttribute('data-id');
            const item = cart.find(i => i.id === id);
            if (item.quantity > 1) {
                item.quantity--;
            } else {
                cart = cart.filter(i => i.id !== id);
            }
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCart();
        });
    });

    document.querySelectorAll('.increase-quantity').forEach(button => {
        button.addEventListener('click', () => {
            const id = button.getAttribute('data-id');
            const item = cart.find(i => i.id === id);
            item.quantity++;
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCart();
        });
    });

    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', () => {
            const id = button.getAttribute('data-id');
            cart = cart.filter(i => i.id !== id);
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCart();
        });
    });
}

// Upload cart to server on page load
window.addEventListener('DOMContentLoaded', () => {
    if (cart.length > 0) {
        fetch('api/cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'sync', cart: cart })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                console.log('Cart synced with database.');
            } else {
                console.error('Failed to sync cart:', data.message);
            }
        })
        .catch(err => console.error('Error syncing cart:', err));
    }
});

updateCart();

// --- SYNC CART TO SESSION BEFORE CHECKOUT ---
checkoutBtn.addEventListener('click', function (e) {
    e.preventDefault(); // Prevent default form submit
    // Sync localStorage cart to PHP session via AJAX
    fetch('api/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'sync', cart: cart })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // After sync, submit the form to checkout_form.php
            document.getElementById('cartDataInput').value = JSON.stringify(cart);
            document.getElementById('checkoutForm').submit();
        } else {
            alert('Failed to sync cart. Please try again.');
        }
    })
    .catch(() => alert('Error syncing cart. Please try again.'));
});
</script>

</body>
</html>
<?php $conn->close(); ?>