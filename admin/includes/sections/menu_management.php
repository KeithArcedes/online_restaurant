<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-utensils me-2"></i>Menu Management</h5>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addMenuModal">
            <i class="fas fa-plus me-1"></i> Add New Item
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT * FROM menus ORDER BY name ASC";
                    $result = $conn->query($query);
                    
                    while ($item = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td>
                        <img src="<?= htmlspecialchars($item['image_url']) ?>" ... 

                                 class="product-img" 
                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                 onerror="this.src='../assets/imgs/default-food.jpg'">
                        </td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($item['category'])); ?></td>
                        <td>Php<?php echo number_format($item['price'], 2); ?></td>
                        <td>
                            <span class="badge <?php echo $item['avail'] === 'Available' ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $item['avail'] === 'Available' ? 'Available' : 'Not Available'; ?>
                            </span>
                        </td>
                        <td>
                        <button class="btn btn-warning btn-sm edit-btn"
                                        data-id="<?php echo $item['menu_id']; ?>"
                                        data-name="<?php echo htmlspecialchars($item['name']); ?>"
                                        data-category="<?php echo $item['category']; ?>"
                                        data-price="<?php echo $item['price']; ?>"
                                        data-avail="<?php echo $item['avail']; ?>"
                                        data-description="<?php echo htmlspecialchars($item['description']); ?>"
                                        data-image="<?php echo $item['image_url']; ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editMenuModal">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <button class="btn btn-sm btn-danger delete-item" 
                                            data-id="<?php echo $item['menu_id']; ?>"
                                            data-name="<?php echo htmlspecialchars($item['name']); ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteMenuModal">
                                        <i class="fas fa-trash"></i>
                                    </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>