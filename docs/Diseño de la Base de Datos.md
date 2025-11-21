# Diseño de la Base de Datos
## Tablas
#### users
| **Atributo**      | **Tipo de Dato (MySQL)**                                         | **Descripción / Restricciones**                                                     |
| ----------------- | ---------------------------------------------------------------- | ----------------------------------------------------------------------------------- |
| **id**            | `INT`<br>`AUTO_INCREMENT`<br>`**PRIMARY KEY**`                   | Identificador numérico único para cada usuario.                                     |
| **name**          | `VARCHAR(100)`<br>`NOT NULL`                                     | Nombre del usuario (Requerido por `RF_REG002`).                                     |
| **lastname**      | `VARCHAR(100)`<br>`NOT NULL`                                     | Apellido del usuario (Requerido por `RF_REG002`).                                   |
| **email**         | `VARCHAR(255)`<br>`NOT NULL UNIQUE`                              | Usado para el login. **Debe ser ÚNICO** (`RF_REG005`).                              |
| **phone_number**  | `VARCHAR(20)`<br>`NOT NULL`                                      | Teléfono/Whatsapp para contacto del Vendedor (`RF_REG002`).                         |
| **password**      | `VARCHAR(255)`<br>`NOT NULL`                                     | **IMPORTANTE:** Debe almacenarse como un HASH (ej. Bcrypt), nunca como texto plano. |
| **status**        | `ENUM('Activo', 'Inactivo')`<br>`NOT NULL`<br>`DEFAULT 'Activo'` | Usado por el Vendedor para "desactivar" cuentas (`RF_AUS009`).                      |
| **user_role_id**  | `TINYINT`<br>`NOT NULL`<br>`**FOREIGN KEY**`                     | Se conecta con la tabla `user_roles(id)`. Define si es Cliente o Vendedor.          |
| **register_date** | `TIMESTAMP`<br>`DEFAULT CURRENT_TIMESTAMP`                       | (Buena práctica) Registra cuándo se creó la cuenta.                                 |

#### user_roles
| **Atributo** | **Tipo de Dato (MySQL)**                           | **Descripción / Restricciones**                                          |
| ------------ | -------------------------------------------------- | ------------------------------------------------------------------------ |
| **id**       | `TINYINT`<br>`AUTO_INCREMENT`<br>`**PRIMARY KEY**` | Usamos `TINYINT` (entero muy pequeño) porque solo tendremos 2 o 3 roles. |
| **name**     | `VARCHAR(50) NOT NULL UNIQUE`                      | El nombre único del rol (ej. "Cliente", "Vendedor").                     |

| id  | name                   |
| --- | ---------------------- |
| 0   | Administrador/Vendedor |
| 1   | Cliente                |

#### components
| **Atributo**          | **Tipo de Dato (MySQL)**                                         | **Descripción / Restricciones**                                                                                                                                            |
| --------------------- | ---------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **id**                | `INT`<br>`AUTO_INCREMENT`<br>`**PRIMARY KEY**`                   | Identificador único para cada componente.                                                                                                                                  |
| **component_type_id** | `TINYINT`<br>`NOT NULL`<br>`**FOREIGN KEY**`                     | Que se conecta con la tabla `component_types(id)`. Define qué _tipo_ de pieza es.                                                                                          |
| **name**              | `VARCHAR(255)`<br>`NOT NULL`                                     | El nombre comercial del producto (Datos en español: "SSD Kingston A400 1TB").                                                                                              |
| **price**             | `DECIMAL(10, 2)`<br>`NOT NULL`                                   | El precio de venta del componente. `(10, 2)` permite hasta 99,999,999.99.                                                                                                  |
| **stock**             | `INT`<br>`NOT NULL`<br>`DEFAULT 0`                               | La cantidad de unidades disponibles. El stock se actualiza manual o por scraping (`RF_ACP006`).                                                                            |
| **tier**              | `INT`<br>`NOT NULL`<br>`DEFAULT 0`                               | **El valor clave para DANIEL.** El Vendedor asigna este valor (ej. 1-10) para que el sistema DANIEL pueda traducir las necesidades del cliente (ej. "1TB" -> `gama >= 5`). |
| **status**            | `ENUM('Activo', 'Inactivo')`<br>`NOT NULL`<br>`DEFAULT 'Activo'` | Usado por el Vendedor para "desactivar" un componente y que no aparezca en las búsquedas (`RF_ACP007`).                                                                    |

