<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header('Location: unauthorized.php');
    exit;
}

if (isset($_SESSION['alert'])) {
    echo "<script>alert('" . addslashes($_SESSION['alert']) . "');</script>";
    unset($_SESSION['alert']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Mobile Menu Toggle -->
        <button class="btn btn-primary d-md-none mb-3" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Dashboard Header -->
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Admin Dashboard</h1>
        </div>

        <!-- Stats Cards -->
        <?php include 'includes/sections/stats.php'; ?>

        <!-- Menu Management -->
        <?php include 'includes/sections/menu_management.php'; ?>

        
    </div>

    <!-- Include all modals -->
    <?php include 'includes/modals/menu_add.php'; ?>
    <?php include 'includes/modals/menu_edit.php'; ?>
    <?php include 'includes/modals/menu_delete.php'; ?>


    <!-- Footer with scripts -->
    <?php include 'includes/footer.php'; ?>
</body>
</html>