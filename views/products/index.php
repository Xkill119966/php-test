<?php
$title = 'Products - Vending Machine';
include __DIR__ . '/../layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-shopping-cart me-2"></i>Available Products</h1>
    <div class="d-flex gap-2">
        <form method="GET" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="Search products..." 
                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit" class="btn btn-outline-primary">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
</div>

<?php if (isset($message)): ?>
<div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show">
    <?= htmlspecialchars($message['text']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Admin Notice:</strong> You are viewing products as an administrator. 
    Use the <a href="/admin/products" class="alert-link">Admin Panel</a> to manage products instead of purchasing them.
</div>
<?php endif; ?>

<?php if (empty($data['products'])): ?>
<div class="text-center py-5">
    <i class="fas fa-box-open fa-4x text-muted mb-4"></i>
    <h3 class="text-muted">No products available</h3>
    <p class="text-muted">Check back later for new products!</p>
    
    <!-- Debug Information -->
    <div class="mt-3 p-3 bg-light text-start">
        <h6>Debug Info:</h6>
        <p><strong>Products count:</strong> <?= count($data['products'] ?? []) ?></p>
        <p><strong>Data keys:</strong> <?= implode(', ', array_keys($data)) ?></p>
        <p><strong>Data structure:</strong></p>
        <pre><?php print_r($data); ?></pre>
        
        <h6>Session Info:</h6>
        <pre><?php print_r($_SESSION); ?></pre>
    </div>
</div>
<?php else: ?>

<!-- Sorting Options -->
<div class="row mb-3">
    <div class="col-md-6">
        <div class="btn-group" role="group">
            <input type="radio" class="btn-check" name="sort" id="sort-name" autocomplete="off" 
                   <?= ($data['sorting']['sort_by'] ?? 'name') === 'name' ? 'checked' : '' ?>>
            <label class="btn btn-outline-secondary" for="sort-name" onclick="sortProducts('name', 'ASC')">
                <i class="fas fa-sort-alpha-down me-1"></i>Name
            </label>

            <input type="radio" class="btn-check" name="sort" id="sort-price-low" autocomplete="off"
                   <?= ($data['sorting']['sort_by'] ?? '') === 'price' && ($data['sorting']['sort_order'] ?? '') === 'ASC' ? 'checked' : '' ?>>
            <label class="btn btn-outline-secondary" for="sort-price-low" onclick="sortProducts('price', 'ASC')">
                <i class="fas fa-dollar-sign me-1"></i>Price: Low to High
            </label>

            <input type="radio" class="btn-check" name="sort" id="sort-price-high" autocomplete="off"
                   <?= ($data['sorting']['sort_by'] ?? '') === 'price' && ($data['sorting']['sort_order'] ?? '') === 'DESC' ? 'checked' : '' ?>>
            <label class="btn btn-outline-secondary" for="sort-price-high" onclick="sortProducts('price', 'DESC')">
                <i class="fas fa-dollar-sign me-1"></i>Price: High to Low
            </label>
        </div>
    </div>
    <div class="col-md-6 text-end">
        <small class="text-muted">Showing <?= count($data['products']) ?> products</small>
    </div>
</div>

<!-- Products Grid -->
<div class="row">
    <?php foreach ($data['products'] as $product): ?>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card product-card h-100">
            <div class="card-body d-flex flex-column">
                <div class="text-center mb-3">
                    <i class="fas fa-cube fa-3x text-primary"></i>
                </div>
                
                <h5 class="card-title text-center"><?= htmlspecialchars($product['name']) ?></h5>
                
                <div class="text-center mb-3">
                    <span class="h4 text-success">$<?= number_format($product['price'], 2) ?></span>
                </div>
                
                <div class="text-center mb-3">
                    <?php if ($product['quantity_available'] > 0): ?>
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle me-1"></i>
                            <?= $product['quantity_available'] ?> in stock
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger">
                            <i class="fas fa-times-circle me-1"></i>
                            Out of stock
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="mt-auto">
                    <?php if ($product['quantity_available'] > 0): ?>
                        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <!-- Admin users see management options instead of purchase -->
                                <div class="d-grid gap-2">
                                    <a href="/admin/products/<?= $product['id'] ?>/edit" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </a>
                                    <a href="/admin/products" class="btn btn-info btn-sm">
                                        <i class="fas fa-cog me-1"></i>Manage
                                    </a>
                                </div>
                            <?php else: ?>
                                <!-- Regular users see purchase button -->
                                <button type="button" class="btn btn-primary w-100" 
                                        onclick="showPurchaseModal(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name']) ?>', <?= $product['price'] ?>, <?= $product['quantity_available'] ?>)">
                                    <i class="fas fa-shopping-cart me-1"></i>Purchase
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="/login" class="btn btn-outline-primary w-100">
                                <i class="fas fa-sign-in-alt me-1"></i>Login to Purchase
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <button type="button" class="btn btn-secondary w-100" disabled>
                            <i class="fas fa-ban me-1"></i>Out of Stock
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Pagination -->
<?php if (isset($data['pagination']) && $data['pagination']['total_pages'] > 1): ?>
<nav class="mt-4">
    <ul class="pagination justify-content-center">
        <?php if ($data['pagination']['has_prev']): ?>
        <li class="page-item">
            <a class="page-link" href="?page=<?= $data['pagination']['current_page'] - 1 ?>">Previous</a>
        </li>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $data['pagination']['total_pages']; $i++): ?>
        <li class="page-item <?= $i === $data['pagination']['current_page'] ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        
        <?php if ($data['pagination']['has_next']): ?>
        <li class="page-item">
            <a class="page-link" href="?page=<?= $data['pagination']['current_page'] + 1 ?>">Next</a>
        </li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>