#### component_types
| **Atributo**  | **Tipo de Dato (MySQL)**                        | **Descripción / Restricciones**                                                                        |
| ------------- | ----------------------------------------------- | ------------------------------------------------------------------------------------------------------ |
| **id**        | `TINYINT` `AUTO_INCREMENT`<br>`**PRIMARY KEY**` | Usamos `TINYINT` porque habrá muy pocos tipos.                                                         |
| **type_name** | `VARCHAR(100) NOT NULL UNIQUE`                  | El nombre único de la categoría (Datos en español: "CPU", "Gabinete", "Almacenamiento", "Ventilador"). |

| id  | name            |
| --- | --------------- |
| 1   | Procesador      |
| 2   | Tarjeta Gráfica |
| 3   | Memoria RAM     |
| 4   | Tarjeta Madre   |
| 5   | Almacenamiento  |
| 6   | Disipador       |
| 7   | Gabinete        |
| 8   | Fuente de Poder |
| 9   | Ventilador      |
| 10  | Conectividad    |
| 11  | Periférico      |


#### functional_kits
| **Atributo**   | **Tipo de Dato (MySQL)**                                         | **Descripción / Restricciones**                                                              |
| -------------- | ---------------------------------------------------------------- | -------------------------------------------------------------------------------------------- |
| **id**         | `INT`<br>`AUTO_INCREMENT`<br>`**PRIMARY KEY**`                   | Identificador único del kit funcional.                                                       |
| **name**       | `VARCHAR(255)`<br>`NOT NULL`                                     | Nombre descriptivo (ej. "Kit Gaming Alfa", "Workstation Esencial").                          |
| **base_price** | `DECIMAL(10, 2)`<br>`NOT NULL`                                   | El precio final del kit (Suma de componentes + Monto de Servicio), calculado en `RF_APC011`. |
| **cpu_tier**   | `INT`<br>`NOT NULL`                                              | La gama abstracta de CPU (ej. 1-10) que el Sistema asigna a este kit (`RF_APC012`).          |
| **gpu_tier**   | `INT`<br>`NOT NULL`                                              | La gama abstracta de GPU (`RF_APC012`).                                                      |
| **ram_tier**   | `INT`<br>`NOT NULL`                                              | La gama abstracta de RAM (`RF_APC012`).                                                      |
| **status**     | `ENUM('Activo', 'Inactivo')`<br>`NOT NULL`<br>`DEFAULT 'Activo'` | Para que el Vendedor pueda ocultar este kit de las búsquedas de DANIEL.                      |

#### structural_kits
| **Atributo**         | **Tipo de Dato (MySQL)**                                         | **Descripción / Restricciones**                                                           |
| -------------------- | ---------------------------------------------------------------- | ----------------------------------------------------------------------------------------- |
| **id**               | `INT`<br>`AUTO_INCREMENT`<br>`**PRIMARY KEY**`                   | Identificador único del kit estructural.                                                  |
| **name**             | `VARCHAR(255)`<br>`NOT NULL`                                     | Nombre descriptivo (ej. "Combo ATX Silencioso", "Gabinete Mini-ITX Pro").                 |
| **structural_price** | `DECIMAL(10, 2)`<br>`NOT NULL`                                   | El precio final del combo (Gabinete + Fans + Servicio), calculado en `RF_APC011`.         |
| **case_tier**        | `INT`<br>`NOT NULL`                                              | La gama abstracta de Gabinete (ej. 1-10, donde 1="Sencillo", 10="Premium") (`RF_APC012`). |
| **status**           | `ENUM('Activo', 'Inactivo')`<br>`NOT NULL`<br>`DEFAULT 'Activo'` | Para que el Vendedor pueda ocultar este combo de las búsquedas de DANIEL.                 |

