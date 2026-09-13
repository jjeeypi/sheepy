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