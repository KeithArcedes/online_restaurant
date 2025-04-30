<?php
require_once 'dbcon.php';
session_start();

// Fetch all available menu items
$query = "SELECT * FROM menus WHERE avail = 'Available' ORDER BY category";
$result = $conn->query($query);
$menu_items = $result->fetch_all(MYSQLI_ASSOC);

// Group menu items by category
$menu_by_category = [];
foreach ($menu_items as $item) {
    $menu_by_category[$item['category']][] = $item;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Online Ordering</title>
    <!-- Bootstrap CSS -->
    <link href="assets/styles/styles.css" rel="stylesheet">
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
        
        .auth-modal .modal-content {
            border-radius: 15px;
            overflow: hidden;
        }
        
        .auth-modal .nav-tabs {
            border: none;
        }
        
        .auth-modal .nav-tabs .nav-link {
            border: none;
            color: #777;
            font-weight: 500;
        }
        
        .auth-modal .nav-tabs .nav-link.active {
            color: var(--primary-color);
            border-bottom: 3px solid var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .cart-sidebar {
                width: 100%;
            }
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
                <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#authModal">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#authModal">Register</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#menu">Menu</a>
                    </li>
                  
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Delicious Food Delivered To Your Door</h1>
            <p class="lead mb-5">Foodie Paradise to Satisfy Your Every Cravings</p>
            <a href="#menu" class="btn btn-primary btn-lg px-4 me-2">View Menu</a>
           
        </div>
    </section>

    <!-- Menu Section -->
    <section id="menu" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Our Menu</h2>
            
            <!-- Category Filters -->
            <div class="col-12 mb-4">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary active filter-btn" data-category="all">All</button>
                    <?php
                    $categories = array();
                    foreach ($menu_items as $item) {
                        $categories[$item['category']] = ucfirst($item['category']);
                    }
                    foreach ($categories as $key => $label): ?>
                        <button type="button" class="btn btn-outline-primary filter-btn" data-category="<?php echo htmlspecialchars($key); ?>">
                            <?php echo htmlspecialchars($label); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="row">
                <?php foreach ($menu_items as $item): ?>
                    <div class="col-md-4 mb-4 menu-item" data-category="<?= htmlspecialchars($item['category']) ?>">
                        <div class="menu-card card">
                            <img src="<?= str_replace('../', '', htmlspecialchars($item['image_url'])) ?>" 
                                 class="card-img-top menu-image" 
                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                 onerror="this.src='assets/imgs/default-food.jpg'">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($item['name']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($item['description']) ?></p>
                           
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const menuItems = document.querySelectorAll('.menu-item');

    filterButtons.forEach(button => {
        button.addEventListener('click', function () {
            const selectedCategory = this.dataset.category;

            // Toggle active button class
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            // Filter menu items
            menuItems.forEach(item => {
                const itemCategory = item.dataset.category;
                if (selectedCategory === 'all' || itemCategory === selectedCategory) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});
</script>

    <!-- Authentication scripts -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const loginError = document.getElementById('loginError');
        const registerError = document.getElementById('registerError');

        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = data.redirect;
                } else {
                    loginError.style.display = 'block';
                    loginError.textContent = data.message;
                }
            })
            .catch(error => {
                loginError.style.display = 'block';
                loginError.textContent = 'An error occurred. Please try again.';
            });
        });

        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            if (formData.get('password') !== formData.get('confirmPassword')) {
                registerError.style.display = 'block';
                registerError.textContent = 'Passwords do not match';
                return;
            }

            fetch('auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Show success message and switch to login tab
                    registerError.style.display = 'block';
                    registerError.className = 'alert alert-success';
                    registerError.textContent = 'Registration successful! Please login.';
                    document.getElementById('login-tab').click();
                    registerForm.reset();
                } else {
                    registerError.style.display = 'block';
                    registerError.className = 'alert alert-danger';
                    registerError.textContent = data.message;
                }
            })
            .catch(error => {
                registerError.style.display = 'block';
                registerError.textContent = 'An error occurred. Please try again.';
            });
        });
    });
    </script>

    <!-- Bootstrap JS Bundle with Popper -->
   <script src="assets/js/bootstrap.bundle.min.js"></script>
   

    <!-- Authentication Modal -->
    <div class="modal fade auth-modal" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs nav-fill mb-4" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="login-tab" data-bs-toggle="tab" href="#login" role="tab">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="register-tab" data-bs-toggle="tab" href="#register" role="tab">Register</a>
                        </li>
                    </ul>
                    
                    <div class="tab-content">
                        <!-- Login Tab -->
                        <div class="tab-pane fade show active" id="login" role="tabpanel">
                            <form id="loginForm" class="needs-validation" novalidate>
                                <input type="hidden" name="action" value="login">
                                <div class="mb-3">
                                    <label for="loginEmail" class="form-label">Email address</label>
                                    <input type="email" class="form-control" id="loginEmail" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="loginPassword" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="loginPassword" name="password" required>
                                </div>
                                <div class="alert alert-danger" id="loginError" style="display: none;"></div>
                                <button type="submit" class="btn btn-primary w-100">Login</button>
                            </form>
                        </div>
                        
                        <!-- Register Tab -->
                        <div class="tab-pane fade" id="register" role="tabpanel">
                            <form id="registerForm" class="needs-validation" novalidate>
                                <input type="hidden" name="action" value="register">
                                <div class="mb-3">
                                    <label for="firstName" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="firstName" name="firstName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="lastName" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="lastName" name="lastName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="registerEmail" class="form-label">Email address</label>
                                    <input type="email" class="form-control" id="registerEmail" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="registerPassword" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="registerPassword" name="password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                                </div>
                                <div class="alert alert-danger" id="registerError" style="display: none;"></div>
                                <button type="submit" class="btn btn-primary w-100">Register</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>