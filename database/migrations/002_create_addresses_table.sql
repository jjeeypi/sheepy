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
