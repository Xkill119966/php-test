<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-box me-2"></i>
                        <?= htmlspecialchars($product['name']) ?>
                    </h4>
                    <div>
                        <a href="/products" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back to Products
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <h5 class="text-primary">$<?= number_format($product['price'], 2) ?></h5>
                            <p class="text-muted mb-2">Price per unit</p>
                        </div>
                        
                        <div class="mb-4">
                            <h6>Availability</h6>
                            <?php if ($product['quantity_available'] > 0): ?>
                                <span class="badge bg-success fs-6">
                                    <i class="fas fa-check-circle me-1"></i>
                                    In Stock (<?= $product['quantity_available'] ?> available)
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger fs-6">
                                    <i class="fas fa-times-circle me-1"></i>
                                    Out of Stock
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($product['quantity_available'] > 0): ?>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <!-- Admin users see management options instead of purchase -->
                                <div class="d-grid gap-2">
                                    <a href="/admin/products/<?= $product['id'] ?>/edit" class="btn btn-warning btn-lg">
                                        <i class="fas fa-edit me-2"></i>
                                        Edit Product
                                    </a>
                                    <a href="/admin/products" class="btn btn-info btn-lg">
                                        <i class="fas fa-cog me-2"></i>
                                        Manage Products
                                    </a>
                                </div>
                            <?php else: ?>
                                <!-- Regular users see purchase button -->
                                <div class="d-grid">
                                    <a href="/purchase/<?= $product['id'] ?>" class="btn btn-success btn-lg">
                                        <i class="fas fa-shopping-cart me-2"></i>
                                        Purchase Now 
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                This product is currently out of stock. Please check back later.
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Product Information
                                </h6>
                                
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Product ID:</strong></td>
                                        <td><?= $product['id'] ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Name:</strong></td>
                                        <td><?= htmlspecialchars($product['name']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Price:</strong></td>
                                        <td>$<?= number_format($product['price'], 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Stock:</strong></td>
                                        <td><?= $product['quantity_available'] ?> units</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Created:</strong></td>
                                        <td><?= date('F j, Y', strtotime($product['created_at'])) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Updated:</strong></td>
                                        <td><?= date('F j, Y', strtotime($product['updated_at'])) ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Admin Actions -->
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-tools me-2"></i>
                    Admin Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex gap-2">
                    <a href="/admin/products/<?= $product['id'] ?>/edit" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>
                        Edit Product
                    </a>
                    
                    <form method="POST" action="/admin/products/<?= $product['id'] ?>/delete" 
                          class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?')">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>
                            Delete Product
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
