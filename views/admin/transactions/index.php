<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>All Transactions</h1>
                <div>
                    <a href="/admin/products" class="btn btn-secondary">Manage Products</a>
                    <a href="/" class="btn btn-primary">View Products</a>
                </div>
            </div>
            
            <?php if (empty($data['transactions'])): ?>
                <div class="alert alert-info">
                    <p class="mb-0">No transactions found.</p>
                </div>
            <?php else: ?>
                <!-- Summary Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">Total Transactions</h5>
                                <h3 class="mb-0"><?= $data['pagination']['total_items'] ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">Total Revenue</h5>
                                <h3 class="mb-0">$<?= number_format(array_sum(array_column($data['transactions'], 'total_amount')), 2) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">Products Sold</h5>
                                <h3 class="mb-0"><?= array_sum(array_column($data['transactions'], 'quantity')) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">Unique Users</h5>
                                <h3 class="mb-0"><?= count(array_unique(array_column($data['transactions'], 'user_id'))) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['transactions'] as $transaction): ?>
                                <tr>
                                    <td>
                                        <small><?= htmlspecialchars(date('M j, Y', strtotime($transaction['transaction_date']))) ?></small><br>
                                        <small class="text-muted"><?= htmlspecialchars(date('g:i A', strtotime($transaction['transaction_date']))) ?></small>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($transaction['username']) ?></strong>
                                    </td>
                                    <td>
                                        <small><?= htmlspecialchars($transaction['email']) ?></small>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($transaction['product_name']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($transaction['quantity']) ?></span>
                                    </td>
                                    <td>$<?= number_format($transaction['unit_price'], 2) ?></td>
                                    <td>
                                        <strong class="text-success">$<?= number_format($transaction['total_amount'], 2) ?></strong>
                                    </td>
                                    <td>
                                        <a href="/products/<?= $transaction['product_id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View Product
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($data['pagination']['total_pages'] > 1): ?>
                    <nav aria-label="Transaction pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($data['pagination']['current_page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $data['pagination']['current_page'] - 1 ?>">Previous</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $data['pagination']['total_pages']; $i++): ?>
                                <li class="page-item <?= $i === $data['pagination']['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($data['pagination']['current_page'] < $data['pagination']['total_pages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $data['pagination']['current_page'] + 1 ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    
                    <div class="text-center text-muted">
                        <small>
                            Showing page <?= $data['pagination']['current_page'] ?> of <?= $data['pagination']['total_pages'] ?> 
                            (<?= $data['pagination']['total_items'] ?> total transactions)
                        </small>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
