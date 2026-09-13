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
