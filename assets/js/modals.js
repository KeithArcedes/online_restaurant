$(document).ready(function() {
    // Clear forms when modals are closed
    $('#addMenuModal, #addMemberModal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
    });

    // Menu Item Modals
    $('#saveMenuItem').click(function() {
        const formData = new FormData();
        formData.append('name', $('#menuItemName').val());
        formData.append('category', $('#menuItemCategory').val());
        formData.append('price', $('#menuItemPrice').val());
        formData.append('description', $('#menuItemDescription').val());
        formData.append('avail', $('#menuItemvAvail').val());
        
        $.ajax({
            url: 'api/menu.php?action=add',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.success) {
                    $('#addMenuModal').modal('hide');
                    location.reload();
                } else {
                    alert(response.message || 'Error adding menu item');
                }
            },
            error: function() {
                alert('Error adding menu item. Please try again.');
            }
        });
    });


    // Edit Menu Item
    $('#editMenuModal').click(function() {
        const formData = new FormData();
        formData.append('id', $('#editMenuItemId').val());
        formData.append('name', $('#editMenuItemName').val());
        formData.append('category', $('#editMenuItemCategory').val());
        formData.append('price', $('#editMenuItemPrice').val());
        formData.append('description', $('#editMenuItemDescription').val());
        formData.append('avail', $('#editMenuItemAvail').val());
        formData.append('image', $('#editMenuItemImage')[0].files[0]);
        
        $.ajax({
            url: 'api/menu.php?action=update',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.success) {
                    $('#editMenuModal').modal('hide');
                    location.reload();
                } else {
                    alert(response.message || 'Error updating menu item');
                }
            }
        });
    });

    // Delete Menu Item
    $('#deleteMenuModal .btn-danger').click(function() {
        const id = $('#deleteMenuModal').data('id');
        
        $.post('api/menu.php?action=delete', { id: id }, function(response) {
            if(response.success) {
                $('#deleteMenuModal').modal('hide');
                location.reload();
            } else {
                alert(response.message || 'Error deleting menu item');
            }
        }, 'json');
    });

    // Populate edit modal with data
    $('.edit-item').click(function() {
        const id = $(this).data('id');
        $.get('api/menu.php?action=get&id=' + id, function(data) {
            $('#editMenuItemId').val(data.id);
            $('#editMenuItemName').val(data.name);
            $('#editMenuItemCategory').val(data.category);
            $('#editMenuItemPrice').val(data.price);
            $('#editMenuItemDescription').val(data.description);
            $('#editMenuItemAvailable').prop('checked', data.available);
            $('#currentMenuItemImage').attr('src', data.image_url || 'https://via.placeholder.com/150');
            $('#deleteMenuModal').data('id', id);
        }, 'json');
    });

    // Populate delete modal with data
    $('.delete-item').click(function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        $('#deleteMenuModal strong').text(name);
        $('#deleteMenuModal').data('id', id);
    });

});