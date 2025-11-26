-- =============================================
-- SEEDS.SQL - Versión Final (Simplificada)
-- =============================================

-- 1. CATÁLOGOS DEL SISTEMA
-- ---------------------------------------------

-- Roles (IDs corregidos: empiezan en 1)
INSERT INTO user_roles (id, name) VALUES
(1, 'Administrador/Vendedor'),
(2, 'Cliente');

-- Tipos de Componente
INSERT INTO component_types (id, type_name) VALUES
(1, 'Procesador'), (2, 'Tarjeta Gráfica'), (3, 'Memoria RAM'),
(4, 'Tarjeta Madre'), (5, 'Almacenamiento'), (6, 'Disipador'),
(7, 'Gabinete'), (8, 'Fuente de Poder'), (9, 'Ventilador'),
(10, 'Conectividad'), (11, 'Periférico');

-- Super Categorías DANIEL
INSERT INTO daniel_map_super_categories (id, name) VALUES
(1, 'General'), (2, 'Almacenamiento'), (3, 'Gabinete'),
(4, 'Conectividad'), (5, 'Videojuegos'), (6, 'Audiovisuales'),
(7, 'Workstation');


-- 2. LÓGICA DANIEL (REGLAS)
-- ---------------------------------------------

-- Necesidades Base
INSERT INTO daniel_map_needs (id, name, super_category_id, cpu_tier, gpu_tier, ram_tier) VALUES
(1, 'General', 1, 0, 0, 0),
-- Gaming
(2, 'Indies', 5, 1, 1, 1),
(3, 'E-Sports', 5, 2, 2, 1),
(4, 'AAA', 5, 3, 3, 2),
-- Audiovisuales
(5, 'Edición de Audio', 6, 2, 0, 2),
(6, 'Edición de Imagen', 6, 2, 1, 2),
(7, 'Edición de Video', 6, 4, 4, 4),
(8, 'Modelado 3D', 6, 4, 4, 4),
-- Workstation
(9, 'Desarrollo de Software', 7, 3, 1, 2),
(10, 'Ciencia de Datos', 7, 4, 5, 4),
(11, 'Arquitectura', 7, 3, 4, 4),
(12, 'Desarrollo de Videojuegos', 7, 2, 2, 2);

-- Personalizaciones (Lista Única)
INSERT INTO daniel_map_personalization (id, name, super_category_id, tier) VALUES
(1, '512GB SSD', 2, 1),
(2, '1TB SSD', 2, 2),
(3, '2TB SSD', 2, 3),
(4, 'Pequeño (ITX)', 3, 1),
(5, 'Mediano (ATX)', 3, 2),
(6, 'Grande (E-ATX)', 3, 3);

-- Boosters (Lista Única)
INSERT INTO daniel_map_boosters (id, name, cpu_tier_plus, gpu_tier_plus, ram_tier_plus) VALUES
(1, 'Óptima (Base)', 0, 0, 0),
(2, 'Mejorada (1440p)', 1, 2, 1),
(3, 'Premium (4K/FPS)', 2, 3, 2),
(4, 'Entusiasta', 0, 0, 0),
(5, 'Emprendedor', 1, 1, 1),
(6, 'Profesional', 2, 2, 2);

-- Conexión Needs <-> Boosters (Tabla Pivote)
INSERT INTO needs_x_boosters (need_id, booster_id) VALUES
-- Gaming (AAA, Esports, Indies) usan 1, 2, 3
(2,1), (2,2), (2,3),
(3,1), (3,2), (3,3),
(4,1), (4,2), (4,3),
-- Workstation/Audio usan 4, 5, 6
(7,4), (7,5), (7,6),
(10,4), (10,5), (10,6),
-- Desarrollo de Videojuegos (ID 12) usa los boosters de trabajo
(12,4), (12,5), (12,6);


-- 3. INVENTARIO REAL (COMPONENTES)
-- ---------------------------------------------
INSERT INTO components (id, component_type_id, name, price, stock, tier, status) VALUES
-- CPUs
(101, 1, 'Intel Core i3-12100F', 110.00, 10, 2, 'Activo'),
(102, 1, 'AMD Ryzen 7 7700X', 350.00, 10, 4, 'Activo'),
(103, 1, 'AMD Ryzen 9 7900X', 550.00, 5, 6, 'Activo'),
-- GPUs
(201, 2, 'NVIDIA RTX 3050 8GB', 250.00, 10, 2, 'Activo'),
(202, 2, 'NVIDIA RTX 4060 8GB', 350.00, 10, 4, 'Activo'),
(203, 2, 'NVIDIA RTX 4080 16GB', 1200.00, 5, 6, 'Activo'),
-- RAMs
(301, 3, '16GB DDR4 3200MHz', 40.00, 20, 2, 'Activo'),
(302, 3, '32GB DDR5 5600MHz', 100.00, 20, 4, 'Activo'),
(303, 3, '64GB DDR5 6000MHz', 220.00, 10, 6, 'Activo'),
-- Almacenamiento (Para personalización)
(501, 5, 'SSD 512GB SATA', 30.00, 50, 1, 'Activo'),
(502, 5, 'SSD 1TB NVMe Gen3', 60.00, 50, 2, 'Activo'),
(503, 5, 'SSD 2TB NVMe Gen4', 120.00, 30, 3, 'Activo'),
-- Gabinetes (Para kits estructurales)
(701, 7, 'Case Mini Tower Básico', 40.00, 20, 1, 'Activo'),
(702, 7, 'Case Mid Tower Flujo Aire', 90.00, 20, 2, 'Activo'),
(703, 7, 'Case Full Tower Premium', 180.00, 10, 3, 'Activo'),
-- Ventiladores
(901, 9, 'Ventilador 120mm Negro', 10.00, 100, 1, 'Activo'),
(902, 9, 'Ventilador 120mm RGB', 25.00, 100, 2, 'Activo');


-- 4. KITS PRECONFIGURADOS (LOS PRODUCTOS)
-- ---------------------------------------------

-- A) Kits Funcionales (Base)
INSERT INTO functional_kits (id, name, base_price, cpu_tier, gpu_tier, ram_tier) VALUES
-- Kit 1: Arranque (Tier 2) - Cumple con Desarrollo Videojuegos (2,2,2)
(1, 'Kit de Arranque', 450.00, 2, 2, 2),
-- Kit 2: Pro Balanceado (Tier 4)
(2, 'Kit Pro Balanceado', 900.00, 4, 4, 4),
-- Kit 3: Entusiasta (Tier 6)
(3, 'Kit Entusiasta', 2100.00, 6, 6, 6);

INSERT INTO functional_kits_x_components (functional_kit_id, component_id, quantity) VALUES
(1, 101, 1), (1, 201, 1), (1, 301, 1),
(2, 102, 1), (2, 202, 1), (2, 302, 1),
(3, 103, 1), (3, 203, 1), (3, 303, 1);

-- B) Kits Estructurales (Gabinete + Fans)
INSERT INTO structural_kits (id, name, structural_price, case_tier) VALUES
(1, 'Combo Básico Compacto', 60.00, 1),
(2, 'Combo Airflow RGB', 140.00, 2),
(3, 'Combo Ultra Tower', 250.00, 3);

-- Relación Kits Estructurales <-> Componentes
INSERT INTO structural_kits_x_components (structural_kit_id, component_id, quantity) VALUES
(1, 701, 1), (1, 901, 2),
(2, 702, 1), (2, 902, 3),
(3, 703, 1), (3, 902, 6);