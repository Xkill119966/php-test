# PHP Vending Machine System

A robust PHP application for managing a vending machine system with product management, inventory tracking, purchase transactions, and user authentication.

## Features

- **Product Management**: Complete CRUD operations with validation
- **Inventory Tracking**: Real-time stock management and updates
- **Purchase Transactions**: Secure purchase process with transaction logging
- **User Authentication**: Session-based authentication with role-based access control
- **RESTful API**: JWT-secured API endpoints for external integrations
- **Form Validation**: Comprehensive server-side and client-side validation
- **Unit Testing**: PHPUnit tests with mocking and dependency injection
- **Attribute Routing**: Clean, SEO-friendly URLs with parameter support
- **Pagination & Sorting**: Advanced product listing with search capabilities
- **Security**: CSRF protection, SQL injection prevention, XSS protection

## Database Schema

### Tables

1. **products**
   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
   - name (VARCHAR(255), NOT NULL)
   - price (DECIMAL(10,2), NOT NULL)
   - quantity_available (INT, NOT NULL DEFAULT 0)
   - created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
   - updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)

2. **users**
   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
   - username (VARCHAR(100), UNIQUE, NOT NULL)
   - email (VARCHAR(255), UNIQUE, NOT NULL)
   - password_hash (VARCHAR(255), NOT NULL)
   - role (ENUM('admin', 'user'), DEFAULT 'user')
   - created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)

3. **transactions**
   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
   - user_id (INT, FOREIGN KEY REFERENCES users(id))
   - product_id (INT, FOREIGN KEY REFERENCES products(id))
   - quantity (INT, NOT NULL)
   - unit_price (DECIMAL(10,2), NOT NULL)
   - total_amount (DECIMAL(10,2), NOT NULL)
   - transaction_date (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd vending-machine-php
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Set up MySQL database**
   ```sql
   CREATE DATABASE vending_machine;
   mysql -u root -p vending_machine < database/schema.sql
   ```

4. **Configure environment**
   - Copy `config/app.php` and set your database credentials
   - Set JWT_SECRET environment variable

5. **Configure web server**
   - Point document root to `public/` directory
   - Ensure mod_rewrite is enabled (Apache) or proper Nginx configuration

6. **Set permissions**
   ```bash
   chmod -R 755 public/
   chmod -R 644 config/
   ```

7. **Test the application**
   - Visit your application URL
   - Login with admin/admin123

For detailed installation instructions, see [INSTALLATION.md](INSTALLATION.md).

## Testing

The application includes comprehensive unit tests using PHPUnit and Mockery for dependency injection and mocking.

### Running Tests
```bash
# Run all tests
composer test

# Run tests with coverage report
composer test-coverage
```

### Test Coverage
- **Controllers**: ProductsController, AuthController, ApiController
- **Validation**: ProductValidator with comprehensive validation rules
- **Models**: Database operations and business logic
- **Authentication**: Session management and role-based access control

### Test Features
- Dependency injection for isolated testing
- Mocking of external dependencies (database, sessions)
- Edge case testing for validation rules
- Error handling and exception testing

## API Endpoints

### Public Endpoints
- `GET /api/products` - List all products with pagination and sorting
- `GET /api/products/{id}` - Get specific product details
- `POST /api/auth/login` - User authentication (returns JWT token)
- `POST /api/auth/register` - User registration (returns JWT token)

### Protected Endpoints (Require JWT Authentication)
- `POST /api/products` - Create new product (Admin only)
- `PUT /api/products/{id}` - Update product (Admin only)
- `DELETE /api/products/{id}` - Delete product (Admin only)
- `POST /api/purchase` - Purchase a product (Authenticated users)

## Web Routes

### Public Routes
- `GET /` - Homepage with available products
- `GET /products` - Product listing with pagination and sorting
- `GET /products/{id}` - Product details page
- `GET /login` - User login form
- `GET /register` - User registration form

### Protected Routes
- `GET /purchase/{id}` - Product purchase page (requires login)
- `GET /admin/products` - Product management (Admin only)
- `GET /admin/products/create` - Create product form (Admin only)
- `GET /admin/products/{id}/edit` - Edit product form (Admin only)
