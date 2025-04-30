<div class="modal fade" id="editMenuModal" tabindex="-1" aria-labelledby="editMenuModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editMenuModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Menu Item
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editMenuItemForm" action="../api/menu.php?action=update" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="editMenuItemId" name="id">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editMenuItemName" class="form-label">Item Name *</label>
                            <input type="text" class="form-control" id="editMenuItemName" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editMenuItemCategory" class="form-label">Category *</label>
                            <select class="form-select" id="editMenuItemCategory" name="category" required>
                                <option value="sizzling">Sizzling</option>
                                <option value="beverage">Beverages</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editMenuItemPrice" class="form-label">Price *</label>
                            <div class="input-group">
                                <span class="input-group-text">Php</span>
                                <input type="number" step="0.01" class="form-control" id="editMenuItemPrice" name="price" required>
                            </div>
                        </div>
                        <div class="col-md-6">
              <label for="editMenuItemAvail" class="form-label">Availability *</label>
              <select class="form-select" id="editMenuItemAvail" name="avail" required>
                <option value="" selected disabled>Select category</option>
                <option value="Available">Available</option>
                <option value="NotAvailable">Not Available</option>
              </select>
            </div>
                    </div>
                    <div class="mb-3">
                        <label for="editMenuItemImage" class="form-label">Item Image</label>
                        <input type="file" class="form-control" id="editMenuItemImage" accept="image/*" name="image">
                        <div class="form-text">Leave empty to keep current image</div>
                        <div class="mt-2">
                            <img src="https://via.placeholder.com/150" id="currentMenuItemImage" class="product-img">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editMenuItemDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editMenuItemDescription" rows="3" name="description"></textarea>
                    </div>
                    <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success" id="updateMenuItem">Save Changes</button>
            </div>
                </form>
            </div>
          
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', function () {
        document.getElementById('editMenuItemId').value = this.dataset.id;
        document.getElementById('editMenuItemName').value = this.dataset.name;
        document.getElementById('editMenuItemCategory').value = this.dataset.category;
        document.getElementById('editMenuItemPrice').value = this.dataset.price;
        document.getElementById('editMenuItemAvail').value = this.dataset.avail;
        document.getElementById('editMenuItemDescription').value = this.dataset.description;
        
        // Set image preview
        const img = document.getElementById('currentMenuItemImage');
        if (img) {
            img.src = this.dataset.image;
        }
        
    });
});
</script>