#### functional_kits_x_components
| **Atributo**          | **Tipo de Dato (MySQL)**                        | **Descripción / Restricciones**                                                 |
| --------------------- | ----------------------------------------------- | ------------------------------------------------------------------------------- |
| **functional_kit_id** | `INT`<br>`NOT NULL`<br>`**FOREIGN KEY**`        | Se conecta con `functional_kits(id)`.                                           |
| **component_id**      | `INT`<br>`NOT NULL`<br>`**FOREIGN KEY**`        | Se conecta con `components(id)`.                                                |
| **quantity**          | `INT`<br>`NOT NULL`<br>`DEFAULT 1`              | La cantidad de esta pieza en el kit (ej. 2 para 2x8GB RAM) (`RF_APC007`).       |
|                       | `PRIMARY KEY (functional_kit_id, component_id)` | `**PRIMARY KEY**` compuesta para asegurar que no se repita el mismo componente. |

#### structural_kits_x_components
| **Atributo**          | **Tipo de Dato (MySQL)**                        | **Descripción / Restricciones**                                           |
| --------------------- | ----------------------------------------------- | ------------------------------------------------------------------------- |
| **structural_kit_id** | `INT`<br>`NOT NULL`<br>`**FOREIGN KEY**`        | Se conecta con `structural_kits(id)`                                      |
| **component_id**      | `INT`<br>`NOT NULL`<br>`**FOREIGN KEY**`        | Se conecta con `components(id)`.                                          |
| **quantity**          | `INT`<br>`NOT NULL`<br>`DEFAULT 1`              | La cantidad de esta pieza (ej. 1 Gabinete, 3 Ventiladores) (`RF_APC007`). |
|                       | `PRIMARY KEY (structural_kit_id, component_id)` | `**PRIMARY KEY**` compuesta.                                              |

#### daniel_map_needs
| **Atributo**          | **Tipo de Dato (MySQL)**                        | **Descripción / Restricciones**                                                           |
| --------------------- | ----------------------------------------------- | ----------------------------------------------------------------------------------------- |
| **id**                | `TINYINT` `AUTO_INCREMENT`<br>`**PRIMARY KEY**` | El ID único de la regla (ej. "N01").                                                      |
| **name**              | `VARCHAR(100)`<br>`NOT NULL`                    | El texto que el cliente ve en el formulario (ej. "AAA", "Indies").                        |
| **super_category_id** | `TINYINT`<br>`NOT NULL`<br>`**FOREIGN KEY**`    | **Atributo Clave.** Le dice a DANIEL a qué sección pertenece la necesidad (ej. "Gaming"). |
| **cpu_tier**          | `INT`<br>`NOT NULL`<br>`DEFAULT 0`              | El requisito de Gama de CPU base.                                                         |
| **gpu_tier**          | `INT`<br>`NOT NULL`<br>`DEFAULT 0`              | El requisito de Gama de GPU base.                                                         |
| **ram_tier**          | `INT`<br>`NOT NULL`<br>`DEFAULT 0`              | El requisito de Gama de RAM base.                                                         |
| **description**       | `TEXT NULL`                                     | (Opcional) Una descripción interna de lo que implica esta necesidad.                      |

