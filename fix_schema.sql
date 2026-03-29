USE college_store;
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS order_items, orders, products, users, password_resets;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name          VARCHAR(120)    NOT NULL,
    email         VARCHAR(180)    NOT NULL UNIQUE,
    password_hash VARCHAR(255)    NOT NULL,
    role          ENUM('student','admin') NOT NULL DEFAULT 'student',
    is_active     TINYINT(1)      NOT NULL DEFAULT 1,
    created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                  ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_email (email),
    INDEX idx_role  (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name        VARCHAR(200)    NOT NULL,
    description TEXT,
    price       DECIMAL(10,2)   NOT NULL CHECK (price >= 0),
    stock       INT UNSIGNED    NOT NULL DEFAULT 0,
    category    VARCHAR(80),
    image_url   VARCHAR(400),
    is_active   TINYINT(1)      NOT NULL DEFAULT 1,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_category (category),
    INDEX idx_active   (is_active),
    FULLTEXT INDEX ft_search (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id      INT UNSIGNED    NOT NULL,
    total_amount DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    status       ENUM('pending','confirmed','processing','completed','cancelled')
                                 NOT NULL DEFAULT 'pending',
    notes        TEXT,
    created_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                 ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_user_id   (user_id),
    INDEX idx_status    (status),
    INDEX idx_created   (created_at),
    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    order_id   INT UNSIGNED    NOT NULL,
    product_id INT UNSIGNED    NOT NULL,
    quantity   INT UNSIGNED    NOT NULL DEFAULT 1 CHECK (quantity > 0),
    unit_price DECIMAL(10,2)   NOT NULL CHECK (unit_price >= 0),
    subtotal   DECIMAL(10,2)   GENERATED ALWAYS AS (quantity * unit_price) STORED,
    PRIMARY KEY (id),
    INDEX idx_order_id   (order_id),
    INDEX idx_product_id (product_id),
    CONSTRAINT fk_items_order
        FOREIGN KEY (order_id)   REFERENCES orders   (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_items_product
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (name, email, password_hash, role) VALUES
(
    'Store Admin',
    'admin@collegestore.local',
    '$2y$10$bif7HvD.SW.s6yERrCoNVuY0LwcDaEXNPpQmXLWe9HAi1H0A674PS',
    'admin'
);

INSERT INTO products (name, description, price, stock, category, image_url) VALUES
('Engineering Drawing Kit',  'Full set: compass, set squares, scales', 349.00, 50, 'Stationery', '../assets/images/engineering_drawing_kit.png'),
('Graph Paper Pad (A4)',      '100-sheet pad, 5mm grid',               49.00,  200,'Stationery', '../assets/images/graph_paper_pad.png'),
('Scientific Calculator',    'Casio FX-991EX, dual-power',            1299.00, 30, 'Electronics', '../assets/images/scientific_calculator.png'),
('Lab Coat (White)',         'Cotton, unisex, sizes S–XXL',           449.00,  80, 'Lab Supplies', '../assets/images/lab_coat.png'),
('USB Pen Drive 32GB',       'USB 3.0, branded',                      399.00,  60, 'Electronics', '../assets/placeholder.jpg');
