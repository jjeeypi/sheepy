# Sheepy

Sheepy is a mobile-first apparel storefront built as a Web Programming midterm project. The application is designed around a calm editorial shopping experience and a structured PHP backend powered by MariaDB and PDO.

The repository currently contains the database schema, database connection layer, Phase 2 core framework, and MVC-style application structure. Storefront, authentication, checkout, order, and administration features are scaffolded for the next development stages.

## Project Status

- [x] MariaDB schema and migrations
- [x] Environment-based PDO connection
- [x] Native prepared-statement mode
- [x] Reusable transaction support
- [x] Safe database connection error response
- [x] PSR-4-style application autoloader
- [x] Dependency injection container with constructor autowiring
- [x] HTTP request, response, and routing foundation
- [x] Rewritten application URLs with secure response headers
- [x] Repeatable Phase 2 core framework smoke test
- [ ] Storefront and product search
- [ ] Registration, login, and secure sessions
- [ ] Cart and simulated checkout
- [ ] Customer orders, profile, and addresses
- [ ] Administrator product CRUD
- [ ] Automated unit and integration tests

## Planned Storefront Experience

The interface mockup defines the following customer experience:

- Editorial home page with category filters, new arrivals, and best sellers
- Product search and recently viewed products
- Product details with images, colours, sizes, reviews, and care information
- Wishlist and shopping bag interactions
- Quantity controls, shipping calculation, and checkout entry
- Customer profile, orders, saved addresses, and sign-out
- Responsive, mobile-first presentation using Sheepy's cream, stone, rust, olive, and ink design palette

The planned administrator area will provide secure product and catalogue management.

## Technology

- PHP 8.2 or newer
- MariaDB 10.4 or compatible MySQL server
- PDO with the `pdo_mysql` extension
- Apache, with XAMPP recommended for local development
- HTML, CSS, and JavaScript for the frontend

## Local Setup with XAMPP

### 1. Start the services

Open the XAMPP Control Panel and start both Apache and MySQL.

### 2. Create the environment file

From the project directory, copy the tracked template:

```powershell
Copy-Item .env.example .env
```

Use `.env.example` only as a guide, then enter the correct local values in `.env`. Never place private environment values in the README, source code, screenshots, issues, or commits. The `.env` file is ignored by Git.

### 3. Create and import the database

Using phpMyAdmin or another database administration tool, create the local database configured in `.env`, then import `database/schema.sql`.

If the tables already exist, do not import the schema again. Individual migration files are available under `database/migrations/` for incremental setup.

### 4. Open the application

Open the local application URL configured in your private `.env` file. A successful response confirms that Apache, PHP, PDO, and MariaDB are connected correctly.

## Database Design

The current schema contains nine InnoDB tables using `utf8mb4_unicode_ci`:

| Area | Tables | Purpose |
| --- | --- | --- |
| Accounts | `users`, `addresses` | Customer/admin accounts and saved delivery addresses |
| Catalogue | `categories`, `products`, `product_images` | Product organisation, inventory, and images |
| Shopping bag | `carts`, `cart_items` | Customer or guest carts and their items |
| Orders | `orders`, `order_items` | Confirmed/cancelled orders and immutable item snapshots |

Checkout is intentionally simulated for this academic project. Confirming checkout creates an order; no real payment is processed and there are no payment or coupon tables.

Before product-option and wishlist functionality is implemented, the schema will need to support colour/size variants and persistent wishlist records. This keeps the finished application consistent with the interface mockup.

## Project Structure

```text
sheepy/
|-- config/                 Application, database, CORS, and route configuration
|-- database/
|   |-- migrations/         Ordered table migrations
|   `-- schema.sql          Complete schema for a fresh installation
|-- public/                 Apache document root and public assets/uploads
|-- src/
|   |-- Controllers/        HTTP request coordination
|   |-- Core/               Router, request/response, container, and database code
|   |-- Exceptions/         Application-specific exceptions
|   |-- Middleware/         Authentication, authorization, CORS, and rate limiting
|   |-- Models/             Domain models
|   |-- Repositories/       Prepared database queries and persistence
|   |-- Services/           Reusable business rules
|   `-- Validators/         Server-side input validation
|-- storage/                Runtime logs and cache
|-- tests/                  Unit and integration tests
`-- views/                  PHP presentation templates
```

## Quality and Security Standards

The project is structured to meet the rubric's highest-quality criteria. Completed features should follow these rules:

- Keep controllers, services, repositories, validation, and views separate
- Use PDO prepared statements for every query containing external values
- Validate on both the client and server; never trust `$_GET` or `$_POST`
- Escape output according to its HTML, URL, or JavaScript context
- Hash passwords with `password_hash()` and verify them with `password_verify()`
- Regenerate session identifiers after login and enforce customer/admin authorization
- Protect state-changing forms with CSRF tokens
- Use transactions for checkout and other multi-table writes
- Log technical failures while showing users safe, friendly messages
- Preserve the mockup's consistent responsive layout and interaction patterns

## Current Connection Layer

Database settings are loaded from real environment variables first and `.env` second. The connection layer:

- Reuses one PDO connection per `Database` instance
- Throws exceptions for database errors
- Returns associative arrays by default
- Disables emulated prepared statements
- Avoids stringifying fetched numeric values
- Provides automatic commit/rollback transaction handling
- Wraps connection failures in a generic application exception

The public front controller catches uncaught failures, logs technical details privately, and returns a generic HTTP `500` response without exposing credentials or raw PDO errors.

## Phase 2 Core Framework

Phase 2 provides the reusable request lifecycle for later project phases:

- Namespace-based class loading from `src/`
- Container bindings, singleton services, and constructor autowiring
- Normalized request paths, input access, JSON parsing, and form method overrides
- Immutable HTML, JSON, text, redirect, and empty responses
- Route groups, constrained parameters, controller handlers, and middleware pipelines
- Correct `404`, `405`, `HEAD`, and `OPTIONS` behavior
- Central exception logging with safe browser-facing errors

Run the Phase 2 smoke test with the local PHP executable:

```powershell
C:\xampp\php\php.exe .\tests\CoreFrameworkSmokeTest.php
```

## Development Notes

- Do not commit `.env`, database credentials, logs, uploaded products, or generated test artifacts.
- Keep user-uploaded product images under `public/uploads/products/`.
- Use `database/schema.sql` only for fresh databases and apply numbered migrations in order for existing databases.
- The scaffolded PHP feature files are intentionally incomplete and must not be presented as finished functionality until implemented and tested.