| id_category | name                                      | super_category_id | cpu_tier | gpu_tier | ram_tier |
| ----------- | ----------------------------------------- | ----------------- | -------- | -------- | -------- |
|             | General                                   | 0                 | 0        | 0        | 0        |
|             | Indies                                    | 1                 | 1        | 1        | 1        |
|             | E-Sports                                  | 1                 | 2        | 2        | 1        |
|             | AAA                                       | 1                 | 3        | 3        | 2        |
|             | Edición de Audio y<br>Composición Músical | 2                 | 2        | 0        | 2        |
|             | Edición de Imagen                         | 2                 | 2        | 1        | 2        |
|             | Edición de Video                          | 2                 | 4        | 4        | 4        |
|             | Modelado y Animación 3D                   | 2                 | 4        | 4        | 4        |
|             | Desarrollo de Software                    | 3                 | 3        | 1        | 2        |
|             | Ciencia de Datos                          | 3                 | 4        | 5        | 4        |
|             | Arquitectura e Ingeniería                 | 3                 | 4        | 3        | 4        |
|             | Desarrollo de Videojuegos                 | 3                 | 2        | 2        | 2        |

#### daniel_map_personalization
| **Atributo**          | **Tipo de Dato (MySQL)**                        | **Descripción / Restricciones**                                                                                           |
| --------------------- | ----------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------- |
| **id**                | `TINYINT` `AUTO_INCREMENT`<br>`**PRIMARY KEY**` | El ID único de la regla.                                                                                                  |
| **name**              | `VARCHAR(100)`<br>`NOT NULL`                    | El texto que el cliente ve en el formulario (ej. "1TB SSD", "Gabinete Mediano", "Solo WIFI").                             |
| **super_category_id** | `TINYINT`<br>`NOT NULL`<br>`**FOREIGN KEY**`    | **Atributo Clave.** Le dice a DANIEL a qué componente aplicar la gama (ej. 'Almacenamiento', 'Gabinete', "Conectividad"). |
| **tier**              | `INT`<br>`NOT NULL`<br>`DEFAULT 0`              | El requisito de gama para la `super_category` especificada.                                                               |
| **description**       | `TEXT NULL`                                     | (Opcional) Una descripción interna.                                                                                       |

| id  | name    | super_category_id | tier |
| --- | ------- | ----------------- | ---- |
|     | 512GB   | 1                 | 1    |
|     | 1TB     | 1                 | 2    |
|     | 2TB     | 1                 | 3    |
|     | 4TB     | 1                 | 4    |
|     | Pequeño | 2                 | 1    |
|     | Mediano | 2                 | 2    |
|     | Grande  | 2                 | 3    |

#### daniel_map_boosters
| **Atributo**      | **Tipo de Dato (MySQL)**                        | **Descripción / Restricciones**                                                    |
| ----------------- | ----------------------------------------------- | ---------------------------------------------------------------------------------- |
| **id**            | `TINYINT` `AUTO_INCREMENT`<br>`**PRIMARY KEY**` | El ID único de la regla.                                                           |
| **name**          | `VARCHAR(100)`<br>`NOT NULL`                    | El texto que el cliente ve en el formulario (ej. "Básico", "Mejorado", "Premium"). |
| **cpu_tier_plus** | `INT`<br>`NOT NULL`<br>`DEFAULT 0`              | La gama que se _suma_ al requisito de CPU.                                         |
| **gpu_tier_plus** | `INT`<br>`NOT NULL`<br>`DEFAULT 0`              | La gama que se _suma_ al requisito de GPU.                                         |
| **ram_tier_plus** | `INT`<br>`NOT NULL`<br>`DEFAULT 0`              | La gama que se _suma_ al requisito de RAM.                                         |
| **description**   | `TEXT NULL`                                     | (Opcional) Una descripción interna.                                                |

| id  | name        | cpu_tier_plus | gpu_tier_plus | ram_tier_plus |
| --- | ----------- | ------------- | ------------- | ------------- |
|     | Óptima      | 0             | 0             | 0             |
|     | Mejorada    | 1             | 2             | 1             |
|     | Premium     | 2             | 3             | 2             |
|     | Entusiasta  | 0             | 0             | 0             |
|     | Emprededor  | 1             | 1             | 1             |
|     | Profesional | 2             | 2             | 2             |

