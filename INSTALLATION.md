# Vending Machine System - Installation Guide

## Prerequisites

- PHP 8.0 or higher
- MySQL 5.7 or higher (or MariaDB 10.2+)
- Composer
- Web server (Apache/Nginx)
- mod_rewrite enabled (for Apache)

## Installation Steps

### 1. Clone the Repository

```bash
git clone <repository-url>
cd vending-machine-php
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Database Setup

#### Create Database
```sql
CREATE DATABASE vending_machine;
```

#### Import Schema
```bash
mysql -u root -p vending_machine < database/schema.sql
```

The schema includes:
- Users table with admin/user roles
- Products table with initial products (Coke, Pepsi, Water)
- Transactions table for purchase history
- Default admin user (username: admin, password: admin123)

### 4. Configuration

#### Database Configuration
Edit `config/database.php` or set environment variables:
```bash
export DB_HOST=localhost
export DB_NAME=vending_machine
export DB_USER=your_username
export DB_PASS=your_password
```

#### JWT Secret
Set a secure JWT secret:
```bash
export JWT_SECRET=your-super-secret-jwt-key-change-this-in-production
```

### 5. Web Server Configuration

#### Apache Configuration
Ensure mod_rewrite is enabled and the `.htaccess` file is in the `public/` directory.

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/vending-machine-php/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 6. Set Permissions

```bash
chmod -R 755 public/
chmod -R 644 config/
```

### 7. Test Installation

Visit your application URL. You should see the vending machine homepage.

## Default Credentials

- **Admin User:**
  - Username: `admin`
  - Password: `admin123`
  - Role: `admin`

## Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage
```

## API Testing

### Authentication
```bash
# Login to get JWT token
curl -X POST http://your-domain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'
```

### Using JWT Token
```bash
# Get products
curl -X GET http://your-domain.com/api/products \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify database credentials in `config/database.php`
   - Ensure MySQL service is running
   - Check database exists

2. **404 Errors**
   - Ensure mod_rewrite is enabled
   - Check `.htaccess` file is in `public/` directory
   - Verify web server configuration

3. **Permission Errors**
   - Check file permissions
   - Ensure web server can read project files

4. **JWT Errors**
   - Verify JWT_SECRET is set
   - Check token expiration

### Logs
Check your web server error logs for detailed error information.

## Security Considerations

1. **Change Default Passwords**
   - Update admin password after first login
   - Use strong, unique passwords

2. **Environment Variables**
   - Store sensitive data in environment variables
   - Never commit `.env` files to version control

3. **JWT Security**
   - Use a strong, random JWT secret
   - Consider token refresh mechanisms
   - Implement proper token expiration

4. **Database Security**
   - Use dedicated database user with minimal privileges
   - Enable SSL connections if possible
   - Regular security updates

## Production Deployment

1. **Environment**
   - Set `APP_ENV=production`
   - Disable debug mode
   - Use HTTPS

2. **Performance**
   - Enable OPcache
   - Use Redis for sessions (optional)
   - Implement caching strategies

3. **Monitoring**
   - Set up error logging
   - Monitor database performance
   - Implement health checks

## Support

For issues and questions:
1. Check the troubleshooting section
2. Review error logs
3. Verify configuration
4. Check system requirements

## License

This project is licensed under the MIT License.
