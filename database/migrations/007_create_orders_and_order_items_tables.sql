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