#### daniel_map_super_categories
| **Atributo** | **Tipo de Dato (MySQL)**                           | **Descripción / Restricciones**                                                                    |
| ------------ | -------------------------------------------------- | -------------------------------------------------------------------------------------------------- |
| **id**       | `TINYINT`<br>`AUTO_INCREMENT`<br>`**PRIMARY KEY**` | El ID único de la regla.                                                                           |
| **name**     | `VARCHAR(100)`<br>`NOT NULL`<br>`UNIQUE`           | El texto que el cliente ve en el formulario (ej. "Videojuegos", "Arquitectura", "Almacenamiento"). |

| id  | name           |
| --- | -------------- |
|     | General        |
|     | Almacenamiento |
|     | Gabinete       |
|     | Conectividad   |
|     | Videojuegos    |
|     | Audiovisuales  |
|     | Workstation    |

#### orders
| **Atributo**    | **Tipo de Dato (MySQL)**                                                                                                                                                                                                                                               | **Descripción / Restricciones**                                                                                                                |
| --------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------- |
| **id**          | `INT`<br>`AUTO_INCREMENT`<br>`**PRIMARY KEY**`                                                                                                                                                                                                                         | El número de pedido único (ej. Pedido #1001).                                                                                                  |
| **user_id**     | `INT`<br>`NOT NULL`<br>`**FOREIGN KEY**`                                                                                                                                                                                                                               | Se conecta con `Usuarios(id)`. El cliente que realizó el pedido.                                                                               |
| **total_price** | `DECIMAL(10, 2)`<br>`NOT NULL`                                                                                                                                                                                                                                         | El precio final total (calculado por DANIEL en `RF_CPC008`) que el cliente aceptó pagar.                                                       |
| **status**      | `ENUM(`<br>`'Pedido Recibido',`<br>`'Esperando Pago',`<br>`'Preparando Componentes',`<br>`'En Ensamblaje',`<br>`'Configuración y Pruebas',`<br>`'Listo para Entrega',`<br>`'Completado',`<br>`'Cancelado'`<br>`)`<br><br>`NOT NULL`<br>`DEFAULT 'Pedido Recibido'`<br> | El estado actual del pedido. (ej. 'Pedido Recibido', 'En Ensamblaje', 'Completado'). Este es el campo que el Vendedor actualiza (`RF_APD009`). |
| **created_at**  | `TIMESTAMP`<br>`DEFAULT CURRENT_TIMESTAMP`                                                                                                                                                                                                                             | (Buena práctica) Registra la fecha y hora exactas en que se realizó el pedido.                                                                 |

#### order_x_components
| **Atributo**          | **Tipo de Dato (MySQL)**                 | **Descripción / Restricciones**                                                                                                                                                                          |
| --------------------- | ---------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **order_id**          | `INT`<br>`NOT NULL`<br>`**FOREIGN KEY**` | Se conecta con `Orders(id)`.                                                                                                                                                                             |
| **component_id**      | `INT`<br>`NOT NULL`<br>`**FOREIGN KEY**` | Se conecta con `Components(id)`.                                                                                                                                                                         |
| **quantity**          | `INT`<br>`NOT NULL`<br>`DEFAULT 1`       | La cantidad de esta pieza específica en este pedido (ej. 2 para RAM).                                                                                                                                    |
| **price_at_purchase** | `DECIMAL(10, 2)`<br>`NOT NULL`           | **Snapshot del Precio.** Almacena el precio del componente _en el momento exacto_ en que se hizo el pedido. Esto evita que los cambios futuros de precio (`RF-COMP-006`) afecten a los pedidos antiguos. |
|                       | `PRIMARY KEY (order_id, component_id)`   | `**PRIMARY KEY**` compuesta. Asegura que el Pedido #1001 no pueda tener el "Componente X" listado dos veces; nos obliga a usar `quantity`.                                                               |
