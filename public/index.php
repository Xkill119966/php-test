<?php

require_once __DIR__ . '/../vendor/autoload.php';

use VendingMachine\Router\Router;
use VendingMachine\Controllers\ProductsController;
use VendingMachine\Controllers\AuthController;
use VendingMachine\Controllers\ApiController;
use VendingMachine\Auth\SessionManager;

// Start session
session_start();

// Create router instance
$router = new Router();

// Add middleware
$router->addMiddleware('auth', function() {
    $sessionManager = new SessionManager();
    if (!$sessionManager->isLoggedIn()) {
        header('Location: /login');
        return false;
    }
    return true;
});

$router->addMiddleware('admin', function() {
    $sessionManager = new SessionManager();
    if (!$sessionManager->isAdmin()) {
        header('HTTP/1.1 403 Forbidden');
        header('Location: /unauthorized');
        return false;
    }
    return true;
});

// Web routes
$router->get('/', function() {
    try {
        $controller = new ProductsController();
        $result = $controller->getAvailableProducts();
        
        // Prepare data structure for the view
        $data = [
            'products' => $result['products'] ?? [],
            'pagination' => $result['pagination'] ?? [],
            'sorting' => ['sort_by' => 'name', 'sort_order' => 'ASC']
        ];
        
        // Handle success/error messages
        $message = null;
        if (isset($_SESSION['success_message'])) {
            $message = ['type' => 'success', 'text' => $_SESSION['success_message']];
            unset($_SESSION['success_message']);
        } elseif (isset($_SESSION['error_message'])) {
            $message = ['type' => 'danger', 'text' => $_SESSION['error_message']];
            unset($_SESSION['error_message']);
        }
        
        $title = 'Welcome to Vending Machine';
        include __DIR__ . '/../views/products/index.php';
    } catch (Exception $e) {
        // Log error and show user-friendly message
        error_log("Error loading products: " . $e->getMessage());
        $data = ['products' => [], 'pagination' => [], 'sorting' => ['sort_by' => 'name', 'sort_order' => 'ASC']];
        $message = ['type' => 'danger', 'text' => 'Error loading products. Please try again.'];
        $title = 'Welcome to Vending Machine';
        include __DIR__ . '/../views/products/index.php';
    }
});

$router->get('/products', function() {
    try {
        $controller = new ProductsController();
        $page = (int) ($_GET['page'] ?? 1);
        $sortBy = $_GET['sort_by'] ?? 'name';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';
        
        $result = $controller->index($page, 10, $sortBy, $sortOrder);
        
        // Prepare data structure for the view
        $data = [
            'products' => $result['products'] ?? [],
            'pagination' => $result['pagination'] ?? [],
            'sorting' => $result['sorting'] ?? ['sort_by' => 'name', 'sort_order' => 'ASC']
        ];
        
        // Handle success/error messages
        $message = null;
        if (isset($_SESSION['success_message'])) {
            $message = ['type' => 'success', 'text' => $_SESSION['success_message']];
            unset($_SESSION['success_message']);
        } elseif (isset($_SESSION['error_message'])) {
            $message = ['type' => 'danger', 'text' => $_SESSION['error_message']];
            unset($_SESSION['error_message']);
        }
        
        $title = 'All Products';
        include __DIR__ . '/../views/products/index.php';
    } catch (Exception $e) {
        // Log error and show user-friendly message
        error_log("Error loading products: " . $e->getMessage());
        $data = ['products' => [], 'pagination' => [], 'sorting' => ['sort_by' => 'name', 'sort_order' => 'ASC']];
        $message = ['type' => 'danger', 'text' => 'Error loading products. Please try again.'];
        $title = 'All Products';
        include __DIR__ . '/../views/products/index.php';
    }
});

