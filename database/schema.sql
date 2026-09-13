-- =====================================================
-- Full database schema (combined from migrations/)
-- Run this for a fresh install, or apply migrations
-- individually in numeric order for incremental setups.
--
-- Note: no payments/coupons tables. "Checkout" is
-- simulated — confirming an order just inserts a row
-- into `orders` with status='confirmed'; cancelling
-- simply discards the pending checkout (no row written).
-- =====================================================

-- ==== 001_create_users_table.sql ====
CREATE TABLE users (
    user_id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(150)        NOT NULL,
    email           VARCHAR(191)        NOT NULL,
    password_hash   VARCHAR(255)        NOT NULL,
    phone           VARCHAR(30)         NULL,
    role            ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_users_email (email),
    UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==== 002_create_addresses_table.sql ====
CREATE TABLE addresses (
    address_id      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED     NOT NULL,
    label           VARCHAR(50)         NULL,           -- e.g. "Home", "Office"
    recipient_name  VARCHAR(150)        NOT NULL,
    phone           VARCHAR(30)         NULL,
    line1           VARCHAR(255)        NOT NULL,
    line2           VARCHAR(255)        NULL,
    city            VARCHAR(100)        NOT NULL,
    state           VARCHAR(100)        NULL,
    postal_code     VARCHAR(20)         NOT NULL,
    country         VARCHAR(100)        NOT NULL,
    is_default      TINYINT(1)          NOT NULL DEFAULT 0,
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    KEY idx_addresses_user_id (user_id),
    CONSTRAINT fk_addresses_user
        FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==== 003_create_categories_table.sql ====
CREATE TABLE categories (
    category_id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_category_id  BIGINT UNSIGNED     NULL,       -- self-reference for subcategories
    name                VARCHAR(150)        NOT NULL,
    slug                VARCHAR(170)        NOT NULL,
    created_at          DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_categories_slug (slug),
    KEY idx_categories_parent_category_id (parent_category_id),
    CONSTRAINT fk_categories_parent
        FOREIGN KEY (parent_category_id) REFERENCES categories(category_id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==== 004_create_products_table.sql ====
CREATE TABLE products (
    product_id      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id     BIGINT UNSIGNED     NULL,
    sku             VARCHAR(64)         NOT NULL,
    name            VARCHAR(200)        NOT NULL,
    slug            VARCHAR(220)        NOT NULL,
    description     TEXT                NULL,
    price           DECIMAL(10,2)       NOT NULL,
    stock_quantity  INT UNSIGNED        NOT NULL DEFAULT 0,
    is_active       TINYINT(1)          NOT NULL DEFAULT 1,
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME            NULL,           -- soft delete, keeps order history intact

    UNIQUE KEY uq_products_sku (sku),
    UNIQUE KEY uq_products_slug (slug),
    KEY idx_products_category_id (category_id),
    KEY idx_products_is_active (is_active),
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories(category_id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==== 005_create_product_images_table.sql ====
CREATE TABLE product_images (
    product_image_id   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id          BIGINT UNSIGNED     NOT NULL,
    url                  VARCHAR(500)        NOT NULL,
    alt_text             VARCHAR(255)        NULL,
    sort_order           SMALLINT UNSIGNED   NOT NULL DEFAULT 0,
    created_at           DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,

    KEY idx_product_images_product_id (product_id),
    CONSTRAINT fk_product_images_product
        FOREIGN KEY (product_id) REFERENCES products(product_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==== 006_create_carts_and_cart_items_tables.sql ====
CREATE TABLE carts (
    cart_id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED     NULL,           -- nullable to support guest carts (see session_token)
    session_token   VARCHAR(100)        NULL,           -- for guest carts, matched via cookie/localStorage
    status          ENUM('active', 'converted', 'abandoned') NOT NULL DEFAULT 'active',
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    KEY idx_carts_user_id (user_id),
    KEY idx_carts_session_token (session_token),
    CONSTRAINT fk_carts_user
        FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cart_items (
    cart_item_id    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id         BIGINT UNSIGNED     NOT NULL,
    product_id      BIGINT UNSIGNED     NOT NULL,
    quantity        INT UNSIGNED        NOT NULL DEFAULT 1,
    unit_price      DECIMAL(10,2)       NOT NULL,       -- price at time added, re-validated at checkout
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_cart_product (cart_id, product_id),   -- one row per product per cart; update quantity instead of duplicating
    KEY idx_cart_items_cart_id (cart_id),
    KEY idx_cart_items_product_id (product_id),
    CONSTRAINT fk_cart_items_cart
        FOREIGN KEY (cart_id) REFERENCES carts(cart_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_cart_items_product
        FOREIGN KEY (product_id) REFERENCES products(product_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==== 007_create_orders_and_order_items_tables.sql ====
CREATE TABLE orders (
    order_id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id             BIGINT UNSIGNED     NOT NULL,
    order_number        VARCHAR(30)         NOT NULL,       -- human-facing reference, e.g. ORD-20260912-0001
    status              ENUM('confirmed', 'cancelled') NOT NULL DEFAULT 'confirmed',
    subtotal            DECIMAL(10,2)       NOT NULL,
    shipping_total      DECIMAL(10,2)       NOT NULL DEFAULT 0.00,
    tax_total           DECIMAL(10,2)       NOT NULL DEFAULT 0.00,
    grand_total         DECIMAL(10,2)       NOT NULL,

    -- shipping address snapshot (don't FK to addresses — user may edit/delete that address later)
    shipping_name        VARCHAR(150)        NOT NULL,
    shipping_phone       VARCHAR(30)         NULL,
    shipping_line1       VARCHAR(255)        NOT NULL,
    shipping_line2       VARCHAR(255)        NULL,
    shipping_city        VARCHAR(100)        NOT NULL,
    shipping_state       VARCHAR(100)        NULL,
    shipping_postal_code VARCHAR(20)         NOT NULL,
    shipping_country     VARCHAR(100)        NOT NULL,

    placed_at           DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at          DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_orders_order_number (order_number),
    KEY idx_orders_user_id (user_id),
    KEY idx_orders_status (status),
    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
    order_item_id   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        BIGINT UNSIGNED     NOT NULL,
    product_id      BIGINT UNSIGNED     NULL,           -- nullable: product may be deleted later, snapshot below survives
    product_name    VARCHAR(200)        NOT NULL,       -- snapshot at purchase time
    product_sku     VARCHAR(64)         NOT NULL,       -- snapshot at purchase time
    unit_price      DECIMAL(10,2)       NOT NULL,       -- snapshot at purchase time
    quantity        INT UNSIGNED        NOT NULL,
    line_total      DECIMAL(10,2)       NOT NULL,       -- unit_price * quantity, stored for fast reads

    KEY idx_order_items_order_id (order_id),
    KEY idx_order_items_product_id (product_id),
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id) REFERENCES orders(order_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product
        FOREIGN KEY (product_id) REFERENCES products(product_id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

