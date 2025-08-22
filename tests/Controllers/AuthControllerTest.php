<?php

namespace VendingMachine\Tests\Controllers;

use PHPUnit\Framework\TestCase;
use VendingMachine\Controllers\AuthController;
use VendingMachine\Models\User;
use VendingMachine\Auth\SessionManager;
use Mockery;

class AuthControllerTest extends TestCase
{
    private AuthController $controller;
    private User $mockUserModel;
    private SessionManager $mockSessionManager;

    protected function setUp(): void
    {
        $this->mockUserModel = Mockery::mock(User::class);
        $this->mockSessionManager = Mockery::mock(SessionManager::class);

        $this->controller = new AuthController();
        
        // Use reflection to inject mocks
        $reflection = new \ReflectionClass($this->controller);
        
        $userProperty = $reflection->getProperty('userModel');
        $userProperty->setAccessible(true);
        $userProperty->setValue($this->controller, $this->mockUserModel);
        
        $sessionProperty = $reflection->getProperty('sessionManager');
        $sessionProperty->setAccessible(true);
        $sessionProperty->setValue($this->controller, $this->mockSessionManager);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testLoginWithValidCredentials(): void
    {
        $loginData = ['username' => 'testuser', 'password' => 'password123'];
        $mockUser = ['id' => 1, 'username' => 'testuser', 'email' => 'test@example.com', 'role' => 'user'];

        $this->mockUserModel->shouldReceive('authenticate')
            ->with('testuser', 'password123')
            ->once()
            ->andReturn($mockUser);

        $this->mockSessionManager->shouldReceive('login')
            ->with($mockUser)
            ->once();

        $result = $this->controller->login($loginData);

        $this->assertTrue($result['success']);
        $this->assertEquals($mockUser, $result['user']);
        $this->assertEquals('Login successful', $result['message']);
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $loginData = ['username' => 'testuser', 'password' => 'wrongpassword'];

        $this->mockUserModel->shouldReceive('authenticate')
            ->with('testuser', 'wrongpassword')
            ->once()
            ->andReturn(null);

        $result = $this->controller->login($loginData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals('Invalid username or password', $result['errors']['credentials']);
    }

    public function testLoginWithMissingUsername(): void
    {
        $loginData = ['password' => 'password123'];

        $result = $this->controller->login($loginData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals('Username is required', $result['errors']['username']);
    }

    public function testLoginWithMissingPassword(): void
    {
        $loginData = ['username' => 'testuser'];

        $result = $this->controller->login($loginData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals('Password is required', $result['errors']['password']);
    }

    public function testRegisterWithValidData(): void
    {
        $registerData = [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'confirm_password' => 'password123'
        ];

        $this->mockUserModel->shouldReceive('findByUsername')
            ->with('newuser')
            ->once()
            ->andReturn(null);

        $this->mockUserModel->shouldReceive('findByEmail')
            ->with('newuser@example.com')
            ->once()
            ->andReturn(null);

        $this->mockUserModel->shouldReceive('create')
            ->with([
                'username' => 'newuser',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'role' => 'user'
            ])
            ->once()
            ->andReturn(1);

        $mockUser = ['id' => 1, 'username' => 'newuser', 'email' => 'newuser@example.com', 'role' => 'user'];
        $this->mockUserModel->shouldReceive('findById')
            ->with(1)
            ->once()
            ->andReturn($mockUser);

        $this->mockSessionManager->shouldReceive('login')
            ->with($mockUser)
            ->once();

        $result = $this->controller->register($registerData);

        $this->assertTrue($result['success']);
        $this->assertEquals($mockUser, $result['user']);
        $this->assertEquals('Registration successful', $result['message']);
    }

    public function testRegisterWithExistingUsername(): void
    {
        $registerData = [
            'username' => 'existinguser',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'confirm_password' => 'password123'
        ];

        $this->mockUserModel->shouldReceive('findByUsername')
            ->with('existinguser')
            ->once()
            ->andReturn(['id' => 1, 'username' => 'existinguser']);

        $result = $this->controller->register($registerData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals('Username already exists', $result['errors']['username']);
    }

    public function testRegisterWithExistingEmail(): void
    {
        $registerData = [
            'username' => 'newuser',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'confirm_password' => 'password123'
        ];

        $this->mockUserModel->shouldReceive('findByUsername')
            ->with('newuser')
            ->once()
            ->andReturn(null);

        $this->mockUserModel->shouldReceive('findByEmail')
            ->with('existing@example.com')
            ->once()
            ->andReturn(['id' => 1, 'email' => 'existing@example.com']);

        $result = $this->controller->register($registerData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals('Email already exists', $result['errors']['email']);
    }

    public function testRegisterWithInvalidUsername(): void
    {
        $registerData = [
            'username' => 'ab', // Too short
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'confirm_password' => 'password123'
        ];

        $result = $this->controller->register($registerData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals('Username must be at least 3 characters long', $result['errors']['username']);
    }

    public function testRegisterWithInvalidEmail(): void
    {
        $registerData = [
            'username' => 'newuser',
            'email' => 'invalid-email',
            'password' => 'password123',
            'confirm_password' => 'password123'
        ];

        $result = $this->controller->register($registerData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals('Please enter a valid email address', $result['errors']['email']);
    }

    public function testRegisterWithShortPassword(): void
    {
        $registerData = [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => '12345', // Too short
            'confirm_password' => '12345'
        ];

        $result = $this->controller->register($registerData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals('Password must be at least 6 characters long', $result['errors']['password']);
    }

    public function testRegisterWithMismatchedPasswords(): void
    {
        $registerData = [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'confirm_password' => 'differentpassword'
        ];

        $result = $this->controller->register($registerData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals('Passwords do not match', $result['errors']['confirm_password']);
    }

    public function testRegisterHandlesException(): void
    {
        $registerData = [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'confirm_password' => 'password123'
        ];

        $this->mockUserModel->shouldReceive('findByUsername')
            ->with('newuser')
            ->once()
            ->andReturn(null);

        $this->mockUserModel->shouldReceive('findByEmail')
            ->with('newuser@example.com')
            ->once()
            ->andReturn(null);

        $this->mockUserModel->shouldReceive('create')
            ->andThrow(new \Exception('Database error'));

        $result = $this->controller->register($registerData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertStringContainsString('Database error', $result['errors']['general']);
    }

    public function testLogout(): void
    {
        $this->mockSessionManager->shouldReceive('logout')
            ->once();

        $result = $this->controller->logout();

        $this->assertTrue($result['success']);
        $this->assertEquals('Logout successful', $result['message']);
    }

    public function testGetCurrentUser(): void
    {
        $mockUser = ['id' => 1, 'username' => 'testuser', 'role' => 'user'];

        $this->mockSessionManager->shouldReceive('getCurrentUser')
            ->once()
            ->andReturn($mockUser);

        $result = $this->controller->getCurrentUser();

        $this->assertEquals($mockUser, $result);
    }
}
