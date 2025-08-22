<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Transaction History</h1>
            
            <?php if (empty($data['transactions'])): ?>
                <div class="alert alert-info">
                    <p class="mb-0">No transactions found.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Amount</th>
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                    <th>User</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['transactions'] as $transaction): ?>
                                <tr>
                                    <td><?= htmlspecialchars(date('M j, Y g:i A', strtotime($transaction['transaction_date']))) ?></td>
                                    <td><?= htmlspecialchars($transaction['product_name']) ?></td>
                                    <td><?= htmlspecialchars($transaction['quantity']) ?></td>
                                    <td>$<?= number_format($transaction['unit_price'], 2) ?></td>
                                    <td><strong>$<?= number_format($transaction['total_amount'], 2) ?></strong></td>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                        <td><?= htmlspecialchars($transaction['username']) ?></td>
                                    <?php endif; ?>
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
            
            <div class="mt-4">
                <a href="/" class="btn btn-primary">Back to Products</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="/admin/products" class="btn btn-secondary">Manage Products</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