// Debug route to check database connection and products
$router->get('/debug', function() {
    try {
        echo "<h1>Debug Information</h1>";
        
        echo "<h2>Database Connection Test:</h2>";
        $db = VendingMachine\Database\Connection::getInstance();
        echo "✓ Database connection successful<br>";
        
        $stmt = $db->query("SELECT COUNT(*) as count FROM products");
        $count = $stmt->fetch();
        echo "✓ Products in database: " . $count['count'] . "<br>";
        
        $stmt = $db->query("SELECT * FROM products LIMIT 5");
        $products = $stmt->fetchAll();
        echo "<h3>Sample products:</h3>";
        echo "<pre>" . print_r($products, true) . "</pre>";
        
        echo "<h2>Controller Test:</h2>";
        $controller = new ProductsController();
        $result = $controller->getAvailableProducts();
        echo "<h3>Controller result:</h3>";
        echo "<pre>" . print_r($result, true) . "</pre>";
        
        echo "<h2>Session Info:</h2>";
        echo "<pre>" . print_r($_SESSION, true) . "</pre>";
        
    } catch (Exception $e) {
        echo "<h1>Error</h1>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
        echo "<p>File: " . $e->getFile() . "</p>";
        echo "<p>Line: " . $e->getLine() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
});

// Test purchase process route
$router->get('/test-purchase', function() {
    try {
        echo "<h1>Test Purchase Process</h1>";
        
        // Check if user is logged in
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            echo "<p>❌ User not logged in. Please login first.</p>";
            echo "<p><a href='/login'>Go to Login</a></p>";
            return;
        }
        
        echo "<p>✓ User logged in: " . $_SESSION['username'] . "</p>";
        
        // Test product model
        $productModel = new VendingMachine\Models\Product();
        echo "<h3>Testing Product Model:</h3>";
        
        $product = $productModel->findById(1);
        if ($product) {
            echo "<p>✓ Product found: " . $product['name'] . " (Stock: " . $product['quantity_available'] . ")</p>";
            
            // Test decreaseQuantity method
            echo "<h3>Testing decreaseQuantity method:</h3>";
            $success = $productModel->decreaseQuantity(1, 1);
            echo "<p>decreaseQuantity result: " . ($success ? 'Success' : 'Failed') . "</p>";
            
            // Check updated stock
            $updatedProduct = $productModel->findById(1);
            echo "<p>Updated stock: " . $updatedProduct['quantity_available'] . "</p>";
            
        } else {
            echo "<p>❌ Product not found</p>";
        }
        
        // Test transaction model
        echo "<h3>Testing Transaction Model:</h3>";
        $transactionModel = new VendingMachine\Models\Transaction();
        
        $testData = [
            'user_id' => $_SESSION['user_id'],
            'product_id' => 1,
            'quantity' => 1,
            'unit_price' => 3.99,
            'total_amount' => 3.99
        ];
        
        echo "<p>Test transaction data:</p>";
        echo "<pre>" . print_r($testData, true) . "</pre>";
        
        $transactionId = $transactionModel->create($testData);
        echo "<p>✓ Transaction created with ID: " . $transactionId . "</p>";
        
        // Test the SQL query directly
        echo "<h3>Testing SQL Query Directly:</h3>";
        $db = VendingMachine\Database\Connection::getInstance();
        
        // Test the decreaseQuantity SQL
        $sql = "UPDATE products SET quantity_available = quantity_available - :decrease_amount WHERE id = :product_id AND quantity_available >= :min_amount";
        $stmt = $db->prepare($sql);
        
        $stmt->bindValue(':product_id', 1, PDO::PARAM_INT);
        $stmt->bindValue(':decrease_amount', 1, PDO::PARAM_INT);
        $stmt->bindValue(':min_amount', 1, PDO::PARAM_INT);
        
        $result = $stmt->execute();
        echo "<p>Direct SQL test result: " . ($result ? 'Success' : 'Failed') . "</p>";
        echo "<p>Rows affected: " . $stmt->rowCount() . "</p>";
        
        // Check database schema
        echo "<h3>Database Schema Check:</h3>";
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>Tables: " . implode(', ', $tables) . "</p>";
        
        if (in_array('transactions', $tables)) {
            $stmt = $db->query("DESCRIBE transactions");
            $columns = $stmt->fetchAll();
            echo "<p>Transactions table structure:</p>";
            echo "<pre>" . print_r($columns, true) . "</pre>";
        } else {
            echo "<p>❌ Transactions table not found!</p>";
        }
        
    } catch (Exception $e) {
        echo "<h1>Error</h1>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
        echo "<p>File: " . $e->getFile() . "</p>";
        echo "<p>Line: " . $e->getLine() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
});

// Quick user status check
$router->get('/user-status', function() {
    echo "<h1>Current User Status</h1>";
    echo "<p><strong>Session Data:</strong></p>";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
    
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
        echo "<h2>✅ Logged In</h2>";
        echo "<p><strong>Username:</strong> " . htmlspecialchars($_SESSION['username']) . "</p>";
        echo "<p><strong>Role:</strong> " . htmlspecialchars($_SESSION['role']) . "</p>";
        echo "<p><strong>User ID:</strong> " . htmlspecialchars($_SESSION['user_id']) . "</p>";
        
        if ($_SESSION['role'] === 'admin') {
            echo "<h3>🎯 Admin Access</h3>";
            echo "<p><a href='/admin/transactions' class='btn btn-success'>View All Transactions</a></p>";
            echo "<p><a href='/admin/products' class='btn btn-primary'>Manage Products</a></p>";
        } else {
            echo "<h3>👤 Regular User</h3>";
            echo "<p><a href='/transactions' class='btn btn-info'>View My Transactions</a></p>";
        }
    } else {
        echo "<h2>❌ Not Logged In</h2>";
        echo "<p><a href='/login' class='btn btn-primary'>Login</a></p>";
        echo "<p><a href='/register' class='btn btn-secondary'>Register</a></p>";
    }
    
    echo "<hr>";
    echo "<p><a href='/' class='btn btn-outline-secondary'>Back to Home</a></p>";
});

