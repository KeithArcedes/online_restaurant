<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-header text-center py-4">
    <h4 class="text-white">RESTAURANT ADMIN</h4>
  </div>
  <ul class="nav flex-column">
    <li class="nav-item">
      <a class="nav-link <?= ($current_page == 'admin_dashboard.php') ? 'active' : '' ?>" href="admin_dashboard.php">
        <i class="fas fa-tachometer-alt"></i>Dashboard
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($current_page == 'admin_orders.php') ? 'active' : '' ?>" href="admin_orders.php">
        <i class="fas fa-shopping-cart"></i>Orders
      </a>
    </li>
    <li class="nav-item mt-3">
      <a class="nav-link text-danger" href="../index.php">
        <i class="fas fa-sign-out-alt"></i>Logout
      </a>
    </li>
  </ul>
</div>
