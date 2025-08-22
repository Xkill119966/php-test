<?php

namespace VendingMachine\Controllers;

use VendingMachine\Models\Product;
use VendingMachine\Models\Transaction;
use VendingMachine\Auth\SessionManager;
use VendingMachine\Validation\ProductValidator;

class ProductsController
{
    private Product $productModel;
    private Transaction $transactionModel;
    private SessionManager $sessionManager;
    private ProductValidator $validator;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->transactionModel = new Transaction();
        $this->sessionManager = new SessionManager();
        $this->validator = new ProductValidator();
    }

    public function index(int $page = 1, int $perPage = 10, string $sortBy = 'name', string $sortOrder = 'ASC'): array
    {
        $offset = ($page - 1) * $perPage;
        $products = $this->productModel->findAll($perPage, $offset, $sortBy, $sortOrder);
        $totalCount = $this->productModel->count();
        $totalPages = ceil($totalCount / $perPage);

        return [
            'products' => $products,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_count' => $totalCount,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ],
            'sorting' => [
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder
            ]
        ];
    }

    public function show(int $id): ?array
    {
        return $this->productModel->findById($id);
    }

    public function create(array $data): array
    {
        $this->sessionManager->requireAdmin();
        
        $validation = $this->validator->validate($data);
        if (!$validation['is_valid']) {
            return [
                'success' => false,
                'errors' => $validation['errors']
            ];
        }

        try {
            $productId = $this->productModel->create($data);
            return [
                'success' => true,
                'product_id' => $productId,
                'message' => 'Product created successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['general' => 'Failed to create product: ' . $e->getMessage()]
            ];
        }
    }

    public function update(int $id, array $data): array
    {
        $this->sessionManager->requireAdmin();
        
        $validation = $this->validator->validate($data, $id);
        if (!$validation['is_valid']) {
            return [
                'success' => false,
                'errors' => $validation['errors']
            ];
        }

        try {
            $success = $this->productModel->update($id, $data);
            return [
                'success' => $success,
                'message' => $success ? 'Product updated successfully' : 'Product not found'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['general' => 'Failed to update product: ' . $e->getMessage()]
            ];
        }
    }

    public function delete(int $id): array
    {
        $this->sessionManager->requireAdmin();
        
        try {
            $success = $this->productModel->delete($id);
            return [
                'success' => $success,
                'message' => $success ? 'Product deleted successfully' : 'Product not found'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['general' => 'Failed to delete product: ' . $e->getMessage()]
            ];
        }
    }

    public function purchase(int $productId, int $quantity = 1): array
    {
        $this->sessionManager->requireLogin();
        
        $userId = $this->sessionManager->getUserId();
        $product = $this->productModel->findById($productId);
        
        if (!$product) {
            return [
                'success' => false,
                'errors' => ['product' => 'Product not found']
            ];
        }

        if ($quantity <= 0) {
            return [
                'success' => false,
                'errors' => ['quantity' => 'Quantity must be greater than 0']
            ];
        }

        if ($product['quantity_available'] < $quantity) {
            return [
                'success' => false,
                'errors' => ['quantity' => 'Insufficient stock available']
            ];
        }

        try {
            $this->productModel->beginTransaction();

            // Decrease product quantity
            $success = $this->productModel->decreaseQuantity($productId, $quantity);
            if (!$success) {
                $this->productModel->rollback();
                return [
                    'success' => false,
                    'errors' => ['quantity' => 'Failed to update product quantity']
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

            // Debug: Log transaction data
            error_log("Creating transaction with data: " . print_r($transactionData, true));

            $transactionId = $this->transactionModel->create($transactionData);
            
            $this->productModel->commit();

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'total_amount' => $totalAmount,
                'message' => "Successfully purchased {$quantity} {$product['name']}(s) for $" . number_format($totalAmount, 2)
            ];

        } catch (\Exception $e) {
            $this->productModel->rollback();
            error_log("Purchase error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            return [
                'success' => false,
                'errors' => ['general' => 'Purchase failed: ' . $e->getMessage()]
            ];
        }
    }

    public function getAvailableProducts(int $page = 1, int $perPage = 10, string $sortBy = 'name', string $sortOrder = 'ASC'): array
    {
        $offset = ($page - 1) * $perPage;
        $products = $this->productModel->findAvailable($perPage, $offset, $sortBy, $sortOrder);
        $totalCount = $this->productModel->count();
        $totalPages = ceil($totalCount / $perPage);
        
        return [
            'products' => $products,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_count' => $totalCount,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ],
            'sorting' => [
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder
            ]
        ];
    }

    public function search(string $query, int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $products = $this->productModel->search($query, $perPage, $offset);
        
        return [
            'products' => $products,
            'query' => $query,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage
            ]
        ];
    }
}
