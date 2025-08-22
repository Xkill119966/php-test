<?php

namespace VendingMachine\Tests\Controllers;

use PHPUnit\Framework\TestCase;
use VendingMachine\Controllers\ProductsController;
use VendingMachine\Models\Product;
use VendingMachine\Models\Transaction;
use VendingMachine\Auth\SessionManager;
use VendingMachine\Validation\ProductValidator;
use Mockery;

class ProductsControllerTest extends TestCase
{
    private ProductsController $controller;
    private Product $mockProductModel;
    private Transaction $mockTransactionModel;
    private SessionManager $mockSessionManager;
    private ProductValidator $mockValidator;

    protected function setUp(): void
    {
        $this->mockProductModel = Mockery::mock(Product::class);
        $this->mockTransactionModel = Mockery::mock(Transaction::class);
        $this->mockSessionManager = Mockery::mock(SessionManager::class);
        $this->mockValidator = Mockery::mock(ProductValidator::class);

        $this->controller = new ProductsController();
        
        // Use reflection to inject mocks
        $reflection = new \ReflectionClass($this->controller);
        
        $productProperty = $reflection->getProperty('productModel');
        $productProperty->setAccessible(true);
        $productProperty->setValue($this->controller, $this->mockProductModel);
        
        $transactionProperty = $reflection->getProperty('transactionModel');
        $transactionProperty->setAccessible(true);
        $transactionProperty->setValue($this->controller, $this->mockTransactionModel);
        
        $sessionProperty = $reflection->getProperty('sessionManager');
        $sessionProperty->setAccessible(true);
        $sessionProperty->setValue($this->controller, $this->mockSessionManager);
        
        $validatorProperty = $reflection->getProperty('validator');
        $validatorProperty->setAccessible(true);
        $validatorProperty->setValue($this->controller, $this->mockValidator);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testIndexReturnsCorrectData(): void
    {
        $mockProducts = [
            ['id' => 1, 'name' => 'Coke', 'price' => 3.99, 'quantity_available' => 50],
            ['id' => 2, 'name' => 'Pepsi', 'price' => 6.885, 'quantity_available' => 30]
        ];

        $this->mockProductModel->shouldReceive('findAll')
            ->with(10, 0, 'name', 'ASC')
            ->once()
            ->andReturn($mockProducts);

        $this->mockProductModel->shouldReceive('count')
            ->once()
            ->andReturn(2);

        $result = $this->controller->index(1, 10, 'name', 'ASC');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('products', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertArrayHasKey('sorting', $result);
        $this->assertEquals($mockProducts, $result['products']);
        $this->assertEquals(1, $result['pagination']['current_page']);
        $this->assertEquals(1, $result['pagination']['total_pages']);
    }

    public function testShowReturnsProduct(): void
    {
        $mockProduct = ['id' => 1, 'name' => 'Coke', 'price' => 3.99, 'quantity_available' => 50];

        $this->mockProductModel->shouldReceive('findById')
            ->with(1)
            ->once()
            ->andReturn($mockProduct);

        $result = $this->controller->show(1);

        $this->assertEquals($mockProduct, $result);
    }

    public function testShowReturnsNullForNonExistentProduct(): void
    {
        $this->mockProductModel->shouldReceive('findById')
            ->with(999)
            ->once()
            ->andReturn(null);

        $result = $this->controller->show(999);

        $this->assertNull($result);
    }

    public function testCreateWithValidData(): void
    {
        $productData = ['name' => 'New Product', 'price' => 5.99, 'quantity_available' => 25];
        
        $this->mockSessionManager->shouldReceive('requireAdmin')
            ->once();

        $this->mockValidator->shouldReceive('validate')
            ->with($productData)
            ->once()
            ->andReturn(['is_valid' => true, 'errors' => []]);

        $this->mockProductModel->shouldReceive('create')
            ->with($productData)
            ->once()
            ->andReturn(3);

        $result = $this->controller->create($productData);

        $this->assertTrue($result['success']);
        $this->assertEquals(3, $result['product_id']);
        $this->assertEquals('Product created successfully', $result['message']);
    }

    public function testCreateWithInvalidData(): void
    {
        $productData = ['name' => '', 'price' => -5, 'quantity_available' => -10];
        
        $this->mockSessionManager->shouldReceive('requireAdmin')
            ->once();

        $this->mockValidator->shouldReceive('validate')
            ->with($productData)
            ->once()
            ->andReturn([
                'is_valid' => false, 
                'errors' => [
                    'name' => 'Product name is required',
                    'price' => 'Price must be greater than 0',
                    'quantity_available' => 'Quantity cannot be negative'
                ]
            ]);

        $result = $this->controller->create($productData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertCount(3, $result['errors']);
    }

    public function testCreateHandlesException(): void
    {
        $productData = ['name' => 'New Product', 'price' => 5.99, 'quantity_available' => 25];
        
        $this->mockSessionManager->shouldReceive('requireAdmin')
            ->once();

        $this->mockValidator->shouldReceive('validate')
            ->with($productData)
            ->once()
            ->andReturn(['is_valid' => true, 'errors' => []]);

        $this->mockProductModel->shouldReceive('create')
            ->with($productData)
            ->once()
            ->andThrow(new \Exception('Database error'));

        $result = $this->controller->create($productData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertStringContainsString('Database error', $result['errors']['general']);
    }

    public function testUpdateWithValidData(): void
    {
        $productData = ['name' => 'Updated Product', 'price' => 6.99];
        
        $this->mockSessionManager->shouldReceive('requireAdmin')
            ->once();

        $this->mockValidator->shouldReceive('validate')
            ->with($productData, 1)
            ->once()
            ->andReturn(['is_valid' => true, 'errors' => []]);

        $this->mockProductModel->shouldReceive('update')
            ->with(1, $productData)
            ->once()
            ->andReturn(true);

        $result = $this->controller->update(1, $productData);

        $this->assertTrue($result['success']);
        $this->assertEquals('Product updated successfully', $result['message']);
    }

    public function testUpdateWithInvalidData(): void
    {
        $productData = ['name' => '', 'price' => -5];
        
        $this->mockSessionManager->shouldReceive('requireAdmin')
            ->once();

        $this->mockValidator->shouldReceive('validate')
            ->with($productData, 1)
            ->once()
            ->andReturn([
                'is_valid' => false, 
                'errors' => [
                    'name' => 'Product name is required',
                    'price' => 'Price must be greater than 0'
                ]
            ]);

        $result = $this->controller->update(1, $productData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertCount(2, $result['errors']);
    }

    public function testDeleteSuccessfully(): void
    {
        $this->mockSessionManager->shouldReceive('requireAdmin')
            ->once();

        $this->mockProductModel->shouldReceive('delete')
            ->with(1)
            ->once()
            ->andReturn(true);

        $result = $this->controller->delete(1);

        $this->assertTrue($result['success']);
        $this->assertEquals('Product deleted successfully', $result['message']);
    }

    public function testDeleteProductNotFound(): void
    {
        $this->mockSessionManager->shouldReceive('requireAdmin')
            ->once();

        $this->mockProductModel->shouldReceive('delete')
            ->with(999)
            ->once()
            ->andReturn(false);

        $result = $this->controller->delete(999);

        $this->assertFalse($result['success']);
        $this->assertEquals('Product not found', $result['message']);
    }

    public function testPurchaseSuccessfully(): void
    {
        $mockProduct = ['id' => 1, 'name' => 'Coke', 'price' => 3.99, 'quantity_available' => 50];
        
        $this->mockSessionManager->shouldReceive('requireLogin')
            ->once();
        
        $this->mockSessionManager->shouldReceive('getUserId')
            ->once()
            ->andReturn(1);

        $this->mockProductModel->shouldReceive('findById')
            ->with(1)
            ->once()
            ->andReturn($mockProduct);

        $this->mockProductModel->shouldReceive('beginTransaction')
            ->once();

        $this->mockProductModel->shouldReceive('decreaseQuantity')
            ->with(1, 2)
            ->once()
            ->andReturn(true);

        $this->mockTransactionModel->shouldReceive('create')
            ->with([
                'user_id' => 1,
                'product_id' => 1,
                'quantity' => 2,
                'unit_price' => 3.99,
                'total_amount' => 7.98
            ])
            ->once()
            ->andReturn(1);

        $this->mockProductModel->shouldReceive('commit')
            ->once();

        $result = $this->controller->purchase(1, 2);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['transaction_id']);
        $this->assertEquals(7.98, $result['total_amount']);
        $this->assertStringContainsString('Successfully purchased 2 Coke(s)', $result['message']);
    }

    public function testPurchaseProductNotFound(): void
    {
        $this->mockSessionManager->shouldReceive('requireLogin')
            ->once();

        $this->mockProductModel->shouldReceive('findById')
            ->with(999)
            ->once()
            ->andReturn(null);

        $result = $this->controller->purchase(999, 1);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals('Product not found', $result['errors']['product']);
    }

    public function testPurchaseInsufficientStock(): void
    {
        $mockProduct = ['id' => 1, 'name' => 'Coke', 'price' => 3.99, 'quantity_available' => 5];
        
        $this->mockSessionManager->shouldReceive('requireLogin')
            ->once();

        $this->mockProductModel->shouldReceive('findById')
            ->with(1)
            ->once()
            ->andReturn($mockProduct);

        $result = $this->controller->purchase(1, 10);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals('Insufficient stock available', $result['errors']['quantity']);
    }

    public function testPurchaseInvalidQuantity(): void
    {
        $this->mockSessionManager->shouldReceive('requireLogin')
            ->once();

        $result = $this->controller->purchase(1, 0);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals('Quantity must be greater than 0', $result['errors']['quantity']);
    }

    public function testPurchaseHandlesException(): void
    {
        $mockProduct = ['id' => 1, 'name' => 'Coke', 'price' => 3.99, 'quantity_available' => 50];
        
        $this->mockSessionManager->shouldReceive('requireLogin')
            ->once();
        
        $this->mockSessionManager->shouldReceive('getUserId')
            ->once()
            ->andReturn(1);

        $this->mockProductModel->shouldReceive('findById')
            ->with(1)
            ->once()
            ->andReturn($mockProduct);

        $this->mockProductModel->shouldReceive('beginTransaction')
            ->once();

        $this->mockProductModel->shouldReceive('decreaseQuantity')
            ->with(1, 2)
            ->once()
            ->andThrow(new \Exception('Database error'));

        $this->mockProductModel->shouldReceive('rollback')
            ->once();

        $result = $this->controller->purchase(1, 2);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertStringContainsString('Database error', $result['errors']['general']);
    }

    public function testGetAvailableProducts(): void
    {
        $mockProducts = [
            ['id' => 1, 'name' => 'Coke', 'price' => 3.99, 'quantity_available' => 50],
            ['id' => 2, 'name' => 'Pepsi', 'price' => 6.885, 'quantity_available' => 30]
        ];

        $this->mockProductModel->shouldReceive('findAvailable')
            ->with(10, 0, 'name', 'ASC')
            ->once()
            ->andReturn($mockProducts);

        $result = $this->controller->getAvailableProducts(1, 10, 'name', 'ASC');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('products', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertEquals($mockProducts, $result['products']);
        $this->assertEquals(1, $result['pagination']['current_page']);
        $this->assertEquals(10, $result['pagination']['per_page']);
    }

    public function testSearchProducts(): void
    {
        $mockProducts = [
            ['id' => 1, 'name' => 'Coke', 'price' => 3.99, 'quantity_available' => 50]
        ];

        $this->mockProductModel->shouldReceive('search')
            ->with('coke', 10, 0)
            ->once()
            ->andReturn($mockProducts);

        $result = $this->controller->search('coke', 1, 10);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('products', $result);
        $this->assertArrayHasKey('query', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertEquals($mockProducts, $result['products']);
        $this->assertEquals('coke', $result['query']);
    }
}
