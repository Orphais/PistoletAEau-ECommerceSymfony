# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Symfony 7.3 e-commerce application ("Pistolets à eau" - Water Guns) with multi-language support (French/English). The application features product management, order processing, user accounts, and an admin dashboard.

## Core Technologies

- **Framework**: Symfony 7.3
- **PHP Version**: >=8.2
- **Database**: MySQL (configured for local development with root@127.0.0.1:3306/tiramisou)
- **ORM**: Doctrine ORM 3.5 with attribute-based entity mapping
- **Frontend**: Symfony AssetMapper with Stimulus/Turbo (UX components)
- **Testing**: PHPUnit 12.4
- **Fixtures**: Doctrine Fixtures with Faker

## Essential Commands

### Development
```bash
# Start Symfony server
php bin/console server:start

# Clear cache
php bin/console cache:clear

# Install assets
php bin/console assets:install

# Import JavaScript dependencies
php bin/console importmap:install
```

### Database
```bash
# Create database
php bin/console doctrine:database:create

# Run migrations
php bin/console doctrine:migrations:migrate

# Generate new migration after entity changes
php bin/console make:migration

# Load fixtures (will populate with fake data)
php bin/console doctrine:fixtures:load
```

### Testing
```bash
# Run all tests
./bin/phpunit

# Run specific test
./bin/phpunit tests/path/to/TestFile.php

# Run tests with coverage
./bin/phpunit --coverage-html var/coverage
```

### Code Generation
```bash
# Generate entity
php bin/console make:entity

# Generate controller
php bin/console make:controller

# Generate form
php bin/console make:form

# Generate repository method
php bin/console make:repository
```

## Architecture

### Entity Relationships

The application uses a normalized e-commerce data model:

- **User** - Authenticated users with roles (ROLE_USER, ROLE_ADMIN)
  - Has one Address
  - Has many Orders
  - Authentication via email/password with form_login

- **Product** - Items for sale
  - Belongs to one Category
  - Has many Images
  - Has many OrderItems
  - Uses ProductStatus enum (AVAILABLE, PREORDER, OUT_OF_STOCK)
  - Stock management integrated

- **Order** - Purchase orders
  - Belongs to one User (customer)
  - Has many OrderItems
  - Uses OrderStatus enum (IN_PREPARATION, SHIPPED, DELIVERED, CANCELLED)
  - Unique reference field
  - Tracks createdAt in Europe/Paris timezone

- **OrderItem** - Line items in orders
  - Belongs to one Order (as purchaseOrder)
  - Belongs to one Product
  - Stores productPrice at time of purchase (price history)
  - Tracks quantity

- **Category** - Product categorization
  - Has many Products

- **Image** - Product images
  - Belongs to one Product

- **Address** - Shipping/billing addresses
  - Linked to Users

### Repository Custom Methods

- **ProductRepository::getStockRatio()** - Returns stock statistics grouped by ProductStatus
- **OrderRepository::getTotalSalesByMonth()** - Aggregates delivered order sales by month

### Security & Authentication

- Security provider uses User entity with email property
- Form login at route 'security.login'
- Admin routes protected with isGranted('ROLE_ADMIN') checks in controllers
- Default admin account in fixtures: admin@tiramisou.com / password
- Regular users in fixtures: password

### Multi-language Support

- Routes support `_locale` parameter with 'fr|en' requirements
- Default locale: French (fr)
- Root path '/' redirects to '/fr'
- Translation files in translations/ directory
- Configure via config/packages/translation.yaml

### Admin Dashboard Features

Located in AdminController with /admin prefix:
- Dashboard with statistics (stock ratios, recent orders, sales by month)
- User management (list, view details)
- Product CRUD (list, create, edit, delete, view)
- Category CRUD
- Order management (list, view details)

All admin actions verify ROLE_ADMIN before execution.

### Pagination

Uses KnpPaginatorBundle throughout the application:
- Home page: 16 products per page
- Admin pages: 25 items per page
- Query parameter: `?page=N`

### Fixtures System

AppFixtures creates comprehensive test data:
- 10 categories with random names
- 50 products (1/3 out of stock, rest split between available/preorder)
- 1 admin user + 10 regular users (all password: 'password')
- 50 orders with various statuses
- 100 order items with realistic pricing
- Faker generates French locale data with Picsum images

## Development Notes

### Naming Conventions
- Use camelCase for all PHP code (as per project standards)
- Entity properties use camelCase
- Database columns use underscore_number_aware strategy (auto-converted)
- Controller methods follow Symfony conventions

### DateTime Handling
- All createdAt fields use DateTimeImmutable with 'Europe/Paris' timezone
- Set in entity constructors

### Enums
- ProductStatus: AVAILABLE, PREORDER, OUT_OF_STOCK
- OrderStatus: IN_PREPARATION, SHIPPED, DELIVERED, CANCELLED
- Located in src/Enum/

### Forms
- ProductType, CategoryType, AddressType, RegistrationType
- Located in src/Form/

### Twig Templates
- Base templates: base.html.twig, adminBase.html.twig
- Pages organized in templates/pages/ by controller
- Partials in templates/_partials/

## Environment Configuration

Default environment uses .env file with:
- APP_ENV=dev
- MySQL database: tiramisou on localhost:3306 (root user, no password)
- Messenger transport: Doctrine
- Mailer DSN: null (disabled)

Test environment uses .env.test with separate test database.
