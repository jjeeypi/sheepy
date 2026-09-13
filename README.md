# Sheepy

Sheepy is a mobile-first apparel storefront built as a Web Programming midterm project. The application is designed around a calm editorial shopping experience and a structured PHP backend powered by MariaDB and PDO.

The repository currently contains the database schema, database connection layer, Phase 2 core framework, Phase 3 authentication, secure administrator product management, and Phase 4 customer catalogue browsing. Cart, checkout, and order features remain scaffolded for later development stages.

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
- [x] Customer registration and username/email login
- [x] Secure sessions, password hashing, and CSRF protection
- [x] Customer/admin access middleware and role-based redirects
- [x] Repeatable Phase 3 authentication smoke test
- [x] Administrator product create, edit, and guarded delete workflows
- [x] Validated product image upload and automatic alt text
- [x] Repeatable product-management smoke test
- [x] Storefront categories, product search, images, and product details
- [x] Repeatable Phase 4 catalogue browsing smoke test
- [ ] Cart and simulated checkout
- [ ] Customer orders, profile, and addresses
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

The administrator area provides secure product creation and management. Its visual design intentionally remains plain until the final UI/UX stage.

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

The repeatable catalogue seed under `database/seeds/` creates the Men, Women, Kids, and Accessories departments with their product-type subcategories. Import it after the schema when setting up a fresh database.

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

## Phase 3 Authentication

Phase 3 provides an intentionally unstyled HTML authentication flow so the final interface can be added later without mixing presentation work into the security layer:

- Customer registration with matching client-side and server-side rules
- Login using either username or email with a generic invalid-credentials response
- Prepared account queries and one-way password hashing
- Strict, HTTP-only, same-site sessions with idle expiry and identifier rotation
- CSRF protection for registration, login, and logout
- Guest, signed-in customer, and administrator route middleware
- Customer redirects to Home and administrator redirects to Dashboard
- Escaped dynamic HTML and private technical error logging

Run both completed framework tests with:

```powershell
C:\xampp\php\php.exe .\tests\CoreFrameworkSmokeTest.php
C:\xampp\php\php.exe .\tests\AuthenticationSmokeTest.php
```

Administrator credentials are local-only and are intentionally omitted from this document. Never paste account credentials into source files, documentation, screenshots, issues, or commits.

## Administrator Product Management

The administrator Dashboard provides separate Add Product and Manage Products actions. The current plain HTML workflow includes:

- Product name, SKU, description, price, stock, visibility, and optional category fields
- Required image upload during creation and optional image replacement during editing
- Server-verified JPG, PNG, WebP, and GIF uploads limited to 5 MB
- Randomized image filenames stored under the public product-upload directory
- Image alt text that automatically follows the product name
- Prepared product queries, database transactions, unique SKUs, and generated URL slugs
- Physical image cleanup after replacement or deletion
- Disabled delete controls and a transactional server-side deletion block for ordered products

Run the product-management smoke test with:

```powershell
C:\xampp\php\php.exe .\tests\ProductManagementSmokeTest.php
```

## Phase 4 Catalogue Browsing

Phase 4 turns the authenticated customer Home page into a functional, intentionally unstyled catalogue matching the supplied mockup's browsing structure:

- Men, Women, Kids, and Accessories department navigation
- Product-type browsing beneath each department
- New-arrival products on Home and a paginated all-products page
- Product search across names, descriptions, and SKUs
- Literal wildcard escaping and prepared category/search queries
- Product cards with uploaded images, stored alt text, price, category, and availability
- Product details with ordered image galleries, descriptions, stock, and category breadcrumbs
- Database-level exclusion of inactive and soft-deleted products from every customer query
- Friendly empty results and `404` pages without exposing technical errors

Run the Phase 4 database-backed smoke test with:

```powershell
C:\xampp\php\php.exe .\tests\CatalogBrowsingSmokeTest.php
```

## Development Notes

- Do not commit `.env`, database credentials, logs, uploaded products, or generated test artifacts.
- Keep user-uploaded product images under `public/uploads/products/`.
- Use `database/schema.sql` only for fresh databases and apply numbered migrations in order for existing databases.
- The scaffolded PHP feature files are intentionally incomplete and must not be presented as finished functionality until implemented and tested.
