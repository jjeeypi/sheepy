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
