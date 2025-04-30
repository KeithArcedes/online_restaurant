<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Restaurant Admin Dashboard</title>
<!-- Bootstrap CSS -->
<link href="../assets/styles/styles.css" rel="stylesheet" />
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<style>
    :root {
        --sidebar-width: 280px;
        --primary-color: #2c3e50;
        --secondary-color: #34495e;
        --accent-color: #e74c3c;
    }

    body {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f5f6fa;
    }

    .sidebar {
        width: var(--sidebar-width);
        min-height: 100vh;
        background-color: var(--primary-color);
        position: fixed;
        transition: all 0.3s;
        z-index: 1000;
    }

    .sidebar .nav-link {
        color: rgba(255, 255, 255, 0.8);
        border-left: 4px solid transparent;
        padding: 12px 20px;
        margin: 2px 0;
        transition: all 0.3s;
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
        color: white;
        background-color: rgba(255, 255, 255, 0.1);
        border-left: 4px solid var(--accent-color);
    }

    .sidebar .nav-link i {
        width: 24px;
        text-align: center;
        margin-right: 10px;
    }

    .main-content {
        margin-left: var(--sidebar-width);
        padding: 20px;
        transition: all 0.3s;
    }

    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
        transition: transform 0.3s;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .card-header {
        background-color: white;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        font-weight: 600;
        border-radius: 10px 10px 0 0 !important;
    }

    .table th {
        border-top: none;
        font-weight: 600;
        color: #7f8c8d;
    }

    .badge {
        font-weight: 500;
        padding: 5px 10px;
    }

    .product-img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 5px;
    }

    .member-avatar {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 50%;
    }

    .stats-card {
        border-left: 4px solid var(--accent-color);
    }

    .stats-card .stat-value {
        font-size: 24px;
        font-weight: 700;
    }

    .stats-card .stat-label {
        color: #7f8c8d;
        font-size: 14px;
    }

    @media (max-width: 768px) {
        .sidebar {
            margin-left: -280px;
        }
        .sidebar.active {
            margin-left: 0;
        }
        .main-content {
            margin-left: 0;
        }
    }
</style>