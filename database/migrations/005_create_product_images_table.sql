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