<?php endif; ?>

<!-- Purchase Modal -->
<div class="modal fade" id="purchaseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Purchase Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="purchaseForm" method="POST" action="/purchase">
                <div class="modal-body">
                    <input type="hidden" id="productId" name="product_id">
                    
                    <div class="text-center mb-3">
                        <i class="fas fa-cube fa-2x text-primary mb-2"></i>
                        <h5 id="productName"></h5>
                        <p class="text-success h5">$<span id="productPrice"></span></p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary" onclick="changeQuantity(-1)">-</button>
                            <input type="number" class="form-control text-center" id="quantity" name="quantity" 
                                   value="1" min="1" max="1" onchange="updateTotal()">
                            <button type="button" class="btn btn-outline-secondary" onclick="changeQuantity(1)">+</button>
                        </div>
                        <small class="text-muted">Available: <span id="maxQuantity"></span></small>
                    </div>
                    
                    <div class="alert alert-info">
                        <strong>Total: $<span id="totalAmount">0.00</span></strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-shopping-cart me-1"></i>Purchase Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentPrice = 0;
let maxStock = 0;

function sortProducts(sortBy, sortOrder) {
    const url = new URL(window.location);
    url.searchParams.set('sort_by', sortBy);
    url.searchParams.set('sort_order', sortOrder);
    window.location.href = url.toString();
}

function showPurchaseModal(productId, productName, price, stock) {
    document.getElementById('productId').value = productId;
    document.getElementById('productName').textContent = productName;
    document.getElementById('productPrice').textContent = price.toFixed(2);
    document.getElementById('maxQuantity').textContent = stock;
    document.getElementById('quantity').max = stock;
    document.getElementById('quantity').value = 1;
    
    currentPrice = price;
    maxStock = stock;
    updateTotal();
    
    new bootstrap.Modal(document.getElementById('purchaseModal')).show();
}

function changeQuantity(delta) {
    const quantityInput = document.getElementById('quantity');
    let newQuantity = parseInt(quantityInput.value) + delta;
    
    if (newQuantity < 1) newQuantity = 1;
    if (newQuantity > maxStock) newQuantity = maxStock;
    
    quantityInput.value = newQuantity;
    updateTotal();
}

function updateTotal() {
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    const total = currentPrice * quantity;
    document.getElementById('totalAmount').textContent = total.toFixed(2);
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
