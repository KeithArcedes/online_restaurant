<div class="modal fade" id="addMenuModal" tabindex="-1" aria-labelledby="addMenuModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="addMenuItemForm" action="../api/menu.php?action=add" method="POST" enctype="multipart/form-data">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="addMenuModalLabel">
            <i class="fas fa-plus-circle me-2"></i>Add New Menu Item
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="menuItemName" class="form-label">Item Name *</label>
              <input type="text" class="form-control" id="menuItemName" name="name" required>
            </div>
            <div class="col-md-6">
              <label for="menuItemCategory" class="form-label">Category *</label>
              <select class="form-select" id="menuItemCategory" name="category" required>
                <option value="" selected disabled>Select category</option>
                <option value="sizzling">Sizzling</option>
                <option value="beverage">Beverage</option>
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="menuItemPrice" class="form-label">Price *</label>
              <div class="input-group">
                <span class="input-group-text">Php</span>
                <input type="number" step="0.01" class="form-control" id="menuItemPrice" name="price" required>
              </div>
            </div>
            <div class="col-md-6">
              <label for="menuItemAvail" class="form-label">Availability *</label>
              <select class="form-select" id="menuItemAvail" name="avail" required>
                <option value="" selected disabled>Select availability</option>
                <option value="Available">Available</option>
                <option value="NotAvailable">Not Available</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label for="menuItemImage" class="form-label">Item Image</label>
            <input type="file" class="form-control" id="menuItemImage" name="image" accept="image/*">
            <div class="form-text">Recommended size: 500x500 pixels</div>
          </div>
          <div class="mb-3">
            <label for="menuItemDescription" class="form-label">Description</label>
            <textarea class="form-control" id="menuItemDescription" name="description" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Add Item</button>
        </div>
      </form>
    </div>
  </div>
</div>