// Debug admin products route
$router->get('/debug-admin-products', function() {
    try {
        echo "<h1>Debug Admin Products</h1>";
        
        // Check if user is logged in and is admin
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            echo "<p>❌ User not logged in</p>";
            return;
        }
        
        if ($_SESSION['role'] !== 'admin') {
            echo "<p>❌ User is not admin (Role: " . htmlspecialchars($_SESSION['role']) . ")</p>";
        } else {
            echo "<p>✅ User is admin: " . htmlspecialchars($_SESSION['username']) . "</p>";
        }
        
        // Test ProductsController
        $controller = new ProductsController();
        $page = 1;
        $result = $controller->index($page, 10);
        
        echo "<h3>Controller Result:</h3>";
        echo "<pre>" . print_r($result, true) . "</pre>";
        
        // Test Product model directly
        $productModel = new \VendingMachine\Models\Product();
        $products = $productModel->findAll(10, 0);
        $count = $productModel->count();
        
        echo "<h3>Direct Model Test:</h3>";
        echo "<p>Products found: " . count($products) . "</p>";
        echo "<p>Total count: " . $count . "</p>";
        echo "<pre>" . print_r($products, true) . "</pre>";
        
        // Check database connection
        $db = \VendingMachine\Database\Connection::getInstance();
        $stmt = $db->query("SELECT COUNT(*) FROM products");
        $dbCount = $stmt->fetchColumn();
        echo "<p>Database count: " . $dbCount . "</p>";
        
    } catch (Exception $e) {
        echo "<h1>Error</h1>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
        echo "<p>File: " . $e->getFile() . "</p>";
        echo "<p>Line: " . $e->getLine() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
});

// Debug products page route
$router->get('/debug-products', function() {
    try {
        echo "<h1>Debug Products Page</h1>";
        
        echo "<h2>Session Info:</h2>";
        echo "<pre>" . print_r($_SESSION, true) . "</pre>";
        
        // Test ProductsController getAvailableProducts
        $controller = new ProductsController();
        $page = 1;
        $result = $controller->getAvailableProducts($page, 10, 'name', 'ASC');
        
        echo "<h3>getAvailableProducts Result:</h3>";
        echo "<pre>" . print_r($result, true) . "</pre>";
        
        // Test ProductsController index
        $indexResult = $controller->index($page, 10, 'name', 'ASC');
        
        echo "<h3>index Result:</h3>";
        echo "<pre>" . print_r($indexResult, true) . "</pre>";
        
        // Test Product model directly
        $productModel = new \VendingMachine\Models\Product();
        $products = $productModel->findAvailable(10, 0, 'name', 'ASC');
        $count = $productModel->count();
        
        echo "<h3>Direct Model Test:</h3>";
        echo "<p>Products found: " . count($products) . "</p>";
        echo "<p>Total count: " . $count . "</p>";
        echo "<pre>" . print_r($products, true) . "</pre>";
        
        // Check database connection
        $db = \VendingMachine\Database\Connection::getInstance();
        $stmt = $db->query("SELECT COUNT(*) FROM products");
        $dbCount = $stmt->fetchColumn();
        echo "<p>Database count: " . $dbCount . "</p>";
        
        if ($dbCount > 0) {
            $stmt = $db->query("SELECT * FROM products LIMIT 3");
            $sampleProducts = $stmt->fetchAll();
            echo "<h3>Sample Products from DB:</h3>";
            echo "<pre>" . print_r($sampleProducts, true) . "</pre>";
        }
        
    } catch (Exception $e) {
        echo "<h1>Error</h1>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
        echo "<p>File: " . $e->getFile() . "</p>";
        echo "<p>Line: " . $e->getLine() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
});

$router->get('/products/{id}', function($id) {
    $controller = new ProductsController();
    $product = $controller->show((int) $id);
    
    if (!$product) {
        header('HTTP/1.1 404 Not Found');
        include __DIR__ . '/../views/404.php';
        return;
    }
    
    $title = $product['name'];
    include __DIR__ . '/../views/products/show.php';
});

$router->get('/purchase/{id}', function($id) {
    $sessionManager = new SessionManager();
    if (!$sessionManager->isLoggedIn()) {
        header('Location: /login');
        return;
    }
    
    // Block admin users from purchasing
    if ($sessionManager->isAdmin()) {
        header('HTTP/1.1 403 Forbidden');
        $_SESSION['error_message'] = 'Admin users cannot purchase products. Use the admin panel to manage products.';
        header('Location: /admin/products');
        exit;
    }
    
    $controller = new ProductsController();
    $product = $controller->show((int) $id);
    
    if (!$product) {
        header('HTTP/1.1 404 Not Found');
        include __DIR__ . '/../views/404.php';
        return;
    }
    
    $title = 'Purchase ' . $product['name'];
    include __DIR__ . '/../views/products/purchase.php';
});

$router->post('/purchase', function() {
    // Block admin users from purchasing
    $sessionManager = new SessionManager();
    if ($sessionManager->isAdmin()) {
        header('HTTP/1.1 403 Forbidden');
        $_SESSION['error_message'] = 'Admin users cannot purchase products. Use the admin panel to manage products.';
        header('Location: /admin/products');
        exit;
    }
    
    $controller = new ProductsController();
    $productId = (int) ($_POST['product_id'] ?? 0);
    $quantity = (int) ($_POST['quantity'] ?? 1);
    
    $result = $controller->purchase($productId, $quantity);
    
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
        header('Location: /products');
    } else {
        $_SESSION['error_message'] = implode(', ', $result['errors']);
        header('Location: /purchase/' . $productId);
    }
    exit;
});

