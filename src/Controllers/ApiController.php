<?php

namespace VendingMachine\Controllers;

use VendingMachine\Models\Product;
use VendingMachine\Models\Transaction;
use VendingMachine\Models\User;
use VendingMachine\Auth\SessionManager;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ApiController
{
    private Product $productModel;
    private Transaction $transactionModel;
    private User $userModel;
    private SessionManager $sessionManager;
    private string $jwtSecret;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->transactionModel = new Transaction();
        $this->userModel = new User();
        $this->sessionManager = new SessionManager();
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-in-production';
    }

    public function getProducts(): array
    {
        try {
            $page = (int) ($_GET['page'] ?? 1);
            $perPage = (int) ($_GET['per_page'] ?? 10);
            $sortBy = $_GET['sort_by'] ?? 'name';
            $sortOrder = $_GET['sort_order'] ?? 'ASC';
            
            $result = $this->productModel->findAll($perPage, ($page - 1) * $perPage, $sortBy, $sortOrder);
            $totalCount = $this->productModel->count();
            
            return [
                'success' => true,
                'data' => $result,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_count' => $totalCount,
                    'total_pages' => ceil($totalCount / $perPage)
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to fetch products: ' . $e->getMessage()
            ];
        }
    }

    public function getProduct(int $id): array
    {
        try {
            $product = $this->productModel->findById($id);
            
            if (!$product) {
                return [
                    'success' => false,
                    'error' => 'Product not found'
                ];
            }
            
            return [
                'success' => true,
                'data' => $product
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to fetch product: ' . $e->getMessage()
            ];
        }
    }

    public function createProduct(): array
    {
        if (!$this->authenticateJWT()) {
            return ['success' => false, 'error' => 'Unauthorized'];
        }
        
        if (!$this->isAdmin()) {
            return ['success' => false, 'error' => 'Forbidden'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        try {
            $productId = $this->productModel->create($input);
            $product = $this->productModel->findById($productId);
            
            return [
                'success' => true,
                'data' => $product,
                'message' => 'Product created successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to create product: ' . $e->getMessage()
            ];
        }
    }

    public function updateProduct(int $id): array
    {
        if (!$this->authenticateJWT()) {
            return ['success' => false, 'error' => 'Unauthorized'];
        }
        
        if (!$this->isAdmin()) {
            return ['success' => false, 'error' => 'Forbidden'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        try {
            $success = $this->productModel->update($id, $input);
            
            if ($success) {
                $product = $this->productModel->findById($id);
                return [
                    'success' => true,
                    'data' => $product,
                    'message' => 'Product updated successfully'
                ];
            }
            
            return [
                'success' => false,
                'error' => 'Product not found'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to update product: ' . $e->getMessage()
            ];
        }
    }

    public function deleteProduct(int $id): array
    {
        if (!$this->authenticateJWT()) {
            return ['success' => false, 'error' => 'Unauthorized'];
        }
        
        if (!$this->isAdmin()) {
            return ['success' => false, 'error' => 'Forbidden'];
        }

        try {
            $success = $this->productModel->delete($id);
            
            return [
                'success' => $success,
                'message' => $success ? 'Product deleted successfully' : 'Product not found'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to delete product: ' . $e->getMessage()
            ];
        }
    }

    public function purchaseProduct(): array
    {
        if (!$this->authenticateJWT()) {
            return ['success' => false, 'error' => 'Unauthorized'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $productId = (int) ($input['product_id'] ?? 0);
        $quantity = (int) ($input['quantity'] ?? 1);
        
        try {
            $userId = $this->getCurrentUserId();
            $product = $this->productModel->findById($productId);
            
            if (!$product) {
                return [
                    'success' => false,
                    'error' => 'Product not found'
                ];
            }

            if ($product['quantity_available'] < $quantity) {
                return [
                    'success' => false,
                    'error' => 'Insufficient stock available'
                ];
            }

            $this->productModel->beginTransaction();

            // Decrease product quantity
            $success = $this->productModel->decreaseQuantity($productId, $quantity);
            if (!$success) {
                $this->productModel->rollback();
                return [
                    'success' => false,
                    'error' => 'Failed to update product quantity'
                ];
            }

            // Create transaction record
            $unitPrice = $product['price'];
            $totalAmount = $unitPrice * $quantity;
            
            $transactionData = [
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_amount' => $totalAmount
            ];

            $transactionId = $this->transactionModel->create($transactionData);
            
            $this->productModel->commit();

            return [
                'success' => true,
                'data' => [
                    'transaction_id' => $transactionId,
                    'total_amount' => $totalAmount,
                    'product_name' => $product['name'],
                    'quantity' => $quantity
                ],
                'message' => "Successfully purchased {$quantity} {$product['name']}(s) for $" . number_format($totalAmount, 2)
            ];

        } catch (\Exception $e) {
            $this->productModel->rollback();
            return [
                'success' => false,
                'error' => 'Purchase failed: ' . $e->getMessage()
            ];
        }
    }

    public function login(): array
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        try {
            $user = $this->userModel->authenticate($input['username'], $input['password']);
            
            if ($user) {
                $token = $this->generateJWT($user);
                return [
                    'success' => true,
                    'data' => [
                        'token' => $token,
                        'user' => $user
                    ],
                    'message' => 'Login successful'
                ];
            }
            
            return [
                'success' => false,
                'error' => 'Invalid credentials'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Login failed: ' . $e->getMessage()
            ];
        }
    }

    public function getTransactions(): array
    {
        if (!$this->authenticateJWT()) {
            return ['success' => false, 'error' => 'Unauthorized'];
        }

        try {
            $page = (int) ($_GET['page'] ?? 1);
            $perPage = (int) ($_GET['per_page'] ?? 10);
            $offset = ($page - 1) * $perPage;
            
            $currentUser = $_SESSION['api_user'];
            
            if ($currentUser['role'] === 'admin') {
                // Admin can see all transactions
                $transactions = $this->transactionModel->findAllWithDetails($perPage, $offset);
                $totalCount = $this->transactionModel->countAll();
            } else {
                // Regular users can only see their own transactions
                $transactions = $this->transactionModel->findByUserId($currentUser['user_id'], $perPage, $offset);
                $totalCount = $this->transactionModel->countByUserId($currentUser['user_id']);
            }
            
            return [
                'success' => true,
                'data' => $transactions,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_count' => $totalCount,
                    'total_pages' => ceil($totalCount / $perPage)
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to fetch transactions: ' . $e->getMessage()
            ];
        }
    }

    public function register(): array
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        try {
            // Check if username or email already exists
            if ($this->userModel->findByUsername($input['username'])) {
                return [
                    'success' => false,
                    'error' => 'Username already exists'
                ];
            }

            if ($this->userModel->findByEmail($input['email'])) {
                return [
                    'success' => false,
                    'error' => 'Email already exists'
                ];
            }

            $userId = $this->userModel->create([
                'username' => $input['username'],
                'email' => $input['email'],
                'password' => $input['password'],
                'role' => 'user'
            ]);

            $user = $this->userModel->findById($userId);
            $token = $this->generateJWT($user);

            return [
                'success' => true,
                'data' => [
                    'token' => $token,
                    'user' => $user
                ],
                'message' => 'Registration successful'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Registration failed: ' . $e->getMessage()
            ];
        }
    }

    private function generateJWT(array $user): string
    {
        $payload = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24) // 24 hours
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    private function authenticateJWT(): bool
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return false;
        }

        $token = $matches[1];
        
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            $_SESSION['api_user'] = (array) $decoded;
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function isAdmin(): bool
    {
        return $_SESSION['api_user']['role'] === 'admin';
    }

    private function getCurrentUserId(): int
    {
        return $_SESSION['api_user']['user_id'];
    }
}
