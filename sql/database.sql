-- Адміністратори
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    telegram_id BIGINT UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Філії
CREATE TABLE branches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Шаблони інвентарю
CREATE TABLE inventory_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    equipment_type VARCHAR(100) NOT NULL,
    brand VARCHAR(100),
    model VARCHAR(100),
    requires_serial TINYINT DEFAULT 0,
    requires_inventory TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Інвентар кабінетів
CREATE TABLE room_inventory (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_telegram_id BIGINT NOT NULL,
    branch_id INT NOT NULL,
    room_number VARCHAR(50) NOT NULL,
    template_id INT NULL,
    equipment_type VARCHAR(100) NOT NULL,
    brand VARCHAR(100),
    model VARCHAR(100),
    serial_number VARCHAR(255) NULL,
    inventory_number VARCHAR(255) NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (template_id) REFERENCES inventory_templates(id),
    INDEX idx_branch_room (branch_id, room_number),
    INDEX idx_inventory_number (inventory_number),
    INDEX idx_serial_number (serial_number)
);

-- Заявки на ремонт
CREATE TABLE repair_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_telegram_id BIGINT NOT NULL,
    username VARCHAR(255) NULL,
    branch_id INT NOT NULL,
    room_number VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    phone VARCHAR(20) NULL,
    status ENUM('нова', 'в_роботі', 'виконана') DEFAULT 'нова',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    INDEX idx_status (status),
    INDEX idx_user (user_telegram_id),
    INDEX idx_created (created_at)
);

-- Заміни картриджів
CREATE TABLE cartridge_replacements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_telegram_id BIGINT NOT NULL,
    username VARCHAR(255) NULL,
    branch_id INT NOT NULL,
    room_number VARCHAR(50) NOT NULL,
    printer_inventory_id INT NULL,
    printer_info VARCHAR(500),
    cartridge_type VARCHAR(255) NOT NULL,
    replacement_date DATE NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (printer_inventory_id) REFERENCES room_inventory(id),
    INDEX idx_replacement_date (replacement_date),
    INDEX idx_branch_room (branch_id, room_number)
);

-- API токени
CREATE TABLE api_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    permissions JSON NOT NULL,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Стани користувачів
CREATE TABLE user_states (
    telegram_id BIGINT PRIMARY KEY,
    current_state VARCHAR(100) NULL,
    temp_data JSON NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);