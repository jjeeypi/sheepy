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
