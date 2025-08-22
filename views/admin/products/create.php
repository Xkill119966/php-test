<?php
$title = 'Add New Product - Admin';
include __DIR__ . '/../../layout/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-plus me-2"></i>Add New Product
                </h4>
            </div>
            
            <div class="card-body">
                <?php if (isset($errors) && !empty($errors)): ?>
                <div class="alert alert-danger">
                    <h6>Please fix the following errors:</h6>
                    <ul class="mb-0">
                        <?php foreach ($errors as $field => $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form id="productForm" method="POST" action="/admin/products" onsubmit="return validateProductForm()">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= htmlspecialchars($data['name'] ?? '') ?>" 
                               required maxlength="255">
                        <div class="form-text">Enter a descriptive name for the product</div>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Price (USD) *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="price" name="price" 
                                   value="<?= htmlspecialchars($data['price'] ?? '') ?>" 
                                   step="0.01" min="0.01" max="9999999.99" required
                                   onblur="validatePrice(this)">
                        </div>
                        <div class="form-text">Price must be greater than $0.00</div>
                    </div>

                    <div class="mb-3">
                        <label for="quantity_available" class="form-label">Initial Stock Quantity *</label>
                        <input type="number" class="form-control" id="quantity_available" name="quantity_available" 
                               value="<?= htmlspecialchars($data['quantity_available'] ?? '0') ?>" 
                               min="0" max="999999" required
                               onblur="validateQuantity(this)">
                        <div class="form-text">Number of items available for purchase</div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="/admin/products" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Products
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Create Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function validateProductForm() {
    let isValid = true;
    
    // Clear previous errors
    document.querySelectorAll('.error-message').forEach(el => el.remove());
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    
    // Validate name
    const name = document.getElementById('name');
    if (!name.value.trim()) {
        showError(name, 'Product name is required');
        isValid = false;
    } else if (name.value.trim().length < 2) {
        showError(name, 'Product name must be at least 2 characters');
        isValid = false;
    }
    
    // Validate price
    const price = document.getElementById('price');
    const priceValue = parseFloat(price.value);
    if (!price.value || isNaN(priceValue) || priceValue <= 0) {
        showError(price, 'Price must be greater than 0');
        isValid = false;
    } else if (priceValue > 9999999.99) {
        showError(price, 'Price cannot exceed $9,999,999.99');
        isValid = false;
    }
    
    // Validate quantity
    const quantity = document.getElementById('quantity_available');
    const quantityValue = parseInt(quantity.value);
    if (!quantity.value || isNaN(quantityValue) || quantityValue < 0) {
        showError(quantity, 'Quantity must be 0 or greater');
        isValid = false;
    } else if (quantityValue > 999999) {
        showError(quantity, 'Quantity cannot exceed 999,999');
        isValid = false;
    }
    
    return isValid;
}

function showError(input, message) {
    input.classList.add('is-invalid');
    const error = document.createElement('div');
    error.className = 'error-message text-danger mt-1';
    error.textContent = message;
    input.parentNode.appendChild(error);
}
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
