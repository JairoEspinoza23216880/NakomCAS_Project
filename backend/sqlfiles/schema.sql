-- Tabla: user_roles
-- Define los roles de usuario en el sistema (ej. administrador, cliente)
CREATE TABLE user_roles (
    id TINYINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Tabla: component_types
-- Tipos de componentes disponibles (ej. CPU, GPU, RAM, etc.)
CREATE TABLE component_types (
    id TINYINT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(100) NOT NULL UNIQUE
);

-- Tabla: daniel_map_super_categories
-- Categorías generales para mapeo de necesidades y personalización
CREATE TABLE daniel_map_super_categories (
    id TINYINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);


-- Tabla: users
-- Usuarios registrados en el sistema
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone_number VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo',
    user_role_id TINYINT NOT NULL,
    register_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_role_id) REFERENCES user_roles(id)
);

-- Tabla: components
-- Componentes individuales disponibles para kits y pedidos
CREATE TABLE components (
    id INT AUTO_INCREMENT PRIMARY KEY,
    component_type_id TINYINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    tier INT NOT NULL DEFAULT 0,
    status ENUM('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo',
    
    FOREIGN KEY (component_type_id) REFERENCES component_types(id)
);

-- Tabla: functional_kits
-- Kits funcionales (ej. conjunto de CPU, GPU, RAM) para computadoras
CREATE TABLE functional_kits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    base_price DECIMAL(10, 2) NOT NULL,
    cpu_tier INT NOT NULL,
    gpu_tier INT NOT NULL,
    ram_tier INT NOT NULL,
    status ENUM('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo'
);

-- Tabla: structural_kits
-- Kits estructurales (ej. gabinete, fuente, etc.)
CREATE TABLE structural_kits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    structural_price DECIMAL(10, 2) NOT NULL,
    case_tier INT NOT NULL,
    status ENUM('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo'
);

-- Tabla: orders
-- Pedidos realizados por los usuarios
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM(
        'Pedido Recibido',
        'Esperando Pago',
        'Preparando Componentes',
        'En Ensamblaje',
        'Configuración y Pruebas',
        'Listo para Entrega',
        'Completado',
        'Cancelado'
    ) NOT NULL DEFAULT 'Pedido Recibido',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id)
);


-- Tabla: daniel_map_needs
-- Mapea necesidades de usuario a categorías y niveles de componentes
CREATE TABLE daniel_map_needs (
    id TINYINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    super_category_id TINYINT NOT NULL,
    cpu_tier INT NOT NULL DEFAULT 0,
    gpu_tier INT NOT NULL DEFAULT 0,
    ram_tier INT NOT NULL DEFAULT 0,
    description TEXT NULL,
    
    FOREIGN KEY (super_category_id) REFERENCES daniel_map_super_categories(id)
);

-- Tabla: daniel_map_personalization
-- Opciones de personalización para kits, asociadas a categorías
CREATE TABLE daniel_map_personalization (
    id TINYINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    super_category_id TINYINT NOT NULL,
    tier INT NOT NULL DEFAULT 0,
    description TEXT NULL,
    
    FOREIGN KEY (super_category_id) REFERENCES daniel_map_super_categories(id)
);

-- Tabla: daniel_map_boosters
-- Mejoras adicionales que aumentan los tiers de componentes
CREATE TABLE daniel_map_boosters (
    id TINYINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    cpu_tier_plus INT NOT NULL DEFAULT 0,
    gpu_tier_plus INT NOT NULL DEFAULT 0,
    ram_tier_plus INT NOT NULL DEFAULT 0,
    description TEXT NULL
);

-- Tabla: needs_x_boosters
-- Relaciona necesidades con boosters aplicables
CREATE TABLE needs_x_boosters (
    need_id TINYINT NOT NULL,
    booster_id TINYINT NOT NULL,
    
    PRIMARY KEY (need_id, booster_id),
    FOREIGN KEY (need_id) REFERENCES daniel_map_needs(id),
    FOREIGN KEY (booster_id) REFERENCES daniel_map_boosters(id)
);


-- Tabla: functional_kits_x_components
-- Relaciona kits funcionales con sus componentes y cantidades
CREATE TABLE functional_kits_x_components (
    functional_kit_id INT NOT NULL,
    component_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    
    PRIMARY KEY (functional_kit_id, component_id),
    FOREIGN KEY (functional_kit_id) REFERENCES functional_kits(id),
    FOREIGN KEY (component_id) REFERENCES components(id)
);

-- Tabla: structural_kits_x_components
-- Relaciona kits estructurales con sus componentes y cantidades
CREATE TABLE structural_kits_x_components (
    structural_kit_id INT NOT NULL,
    component_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    
    PRIMARY KEY (structural_kit_id, component_id),
    FOREIGN KEY (structural_kit_id) REFERENCES structural_kits(id),
    FOREIGN KEY (component_id) REFERENCES components(id)
);

-- Tabla: order_x_components
-- Relaciona pedidos con los componentes adquiridos, cantidad y precio en el momento de la compra
CREATE TABLE order_x_components (
    order_id INT NOT NULL,
    component_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price_at_purchase DECIMAL(10, 2) NOT NULL,
    
    PRIMARY KEY (order_id, component_id),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (component_id) REFERENCES components(id)
);