<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Restaurant Admin - Menu Management</title>
    <!-- Bootstrap CSS -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <!-- Font Awesome -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
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
        width: 280px;
        min-height: 100vh;
        background-color: var(--primary-color);
        position: fixed;
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

      .product-img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 5px;
      }

      .badge {
        font-weight: 500;
        padding: 5px 10px;
      }

      .form-switch .form-check-input {
        width: 3em;
        height: 1.5em;
      }
    </style>
  </head>
  <body>
    <!-- Sidebar -->
    <?php include 'admin_sidebar.php'; ?>


    <!-- Main Content -->
    <div class="main-content">
      <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"
      >
        <h1 class="h2"><i class="fas fa-utensils me-2"></i>Menu Management</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
          <button
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#addMenuModal"
          >
            <i class="fas fa-plus me-2"></i>Add New Item
          </button>
        </div>
      </div>

      <!-- Menu Items Table -->
      <div class="card">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">Menu Items</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th width="80px">Image</th>
                  <th>Name</th>
                  <th>Category</th>
                  <th>Price</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="menuItemsTable">
                <!-- Menu items will be loaded here -->
              </tbody>
            </table>
          </div>
          <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
              <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1">Previous</a>
              </li>
              <li class="page-item active">
                <a class="page-link" href="#">1</a>
              </li>
              <li class="page-item"><a class="page-link" href="#">2</a></li>
              <li class="page-item"><a class="page-link" href="#">3</a></li>
              <li class="page-item">
                <a class="page-link" href="#">Next</a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>

    <!-- Add Menu Item Modal -->
    <div
      class="modal fade"
      id="addMenuModal"
      tabindex="-1"
      aria-labelledby="addMenuModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="addMenuModalLabel">
              <i class="fas fa-plus-circle me-2"></i>Add New Menu Item
            </h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body">
            <form id="addMenuItemForm">
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="itemName" class="form-label">Item Name *</label>
                  <input
                    type="text"
                    class="form-control"
                    id="itemName"
                    required
                  />
                </div>
                <div class="col-md-6">
                  <label for="itemCategory" class="form-label"
                    >Category *</label
                  >
                  <select class="form-select" id="itemCategory" required>
                    <option value="" selected disabled>Select category</option>
                    <option value="appetizers">Appetizers</option>
                    <option value="main-courses">Main Courses</option>
                    <option value="desserts">Desserts</option>
                    <option value="beverages">Beverages</option>
                  </select>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="itemPrice" class="form-label">Price *</label>
                  <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input
                      type="number"
                      step="0.01"
                      class="form-control"
                      id="itemPrice"
                      required
                    />
                  </div>
                </div>
                <div class="col-md-6">
                  <label for="itemCost" class="form-label">Cost</label>
                  <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input
                      type="number"
                      step="0.01"
                      class="form-control"
                      id="itemCost"
                    />
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <label for="itemImage" class="form-label">Item Image</label>
                <input
                  type="file"
                  class="form-control"
                  id="itemImage"
                  accept="image/*"
                />
                <div class="form-text">Recommended size: 500x500 pixels</div>
              </div>
              <div class="mb-3">
                <label for="itemDescription" class="form-label"
                  >Description</label
                >
                <textarea
                  class="form-control"
                  id="itemDescription"
                  rows="3"
                ></textarea>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Availability</label>
                  <div class="form-check form-switch">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      id="itemAvailable"
                      checked
                    />
                    <label class="form-check-label" for="itemAvailable"
                      >Available</label
                    >
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="itemIngredients" class="form-label"
                    >Ingredients</label
                  >
                  <input
                    type="text"
                    class="form-control"
                    id="itemIngredients"
                    placeholder="Comma separated list"
                  />
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Cancel
            </button>
            <button type="button" class="btn btn-primary" id="saveMenuItemBtn">
              Save Item
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Menu Item Modal -->
    <div
      class="modal fade"
      id="editMenuModal"
      tabindex="-1"
      aria-labelledby="editMenuModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-warning text-dark">
            <h5 class="modal-title" id="editMenuModalLabel">
              <i class="fas fa-edit me-2"></i>Edit Menu Item
            </h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body">
            <form id="editMenuItemForm">
              <input type="hidden" id="editItemId" />
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="editItemName" class="form-label"
                    >Item Name *</label
                  >
                  <input
                    type="text"
                    class="form-control"
                    id="editItemName"
                    required
                  />
                </div>
                <div class="col-md-6">
                  <label for="editItemCategory" class="form-label"
                    >Category *</label
                  >
                  <select class="form-select" id="editItemCategory" required>
                    <option value="appetizers">Appetizers</option>
                    <option value="main-courses">Main Courses</option>
                    <option value="desserts">Desserts</option>
                    <option value="beverages">Beverages</option>
                  </select>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="editItemPrice" class="form-label">Price *</label>
                  <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input
                      type="number"
                      step="0.01"
                      class="form-control"
                      id="editItemPrice"
                      required
                    />
                  </div>
                </div>
                <div class="col-md-6">
                  <label for="editItemCost" class="form-label">Cost</label>
                  <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input
                      type="number"
                      step="0.01"
                      class="form-control"
                      id="editItemCost"
                    />
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <label for="editItemImage" class="form-label">Item Image</label>
                <input
                  type="file"
                  class="form-control"
                  id="editItemImage"
                  accept="image/*"
                />
                <div class="form-text">Leave empty to keep current image</div>
                <div class="mt-2" id="currentImageContainer">
                  <img src="" id="currentItemImage" class="product-img" />
                </div>
              </div>
              <div class="mb-3">
                <label for="editItemDescription" class="form-label"
                  >Description</label
                >
                <textarea
                  class="form-control"
                  id="editItemDescription"
                  rows="3"
                ></textarea>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Availability</label>
                  <div class="form-check form-switch">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      id="editItemAvailable"
                    />
                    <label class="form-check-label" for="editItemAvailable"
                      >Available</label
                    >
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="editItemIngredients" class="form-label"
                    >Ingredients</label
                  >
                  <input
                    type="text"
                    class="form-control"
                    id="editItemIngredients"
                    placeholder="Comma separated list"
                  />
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Cancel
            </button>
            <button
              type="button"
              class="btn btn-primary"
              id="updateMenuItemBtn"
            >
              Update Item
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      class="modal fade"
      id="deleteMenuModal"
      tabindex="-1"
      aria-labelledby="deleteMenuModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="deleteMenuModalLabel">
              <i class="fas fa-trash-alt me-2"></i>Confirm Deletion
            </h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body">
            <p>
              Are you sure you want to delete
              <strong id="itemToDeleteName"></strong>?
            </p>
            <p class="text-danger">This action cannot be undone.</p>
            <input type="hidden" id="itemToDeleteId" />
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Cancel
            </button>
            <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
      // Sample menu items data (in a real app, this would come from an API)
      let menuItems = [
        {
          id: 1,
          name: "Margherita Pizza",
          category: "main-courses",
          price: 12.99,
          cost: 4.5,
          image:
            "https://images.unsplash.com/photo-1574071318508-1cdbab80d002?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80",
          description: "Classic pizza with tomato sauce, mozzarella, and basil",
          ingredients: "Dough, Tomato sauce, Mozzarella, Basil",
          available: true,
        },
        {
          id: 2,
          name: "Caesar Salad",
          category: "appetizers",
          price: 8.99,
          cost: 3.2,
          image:
            "https://images.unsplash.com/photo-1546793665-c74683f339c1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80",
          description:
            "Fresh romaine lettuce with Caesar dressing, croutons and parmesan",
          ingredients: "Romaine lettuce, Croutons, Parmesan, Caesar dressing",
          available: true,
        },
        {
          id: 3,
          name: "Chocolate Lava Cake",
          category: "desserts",
          price: 6.5,
          cost: 2.1,
          image:
            "https://images.unsplash.com/photo-1564355808539-22fda35bed7e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80",
          description:
            "Warm chocolate cake with a molten center, served with vanilla ice cream",
          ingredients: "Chocolate, Flour, Eggs, Sugar, Butter",
          available: false,
        },
      ];

      // DOM elements
      const menuItemsTable = document.getElementById("menuItemsTable");
      const saveMenuItemBtn = document.getElementById("saveMenuItemBtn");
      const updateMenuItemBtn = document.getElementById("updateMenuItemBtn");
      const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
      const addMenuItemForm = document.getElementById("addMenuItemForm");
      const editMenuItemForm = document.getElementById("editMenuItemForm");

      // Initialize the page
      document.addEventListener("DOMContentLoaded", function () {
        renderMenuItems();

        // Event listeners
        saveMenuItemBtn.addEventListener("click", addMenuItem);
        updateMenuItemBtn.addEventListener("click", updateMenuItem);
        confirmDeleteBtn.addEventListener("click", deleteMenuItem);
      });

      // Render menu items table
      function renderMenuItems() {
        menuItemsTable.innerHTML = "";

        if (menuItems.length === 0) {
          menuItemsTable.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4">No menu items found</td>
                    </tr>
                `;
          return;
        }

        menuItems.forEach((item) => {
          const row = document.createElement("tr");
          row.innerHTML = `
                    <td><img src="${item.image}" alt="${
            item.name
          }" class="product-img"></td>
                    <td>${item.name}</td>
                    <td>${formatCategory(item.category)}</td>
                    <td>$${item.price.toFixed(2)}</td>
                    <td>
                        <span class="badge ${
                          item.available ? "bg-success" : "bg-secondary"
                        }">
                            ${item.available ? "Available" : "Out of Stock"}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-warning me-1 edit-btn" data-id="${
                          item.id
                        }">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${
                          item.id
                        }">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
          menuItemsTable.appendChild(row);
        });

        // Add event listeners to edit and delete buttons
        document.querySelectorAll(".edit-btn").forEach((btn) => {
          btn.addEventListener("click", function () {
            const itemId = parseInt(this.getAttribute("data-id"));
            openEditModal(itemId);
          });
        });

        document.querySelectorAll(".delete-btn").forEach((btn) => {
          btn.addEventListener("click", function () {
            const itemId = parseInt(this.getAttribute("data-id"));
            openDeleteModal(itemId);
          });
        });
      }

      // Format category for display
      function formatCategory(category) {
        const categories = {
          appetizers: "Appetizers",
          "main-courses": "Main Courses",
          desserts: "Desserts",
          beverages: "Beverages",
        };
        return categories[category] || category;
      }

      // Add new menu item
      function addMenuItem() {
        if (!addMenuItemForm.checkValidity()) {
          addMenuItemForm.reportValidity();
          return;
        }

        const newItem = {
          id:
            menuItems.length > 0
              ? Math.max(...menuItems.map((item) => item.id)) + 1
              : 1,
          name: document.getElementById("itemName").value,
          category: document.getElementById("itemCategory").value,
          price: parseFloat(document.getElementById("itemPrice").value),
          cost: parseFloat(document.getElementById("itemCost").value) || 0,
          image: "https://via.placeholder.com/500", // Default image
          description: document.getElementById("itemDescription").value,
          ingredients: document.getElementById("itemIngredients").value,
          available: document.getElementById("itemAvailable").checked,
        };

        menuItems.push(newItem);
        renderMenuItems();

        // Reset form and close modal
        addMenuItemForm.reset();
        bootstrap.Modal.getInstance(
          document.getElementById("addMenuModal")
        ).hide();

        alert("Menu item added successfully!");
      }

      // Open edit modal with menu item data
      function openEditModal(itemId) {
        const item = menuItems.find((item) => item.id === itemId);
        if (!item) return;

        document.getElementById("editItemId").value = item.id;
        document.getElementById("editItemName").value = item.name;
        document.getElementById("editItemCategory").value = item.category;
        document.getElementById("editItemPrice").value = item.price;
        document.getElementById("editItemCost").value = item.cost;
        document.getElementById("editItemDescription").value = item.description;
        document.getElementById("editItemIngredients").value = item.ingredients;
        document.getElementById("editItemAvailable").checked = item.available;
        document.getElementById("currentItemImage").src = item.image;

        const editModal = new bootstrap.Modal(
          document.getElementById("editMenuModal")
        );
        editModal.show();
      }

      // Update existing menu item
      function updateMenuItem() {
        if (!editMenuItemForm.checkValidity()) {
          editMenuItemForm.reportValidity();
          return;
        }

        const itemId = parseInt(document.getElementById("editItemId").value);
        const itemIndex = menuItems.findIndex((item) => item.id === itemId);

        if (itemIndex === -1) return;

        menuItems[itemIndex] = {
          ...menuItems[itemIndex],
          name: document.getElementById("editItemName").value,
          category: document.getElementById("editItemCategory").value,
          price: parseFloat(document.getElementById("editItemPrice").value),
          cost: parseFloat(document.getElementById("editItemCost").value) || 0,
          description: document.getElementById("editItemDescription").value,
          ingredients: document.getElementById("editItemIngredients").value,
          available: document.getElementById("editItemAvailable").checked,
        };

        renderMenuItems();
        bootstrap.Modal.getInstance(
          document.getElementById("editMenuModal")
        ).hide();

        alert("Menu item updated successfully!");
      }

      // Open delete confirmation modal
      function openDeleteModal(itemId) {
        const item = menuItems.find((item) => item.id === itemId);
        if (!item) return;

        document.getElementById("itemToDeleteId").value = item.id;
        document.getElementById("itemToDeleteName").textContent = item.name;

        const deleteModal = new bootstrap.Modal(
          document.getElementById("deleteMenuModal")
        );
        deleteModal.show();
      }

      // Delete menu item
      function deleteMenuItem() {
        const itemId = parseInt(
          document.getElementById("itemToDeleteId").value
        );
        const itemIndex = menuItems.findIndex((item) => item.id === itemId);

        if (itemIndex === -1) return;

        menuItems.splice(itemIndex, 1);
        renderMenuItems();
        bootstrap.Modal.getInstance(
          document.getElementById("deleteMenuModal")
        ).hide();

        alert("Menu item deleted successfully!");
      }
    </script>
  </body>
</html>
