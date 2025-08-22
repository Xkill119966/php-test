<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-shopping-cart me-2"></i>
                    Purchase Product
                </h4>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($_SESSION['error_message']) ?>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <h5><?= htmlspecialchars($product['name']) ?></h5>
                        <p class="text-muted">Product ID: <?= $product['id'] ?></p>
                        
                        <div class="mb-3">
                            <strong>Price:</strong> 
                            <span class="text-primary fs-5">$<?= number_format($product['price'], 2) ?></span>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Available Quantity:</strong> 
                            <span class="badge bg-<?= $product['quantity_available'] > 0 ? 'success' : 'danger' ?>">
                                <?= $product['quantity_available'] ?>
                            </span>
                        </div>
                        
                        <?php if ($product['quantity_available'] <= 0): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                This product is currently out of stock.
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6">
                        <form method="POST" action="/purchase" id="purchaseForm">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" 
                                       value="1" min="1" max="<?= $product['quantity_available'] ?>" 
                                       <?= $product['quantity_available'] <= 0 ? 'disabled' : '' ?> required>
                                <div class="invalid-feedback" id="quantity-error"></div>
                                <div class="form-text">Maximum quantity: <?= $product['quantity_available'] ?></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Total Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" class="form-control" id="totalAmount" 
                                           value="<?= number_format($product['price'], 2) ?>" readonly>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success" 
                                        <?= $product['quantity_available'] <= 0 ? 'disabled' : '' ?>>
                                    <i class="fas fa-credit-card me-2"></i>
                                    Complete Purchase
                                </button>
                                
                                <a href="/products" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Back to Products
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Product Details Card -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Product Details
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Created:</strong> <?= date('F j, Y', strtotime($product['created_at'])) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Last Updated:</strong> <?= date('F j, Y', strtotime($product['updated_at'])) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('purchaseForm');
    const quantityInput = document.getElementById('quantity');
    const totalAmountInput = document.getElementById('totalAmount');
    const productPrice = <?= $product['price'] ?>;
    const maxQuantity = <?= $product['quantity_available'] ?>;
    
    // Update total amount when quantity changes
    quantityInput.addEventListener('input', function() {
        const quantity = parseInt(this.value) || 0;
        const total = (quantity * productPrice).toFixed(2);
        totalAmountInput.value = total;
        
        // Validate quantity
        validateQuantity(quantity);
    });
    
    // Form validation
    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
        }
    });
    
    function validateForm() {
        const quantity = parseInt(quantityInput.value) || 0;
        return validateQuantity(quantity);
    }
    
    function validateQuantity(quantity) {
        let isValid = true;
        
        // Clear previous errors
        clearQuantityError();
        
        if (quantity <= 0) {
            showQuantityError('Quantity must be greater than 0');
            isValid = false;
        } else if (quantity > maxQuantity) {
            showQuantityError(`Quantity cannot exceed available stock (${maxQuantity})`);
            isValid = false;
        }
        
        return isValid;
    }
    
    function showQuantityError(message) {
        quantityInput.classList.add('is-invalid');
        document.getElementById('quantity-error').textContent = message;
    }
    
    function clearQuantityError() {
        quantityInput.classList.remove('is-invalid');
        document.getElementById('quantity-error').textContent = '';
    }
    
    // Initialize total amount
    quantityInput.dispatchEvent(new Event('input'));
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
