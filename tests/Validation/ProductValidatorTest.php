<?php

namespace VendingMachine\Tests\Validation;

use PHPUnit\Framework\TestCase;
use VendingMachine\Validation\ProductValidator;

class ProductValidatorTest extends TestCase
{
    private ProductValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new ProductValidator();
    }

    public function testValidateWithValidData(): void
    {
        $data = [
            'name' => 'Test Product',
            'price' => 9.99,
            'quantity_available' => 50
        ];

        $result = $this->validator->validate($data);

        $this->assertTrue($result['is_valid']);
        $this->assertEmpty($result['errors']);
    }

    public function testValidateWithMissingName(): void
    {
        $data = [
            'price' => 9.99,
            'quantity_available' => 50
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('name', $result['errors']);
        $this->assertEquals('Product name is required', $result['errors']['name']);
    }

    public function testValidateWithEmptyName(): void
    {
        $data = [
            'name' => '',
            'price' => 9.99,
            'quantity_available' => 50
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('name', $result['errors']);
        $this->assertEquals('Product name is required', $result['errors']['name']);
    }

    public function testValidateWithShortName(): void
    {
        $data = [
            'name' => 'A',
            'price' => 9.99,
            'quantity_available' => 50
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('name', $result['errors']);
        $this->assertEquals('Product name must be at least 2 characters long', $result['errors']['name']);
    }

    public function testValidateWithLongName(): void
    {
        $data = [
            'name' => str_repeat('A', 256),
            'price' => 9.99,
            'quantity_available' => 50
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('name', $result['errors']);
        $this->assertEquals('Product name must not exceed 255 characters', $result['errors']['name']);
    }

    public function testValidateWithMissingPrice(): void
    {
        $data = [
            'name' => 'Test Product',
            'quantity_available' => 50
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('price', $result['errors']);
        $this->assertEquals('Price is required', $result['errors']['price']);
    }

    public function testValidateWithNonNumericPrice(): void
    {
        $data = [
            'name' => 'Test Product',
            'price' => 'invalid',
            'quantity_available' => 50
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('price', $result['errors']);
        $this->assertEquals('Price must be a valid number', $result['errors']['price']);
    }

    public function testValidateWithZeroPrice(): void
    {
        $data = [
            'name' => 'Test Product',
            'price' => 0,
            'quantity_available' => 50
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('price', $result['errors']);
        $this->assertEquals('Price must be greater than 0', $result['errors']['price']);
    }

    public function testValidateWithNegativePrice(): void
    {
        $data = [
            'name' => 'Test Product',
            'price' => -5.99,
            'quantity_available' => 50
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('price', $result['errors']);
        $this->assertEquals('Price must be greater than 0', $result['errors']['price']);
    }

    public function testValidateWithHighPrice(): void
    {
        $data = [
            'name' => 'Test Product',
            'price' => 10000000.00,
            'quantity_available' => 50
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('price', $result['errors']);
        $this->assertEquals('Price cannot exceed $9,999,999.99', $result['errors']['price']);
    }

    public function testValidateWithMissingQuantity(): void
    {
        $data = [
            'name' => 'Test Product',
            'price' => 9.99
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('quantity_available', $result['errors']);
        $this->assertEquals('Quantity is required', $result['errors']['quantity_available']);
    }

    public function testValidateWithNonNumericQuantity(): void
    {
        $data = [
            'name' => 'Test Product',
            'price' => 9.99,
            'quantity_available' => 'invalid'
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('quantity_available', $result['errors']);
        $this->assertEquals('Quantity must be a valid number', $result['errors']['quantity_available']);
    }

    public function testValidateWithNegativeQuantity(): void
    {
        $data = [
            'name' => 'Test Product',
            'price' => 9.99,
            'quantity_available' => -10
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('quantity_available', $result['errors']);
        $this->assertEquals('Quantity cannot be negative', $result['errors']['quantity_available']);
    }

    public function testValidateWithHighQuantity(): void
    {
        $data = [
            'name' => 'Test Product',
            'price' => 9.99,
            'quantity_available' => 1000000
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('quantity_available', $result['errors']);
        $this->assertEquals('Quantity cannot exceed 999,999', $result['errors']['quantity_available']);
    }

    public function testValidateWithMultipleErrors(): void
    {
        $data = [
            'name' => '',
            'price' => -5,
            'quantity_available' => -10
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result['is_valid']);
        $this->assertCount(3, $result['errors']);
        $this->assertArrayHasKey('name', $result['errors']);
        $this->assertArrayHasKey('price', $result['errors']);
        $this->assertArrayHasKey('quantity_available', $result['errors']);
    }

    public function testValidatePurchaseWithValidData(): void
    {
        $data = [
            'product_id' => 1,
            'quantity' => 5
        ];

        $result = $this->validator->validatePurchase($data);

        $this->assertTrue($result['is_valid']);
        $this->assertEmpty($result['errors']);
    }

    public function testValidatePurchaseWithMissingProductId(): void
    {
        $data = [
            'quantity' => 5
        ];

        $result = $this->validator->validatePurchase($data);

        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('product_id', $result['errors']);
        $this->assertEquals('Product ID is required', $result['errors']['product_id']);
    }

    public function testValidatePurchaseWithInvalidProductId(): void
    {
        $data = [
            'product_id' => 'invalid',
            'quantity' => 5
        ];

        $result = $this->validator->validatePurchase($data);

        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('product_id', $result['errors']);
        $this->assertEquals('Invalid product ID', $result['errors']['product_id']);
    }

    public function testValidatePurchaseWithZeroProductId(): void
    {
        $data = [
            'product_id' => 0,
            'quantity' => 5
        ];

        $result = $this->validator->validatePurchase($data);

        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('product_id', $result['errors']);
        $this->assertEquals('Invalid product ID', $result['errors']['product_id']);
    }

    public function testValidatePurchaseWithMissingQuantity(): void
    {
        $data = [
            'product_id' => 1
        ];

        $result = $this->validator->validatePurchase($data);

        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('quantity', $result['errors']);
        $this->assertEquals('Quantity is required', $result['errors']['quantity']);
    }

    public function testValidatePurchaseWithInvalidQuantity(): void
    {
        $data = [
            'product_id' => 1,
            'quantity' => 'invalid'
        ];

        $result = $this->validator->validatePurchase($data);

        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('quantity', $result['errors']);
        $this->assertEquals('Quantity must be greater than 0', $result['errors']['quantity']);
    }

    public function testValidatePurchaseWithZeroQuantity(): void
    {
        $data = [
            'product_id' => 1,
            'quantity' => 0
        ];

        $result = $this->validator->validatePurchase($data);

        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('quantity', $result['errors']);
        $this->assertEquals('Quantity must be greater than 0', $result['errors']['quantity']);
    }

    public function testValidatePurchaseWithHighQuantity(): void
    {
        $data = [
            'product_id' => 1,
            'quantity' => 150
        ];

        $result = $this->validator->validatePurchase($data);

        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('quantity', $result['errors']);
        $this->assertEquals('Cannot purchase more than 100 items at once', $result['errors']['quantity']);
    }

    public function testValidatePurchaseWithMultipleErrors(): void
    {
        $data = [
            'product_id' => 0,
            'quantity' => 0
        ];

        $result = $this->validator->validatePurchase($data);

        $this->assertFalse($result['is_valid']);
        $this->assertCount(2, $result['errors']);
        $this->assertArrayHasKey('product_id', $result['errors']);
        $this->assertArrayHasKey('quantity', $result['errors']);
    }
}
