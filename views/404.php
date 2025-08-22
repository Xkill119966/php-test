<?php include __DIR__ . '/layout/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6 text-center">
        <div class="card">
            <div class="card-body">
                <h1 class="display-1 text-muted">404</h1>
                <h2 class="mb-4">Page Not Found</h2>
                <p class="lead mb-4">
                    The page you're looking for doesn't exist or has been moved.
                </p>
                <div class="d-grid gap-2 d-md-block">
                    <a href="/" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>
                        Go Home
                    </a>
                    <a href="/products" class="btn btn-outline-secondary">
                        <i class="fas fa-box me-2"></i>
                        Browse Products
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
