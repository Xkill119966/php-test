<?php

namespace VendingMachine\Validation;

class ProductValidator
{
    public function validate(array $data, ?int $id = null): array
    {
        $errors = [];

        // Validate name
        if (empty($data['name'])) {
            $errors['name'] = 'Product name is required';
        } elseif (strlen($data['name']) < 2) {
            $errors['name'] = 'Product name must be at least 2 characters long';
        } elseif (strlen($data['name']) > 255) {
            $errors['name'] = 'Product name must not exceed 255 characters';
        }

        // Validate price
        if (!isset($data['price'])) {
            $errors['price'] = 'Price is required';
        } elseif (!is_numeric($data['price'])) {
            $errors['price'] = 'Price must be a valid number';
        } elseif ($data['price'] <= 0) {
            $errors['price'] = 'Price must be greater than 0';
        } elseif ($data['price'] > 9999999.99) {
            $errors['price'] = 'Price cannot exceed $9,999,999.99';
        }

        // Validate quantity_available
        if (!isset($data['quantity_available'])) {
            $errors['quantity_available'] = 'Quantity is required';
        } elseif (!is_numeric($data['quantity_available'])) {
            $errors['quantity_available'] = 'Quantity must be a valid number';
        } elseif ($data['quantity_available'] < 0) {
            $errors['quantity_available'] = 'Quantity cannot be negative';
        } elseif ($data['quantity_available'] > 999999) {
            $errors['quantity_available'] = 'Quantity cannot exceed 999,999';
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors
        ];
    }

    public function validatePurchase(array $data): array
    {
        $errors = [];

        if (!isset($data['product_id'])) {
            $errors['product_id'] = 'Product ID is required';
        } elseif (!is_numeric($data['product_id']) || $data['product_id'] <= 0) {
            $errors['product_id'] = 'Invalid product ID';
        }

        if (!isset($data['quantity'])) {
            $errors['quantity'] = 'Quantity is required';
        } elseif (!is_numeric($data['quantity']) || $data['quantity'] <= 0) {
            $errors['quantity'] = 'Quantity must be greater than 0';
        } elseif ($data['quantity'] > 100) {
            $errors['quantity'] = 'Cannot purchase more than 100 items at once';
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
