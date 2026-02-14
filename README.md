# Products CRUD Application

A PHP-based web application for managing products with full CRUD (Create, Read, Update, Delete) operations.

## Features

- **List Products**: View all products in a table format
- **Create Product**: Add new products with details
- **View Product**: See detailed information about a specific product
- **Edit Product**: Update product information
- **Delete Product**: Remove products from the database
- **JSON Support**: Handle complex product data like variants, features, and FAQs

## Requirements

- PHP 7.4 or higher
- MySQL/MariaDB database
- Web server (Apache, Nginx, or PHP built-in server)

## Installation

1. **Create the database and import the SQL file:**
   ```sql
   CREATE DATABASE employee_training;
   ```
   Then import the `products.sql` file into your database.

2. **Update database credentials:**
   Edit `config/db.php` and update:
   - `$servername` (default: localhost)
   - `$username` (default: root)
   - `$password` (default: empty)
   - `$database` (default: employee_training)

3. **Place the application in your web root:**
   - For Apache: `htdocs` or `www` folder
   - For Nginx: configured web root

## Project Structure

```
.
├── config/
│   └── db.php              # Database connection
├── src/
│   └── Product.php         # Product class with database operations
├── public/
│   ├── index.php           # List all products
│   ├── create.php          # Create new product form
│   ├── edit.php            # Edit product form
│   ├── view.php            # View product details
│   ├── delete.php          # Delete product
│   └── style.css           # Styling
└── README.md
```

## Usage

### Running with PHP Built-in Server

```bash
cd public
php -S localhost:8000
```

Then visit `http://localhost:8000` in your browser.

### Running with Apache/Nginx

Configure your web server to point to the `public` directory and access via your domain/IP.

## File Descriptions

### config/db.php
Contains database connection configuration and creates the connection object.

### src/Product.php
Defines the `Product` class with methods:
- `getAll()`: Retrieve all products
- `getById($id)`: Get a specific product
- `create($data)`: Create a new product
- `update($id, $data)`: Update product information
- `delete($id)`: Delete a product

### public/index.php
Displays a paginated list of all products with action buttons for viewing, editing, and deleting.

### public/create.php
Provides a form to add new products with validation for JSON fields.

### public/edit.php
Allows updating existing product information.

### public/view.php
Shows detailed product information in a formatted view.

### public/delete.php
Handles product deletion with confirmation.

### public/style.css
Responsive CSS styling for the entire application.

## Database Schema

The `products` table includes:
- `product_id`: Unique identifier (Primary Key)
- `product_name`: Product name (Required)
- `product_type`: Category/type of product
- `brand`: Brand name
- `material`: Product material
- `price`: Product price
- `delivery`: JSON field for delivery information
- `variants`: JSON field for product variants
- `features`: JSON array of product features
- `faqs`: JSON array of frequently asked questions
- `created_at`: Timestamp of creation

## Security Notes

- All user inputs are validated and sanitized
- SQL prepared statements are used to prevent SQL injection
- HTML entities are escaped to prevent XSS attacks
- Delete operations require confirmation

## License

This project is open source and available for educational purposes.
