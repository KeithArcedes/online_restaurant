<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="stat-value text-primary">
                    <?php 
                    $query = "SELECT COUNT(*) as count FROM menus";
                    $result = $conn->query($query);
                    echo $result->fetch_assoc()['count'];
                    ?>
                </div>
                <div class="stat-label">Total Menu Items</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="stat-value text-warning">
                    <?php 
                    $query = "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'";
                    $result = $conn->query($query);
                    echo $result->fetch_assoc()['count'];
                    ?>
                </div>
                <div class="stat-label">Pending Orders</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="stat-value text-danger">
                    <?php 
                    $query = "SELECT COUNT(*) as count FROM menus WHERE avail = 'NotAvailable'";
                    $result = $conn->query($query);
                    echo $result->fetch_assoc()['count'];
                    ?>
                </div>
                <div class="stat-label">Not Available</div>
            </div>
        </div>
    </div>
</div>