// Authentication routes
$router->get('/login', function() {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
        header('Location: /');
        exit;
    }
    
    $title = 'Login';
    include __DIR__ . '/../views/auth/login.php';
});

$router->post('/login', function() {
    $controller = new AuthController();
    $result = $controller->login($_POST);
    
    if ($result['success']) {
        header('Location: /');
    } else {
        $_SESSION['error_message'] = implode(', ', $result['errors']);
        header('Location: /login');
    }
    exit;
});

$router->get('/register', function() {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
        header('Location: /');
        exit;
    }
    
    $title = 'Register';
    include __DIR__ . '/../views/auth/register.php';
});

$router->post('/register', function() {
    $controller = new AuthController();
    $result = $controller->register($_POST);
    
    if ($result['success']) {
        header('Location: /');
    } else {
        $_SESSION['error_message'] = implode(', ', $result['errors']);
        header('Location: /register');
    }
    exit;
});

$router->get('/logout', function() {
    $controller = new AuthController();
    $controller->logout();
    header('Location: /');
    exit;
});

// Admin routes
$router->get('/admin/products', function() {
    $controller = new ProductsController();
    $page = (int) ($_GET['page'] ?? 1);
    $result = $controller->index($page, 10);
    
    // Prepare data structure for the view
    $data = [
        'products' => $result['products'] ?? [],
        'pagination' => $result['pagination'] ?? [],
        'sorting' => $result['sorting'] ?? ['sort_by' => 'name', 'sort_order' => 'ASC']
    ];
    
    // Handle success/error messages
    $message = null;
    if (isset($_SESSION['success_message'])) {
        $message = ['type' => 'success', 'text' => $_SESSION['success_message']];
        unset($_SESSION['success_message']);
    } elseif (isset($_SESSION['error_message'])) {
        $message = ['type' => 'danger', 'text' => $_SESSION['error_message']];
        unset($_SESSION['error_message']);
    }
    
    $title = 'Manage Products';
    include __DIR__ . '/../views/admin/products/index.php';
}, ['auth', 'admin']);

$router->get('/admin/products/create', function() {
    $title = 'Create Product';
    include __DIR__ . '/../views/admin/products/create.php';
}, ['auth', 'admin']);

$router->post('/admin/products/create', function() {
    $controller = new ProductsController();
    $result = $controller->create($_POST);
    
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
        header('Location: /admin/products');
    } else {
        $_SESSION['error_message'] = implode(', ', $result['errors']);
        header('Location: /admin/products/create');
    }
    exit;
}, ['auth', 'admin']);

$router->get('/admin/products/{id}/edit', function($id) {
    $controller = new ProductsController();
    $product = $controller->show((int) $id);
    
    if (!$product) {
        header('HTTP/1.1 404 Not Found');
        include __DIR__ . '/../views/404.php';
        return;
    }
    
    $title = 'Edit ' . $product['name'];
    include __DIR__ . '/../views/admin/products/edit.php';
}, ['auth', 'admin']);

