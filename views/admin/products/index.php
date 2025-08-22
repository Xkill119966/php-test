<?php
$title = 'Manage Products - Admin';
include __DIR__ . '/../../layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-boxes me-2"></i>Manage Products</h1>
    <a href="/admin/products/create" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Add New Product
    </a>
</div>

<?php if (isset($message)): ?>
<div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show">
    <?= htmlspecialchars($message['text']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">Products List</h5>
            </div>
            <div class="col-md-6">
                <form method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search products..." 
                           value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="card-body p-0">
        <?php if (empty($data['products'])): ?>
        <div class="text-center py-5">
            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
            <p class="text-muted">No products found</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>
                            <a href="?sort_by=id&sort_order=<?= $data['sorting']['sort_by'] === 'id' && $data['sorting']['sort_order'] === 'ASC' ? 'DESC' : 'ASC' ?>" 
                               class="text-decoration-none">
                                ID <?= $data['sorting']['sort_by'] === 'id' ? ($data['sorting']['sort_order'] === 'ASC' ? '↑' : '↓') : '' ?>
                            </a>
                        </th>
                        <th>
                            <a href="?sort_by=name&sort_order=<?= $data['sorting']['sort_by'] === 'name' && $data['sorting']['sort_order'] === 'ASC' ? 'DESC' : 'ASC' ?>" 
                               class="text-decoration-none">
                                Name <?= $data['sorting']['sort_by'] === 'name' ? ($data['sorting']['sort_order'] === 'ASC' ? '↑' : '↓') : '' ?>
                            </a>
                        </th>
                        <th>
                            <a href="?sort_by=price&sort_order=<?= $data['sorting']['sort_by'] === 'price' && $data['sorting']['sort_order'] === 'ASC' ? 'DESC' : 'ASC' ?>" 
                               class="text-decoration-none">
                                Price <?= $data['sorting']['sort_by'] === 'price' ? ($data['sorting']['sort_order'] === 'ASC' ? '↑' : '↓') : '' ?>
                            </a>
                        </th>
                        <th>
                            <a href="?sort_by=quantity_available&sort_order=<?= $data['sorting']['sort_by'] === 'quantity_available' && $data['sorting']['sort_order'] === 'ASC' ? 'DESC' : 'ASC' ?>" 
                               class="text-decoration-none">
                                Stock <?= $data['sorting']['sort_by'] === 'quantity_available' ? ($data['sorting']['sort_order'] === 'ASC' ? '↑' : '↓') : '' ?>
                            </a>
                        </th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['products'] as $product): ?>
                    <tr>
                        <td><?= $product['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($product['name']) ?></strong>
                        </td>
                        <td>
                            <span class="badge bg-success">$<?= number_format($product['price'], 2) ?></span>
                        </td>
                        <td>
                            <span class="badge <?= $product['quantity_available'] > 0 ? 'bg-primary' : 'bg-danger' ?>">
                                <?= $product['quantity_available'] ?>
                            </span>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?= date('M j, Y', strtotime($product['created_at'])) ?>
                            </small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/admin/products/<?= $product['id'] ?>" class="btn btn-outline-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="/admin/products/<?= $product['id'] ?>/edit" class="btn btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger" title="Delete" 
                                        onclick="confirmDelete(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name']) ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if ($data['pagination']['total_pages'] > 1): ?>
    <div class="card-footer">
        <nav>
            <ul class="pagination justify-content-center mb-0">
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
        
        <div class="text-center mt-2">
            <small class="text-muted">
                Showing <?= count($data['products']) ?> of <?= $data['pagination']['total_count'] ?> products
            </small>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the product "<span id="productName"></span>"?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(productId, productName) {
    document.getElementById('productName').textContent = productName;
    document.getElementById('deleteForm').action = '/admin/products/' + productId;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
