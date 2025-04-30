<?php
require '../dbcon.php';

// Fetch orders from the database
$query = "SELECT o.order_id, o.order_date, o.total_amount, o.status, c.first_name, c.last_name 
          FROM orders o
          JOIN customers c ON o.customer_id = c.customer_id
          ORDER BY o.order_date DESC";
$result = $conn->query($query);
$orders = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Admin - Orders</title>
    <!-- Bootstrap CSS -->
    <link href="../assets/styles/styles.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<style>
      :root {
        --primary-color: #2c3e50;
        --secondary-color: #34495e;
        --accent-color: #e74c3c;
        --pending-color: #f39c12;
        --completed-color: #27ae60;
        --cancelled-color: #e74c3c;
        --sidebar-width: 280px;
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
        margin-left: 280px;
        padding: 20px;
      }

      .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
      }

      .order-status-badge {
        font-weight: 500;
        padding: 5px 10px;
        border-radius: 20px;
      }

      .status-pending {
        background-color: var(--pending-color);
        color: white;
      }

      .status-completed {
        background-color: var(--completed-color);
        color: white;
      }

      .status-cancelled {
        background-color: var(--cancelled-color);
        color: white;
      }

      .order-item-img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 5px;
      }

      .filter-btn.active {
        background-color: var(--primary-color);
        color: white;
      }

      .order-details-card {
        border-left: 4px solid var(--primary-color);
      }
    </style>
<body>
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Orders Management</h1>
        </div>

        <!-- Orders List -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Orders</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer Name</th>
                                <th>Order Date</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($orders)): ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($order['order_id']) ?></td>
                                        <td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                                        <td><?= date('M j, Y', strtotime($order['order_date'])) ?></td>
                                        <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                switch ($order['status']) {
                                                    case 'completed': echo 'success'; break;
                                                    case 'pending': echo 'warning'; break;
                                                    case 'cancelled': echo 'danger'; break;
                                                    default: echo 'secondary';
                                                }
                                            ?>">
                                                <?= ucfirst(htmlspecialchars($order['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary view-order-btn" data-order-id="<?= $order['order_id'] ?>">
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No orders found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add modal for order actions -->
<div class="modal fade" id="orderActionModal" tabindex="-1" aria-labelledby="orderActionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderActionModalLabel">Order Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>What action would you like to take for this order?</p>
                <input type="hidden" id="orderIdInput">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" data-status="confirmed">Confirm</button>
                <button type="button" class="btn btn-danger" data-status="cancelled">Cancel</button>
                <button type="button" class="btn btn-primary" data-status="pending">Pending</button>
                <button type="button" class="btn btn-success" data-status="completed">Complete</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const orderActionModal = new bootstrap.Modal(document.getElementById('orderActionModal'));
        const orderIdInput = document.getElementById('orderIdInput');

        // View button click handler
        document.querySelectorAll('.view-order-btn').forEach(button => {
            button.addEventListener('click', function () {
                const orderId = this.dataset.orderId;
                orderIdInput.value = orderId;
                orderActionModal.show();
            });
        });

        // Status update button click handlers
        document.querySelectorAll('.modal-footer button[data-status]').forEach(button => {
            button.addEventListener('click', async function() {
                const status = this.getAttribute('data-status');
                const orderId = orderIdInput.value;
                
                try {
                    const response = await fetch('../api/orders.php?action=update_status', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            order_id: orderId,
                            status: status
                        })
                    });

                    const result = await response.json();
                    
                    if (result.success) {
                        orderActionModal.hide();
                        alert('Order status updated successfully!');
                        location.reload();
                    } else {
                        throw new Error(result.message || 'Failed to update order status');
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            });
        });
    });
    </script>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