$router->post('/admin/products/{id}/edit', function($id) {
    $controller = new ProductsController();
    $result = $controller->update((int) $id, $_POST);
    
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
        header('Location: /admin/products');
    } else {
        $_SESSION['error_message'] = implode(', ', $result['errors']);
        header('Location: /admin/products/' . $id . '/edit');
    }
    exit;
}, ['auth', 'admin']);

$router->post('/admin/products/{id}/delete', function($id) {
    $controller = new ProductsController();
    $result = $controller->delete((int) $id);
    
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
    } else {
        $_SESSION['error_message'] = $result['message'];
    }
    
    header('Location: /admin/products');
    exit;
}, ['auth', 'admin']);

// Transactions route
$router->get('/transactions', function() {
    $sessionManager = new SessionManager();
    if (!$sessionManager->isLoggedIn()) {
        header('Location: /login');
        return;
    }
    
    $transactionModel = new \VendingMachine\Models\Transaction();
    $page = (int) ($_GET['page'] ?? 1);
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    if ($sessionManager->isAdmin()) {
        // Admin can see all transactions
        $transactions = $transactionModel->findAllWithDetails($limit, $offset);
        $totalTransactions = $transactionModel->countAll();
    } else {
        // Regular users can only see their own transactions
        $transactions = $transactionModel->findByUserId($_SESSION['user_id'], $limit, $offset);
        $totalTransactions = $transactionModel->countByUserId($_SESSION['user_id']);
    }
    
    $totalPages = ceil($totalTransactions / $limit);
    
    $data = [
        'transactions' => $transactions,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalTransactions,
            'per_page' => $limit
        ]
    ];
    
    $title = 'Transaction History';
    include __DIR__ . '/../views/transactions/index.php';
}, ['auth']);

// Admin transactions route
$router->get('/admin/transactions', function() {
    $sessionManager = new SessionManager();
    if (!$sessionManager->isAdmin()) {
        header('Location: /unauthorized');
        return;
    }
    
    $transactionModel = new \VendingMachine\Models\Transaction();
    $page = (int) ($_GET['page'] ?? 1);
    $limit = 20; // More items per page for admin view
    $offset = ($page - 1) * $limit;
    
    // Admin can see all transactions with more details
    $transactions = $transactionModel->findAllWithDetails($limit, $offset);
    $totalTransactions = $transactionModel->countAll();
    
    $totalPages = ceil($totalTransactions / $limit);
    
    $data = [
        'transactions' => $transactions,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalTransactions,
            'per_page' => $limit
        ]
    ];
    
    $title = 'All Transactions';
    include __DIR__ . '/../views/admin/transactions/index.php';
}, ['auth', 'admin']);

// API routes
$router->get('/api/products', function() {
    header('Content-Type: application/json');
    $controller = new ApiController();
    $result = $controller->getProducts();
    echo json_encode($result);
});

$router->get('/api/products/{id}', function($id) {
    header('Content-Type: application/json');
    $controller = new ApiController();
    $result = $controller->getProduct((int) $id);
    echo json_encode($result);
});

$router->post('/api/products', function() {
    header('Content-Type: application/json');
    $controller = new ApiController();
    $result = $controller->createProduct();
    echo json_encode($result);
});

$router->put('/api/products/{id}', function($id) {
    header('Content-Type: application/json');
    $controller = new ApiController();
    $result = $controller->updateProduct((int) $id);
    echo json_encode($result);
});

$router->delete('/api/products/{id}', function($id) {
    header('Content-Type: application/json');
    $controller = new ApiController();
    $result = $controller->deleteProduct((int) $id);
    echo json_encode($result);
});

$router->post('/api/purchase', function() {
    header('Content-Type: application/json');
    $controller = new ApiController();
    $result = $controller->purchaseProduct();
    echo json_encode($result);
});

$router->post('/api/auth/login', function() {
    header('Content-Type: application/json');
    $controller = new ApiController();
    $result = $controller->login();
    echo json_encode($result);
});

$router->post('/api/auth/register', function() {
    header('Content-Type: application/json');
    $controller = new ApiController();
    $result = $controller->register();
    echo json_encode($result);
});

// API Transactions endpoint
$router->get('/api/transactions', function() {
    header('Content-Type: application/json');
    $controller = new ApiController();
    $result = $controller->getTransactions();
    echo json_encode($result);
});

// Dispatch the request
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$result = $router->dispatch($method, $uri);

// If it's an API call, the result is already JSON encoded
// If it's a web call, the view should handle the output
if ($result === false) {
    // Middleware blocked the request
    exit;
}
