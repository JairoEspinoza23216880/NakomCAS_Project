# Contrato de API

## Sesiones

### Registro de Usuario
- **Endpoint:** `POST /api/register`
- **Objetivo:** Crear un nuevo usuario en la base de datos. 
- **Protección:** Pública (No requiere Token).
#### Frontend envía
``` json
{
  "name": "Juan",
  "lastname": "Pérez",
  "email": "juan.perez@nakom.com",
  "phone_number" : "9861530690",
  "password": "mi_contraseña_segura" 
}
```

#### Backend Responde

| **Estado HTTP**     | **Caso**          | **Respuesta Común (JSON)**                                                                           |
| ------------------- | ----------------- | ---------------------------------------------------------------------------------------------------- |
| **201 Created**     | Éxito             | `{ "success": true, "message": "Registro exitoso. Procede a iniciar sesión." }`                      |
| **409 Conflict**    | Correo ya existe  | `{ "success": false, "message": "El correo electrónico ya está registrado." }`                       |

### Inicio de Sesión
- **Endpoint:** `POST /api/login` 
- **Objetivo:** Validar credenciales y obtener el Token de Acceso. 
- **Protección:** Pública (No requiere Token).

#### Frontend envía
``` json
{
  "email": "juan.perez@nakom.com",
  "password": "mi_contraseña_segura"
}
```

#### Backend responde
| **Estado HTTP**      | **Caso**               | **Respuesta Común (JSON)**                                                                                           |
| -------------------- | ---------------------- | -------------------------------------------------------------------------------------------------------------------- |
| **200 OK**           | Éxito                  | `{ "success": true, "token": "eyJhbGciOiJIUzI1NiIsInR...", "user": { "id": 10, "name": "Juan", "role": "client" } }` |
| **401 Unauthorized** | Credenciales inválidas | `{ "success": false, "message": "Credenciales inválidas o usuario no encontrado." }`                                 |
``` json
{
  "success": true,
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6Ikp1YW4iLCJpYXQiOjE2MjMxMjY2MTMsImV4cCI6MTYyMzEyNzIxM30.M_K_kE-P0T-C2sJ7H_l3m2B...",
  "user": {
    "id": 10,
    "name": "Juan",
    "role": "client"
  }
}
```

### Validación de Sesión
- **Endpoint:** `GET /api/me` 
- **Objetivo:** Verificar si el Token guardado en el Frontend es válido y obtener los datos del usuario actual.
- **Protección:** **Requiere Token** (Debe pasar por el Middleware).

#### Frontend  envía (Request Header)

| **Cabecera**      | **Valor**                          |
| ----------------- | ---------------------------------- |
| **Authorization** | `Bearer [TOKEN_OBTENIDO_EN_LOGIN]` |
#### Backend responde
| **Estado HTTP**      | **Caso**                | **Respuesta Común (JSON)**                                                        |
| -------------------- | ----------------------- | --------------------------------------------------------------------------------- |
| **200 OK**           | Token válido            | `{ "id": 10, "name": "Juan", "email": "juan.perez@nakom.com", "role": "client" }` |
| **401 Unauthorized** | Token inválido/expirado | `{ "success": false, "message": "Token no provisto o inválido." }`                |
**Nota Importante:** El Backend usa el Token para saber quién es el usuario y no necesita consultar la Base de Datos. Si el Token pasa el Middleware, el Backend sabe que es válido y solo necesita devolver los datos.

## Configurador 

### Búsqueda y Cálculo DANIEL
**Endpoint:** `POST /api/configurator/search` 
**Objetivo:** Recibir todas las elecciones del formulario único, ejecutar la lógica `MAX()` + `SUM()`, combinar los Kits y devolver la PC ideal (o un error). 
**Protección:** Pública (No requiere Token, aunque lo puede requerir en el futuro).

#### Frontend envía
``` json
{
  "selected_needs_n_boosters": [(1,0),(5,2)], // Array de Tuplas : IDs de las Necesidades junto a su Booster
  "selected_personalization_ids": [1, 3, 2], // IDs de personalización (ej. 2TB SSD, Gabinete Grande, Conectividad WIFI)
}
```

#### Backend responde
| **Estado HTTP**   | **Caso**          | **Respuesta Común (JSON)**                                                                                            |
| ----------------- | ----------------- | --------------------------------------------------------------------------------------------------------------------- |
| **200 OK**        | PC Encontrada     | `{ "success": true, "total_price": 1789.99, "build": { ... } }`                                                       |
| **404 Not Found** | No se encontró PC | `{ "success": false, "message": "No pudimos encontrar una combinación viable dentro del presupuesto y requisitos." }` |
``` json
{
  "success": true,
  "total_price": 1789.99,
  "build": {
    "functional_kit": {
      "id": 5, 
      "name": "Kit Gaming Pro (Tiers 4/4/4)",
      "base_price": 1500.00,
      "components_list": ["Ryzen 7 7700X", "RTX 4070", "32GB DDR5 6000MHz", "..."]
    },
    "structural_kit": {
      "id": 8,
      "name": "Combo Airflow RGB",
      "price": 180.00,
      "components_list": ["Gabinete XL", "5x Ventiladores RGB"]
    },
    "personalization_components": [
      { "id": 31, "name": "SSD 2TB NVMe Gen4", "price": 109.99 }
    ]
  }
}
```
El equipo de Frontend debe asegurarse de que sus formularios y selectores envíen **únicamente los `id` numéricos** que definimos en las tablas, no los nombres (ej. `1`, no `"Gaming AAA"`).

### Hacer el Pedido
- **Endpoint:** `POST /api/orders` 
- **Objetivo:** Convertir la selección de DANIEL en un pedido real. "Apalana" los kits y guarda el snapshot de los componentes. 
- **Protección:** **Requiere Token**.
#### Frontend envía
``` json
{
  "functional_kit_id": 5,          // ID del kit funcional elegido
  "structural_kit_id": 8,          // ID del kit estructural elegido
  "personalization_ids": [31, 42], // IDs de componentes extra (SSD, etc.)
  "total_price": 1789.99           // Precio final confirmado por el cliente
}
```
#### Backend responde
| **Estado HTTP**     | **Caso**       | **Respuesta Común (JSON)**                                                                                |
| ------------------- | -------------- | --------------------------------------------------------------------------------------------------------- |
| **201 Created**     | Éxito          | `{ "success": true, "message": "Pedido #1005 creado.", "order_id": 1005 }`                                |
| **400 Bad Request** | Error de Stock | `{ "success": false, "message": "Lo sentimos, el componente 'RTX 4060' se agotó mientras confirmabas." }` |

## Contacto
### Enviar Mensaje a Contacto
- **Endpoint:** `POST /api/contact` 
- **Objetivo:** Recibir el mensaje del cliente y enviarlo por email al administrador. 
- **Protección:** **Requiere Token** (Header `Authorization`).

#### Frontend envía
| **Cabecera**      | **Valor**                    |
| ----------------- | ---------------------------- |
| **Authorization** | `Bearer [TOKEN_DEL_USUARIO]` |
*(Nota: El Frontend **NO** necesita enviar el nombre ni el correo del usuario en el JSON. El Backend extrae esos datos de forma segura desde el Token o la Base de Datos, evitando que un usuario se haga pasar por otro).*

``` json
{
  "subject": "Duda sobre mi pedido #1024",
  "message": "Hola, quisiera saber si es posible cambiar el gabinete de mi pedido antes de que lo ensamblen."
}
```

#### Backend responde

|**Estado HTTP**|**Caso**|**Respuesta Común (JSON)**|
|---|---|---|
|**200 OK**|Enviado|`{ "success": true, "message": "Tu mensaje ha sido enviado. Te responderemos pronto." }`|
|**400 Bad Request**|Datos vacíos|`{ "success": false, "message": "El asunto y el mensaje son obligatorios." }`|
|**500 Server Error**|Fallo SMTP|`{ "success": false, "message": "Hubo un error al enviar el correo. Intenta más tarde." }`|
#### Flujo de Lógica (Backend)
1. **Verificar Token:** Confirma quién es el usuario (ej. "Juan Pérez", `juan@correo.com`).
2. **Validar:** Revisa que `subject` y `message` no estén vacíos.
3. **Configurar Mailer:** Usa una librería (como **PHPMailer**, que se instala fácil con Composer) para conectarse al Gmail/Outlook del negocio.
4. **Enviar:**
    - **De:** `juan@correo.com` (El cliente)
    - **Para:** `ventas@nakomcas.com` (El Vendedor)
    - **Asunto:** `[Contacto Web] Duda sobre mi pedido...`
5. **Responder al Frontend:** Devuelve el JSON de éxito.

#### Nota para el Frontend
El Frontend **NO** necesita un endpoint especial para esto en la página de contacto.
1. Al cargar la página `/contacto`, el Frontend usa los datos que **ya tiene guardados** (del Login o de `/api/me`).
2. Pinta el nombre y correo en `inputs` con el atributo `readonly` (solo lectura).
3. Solo deja editar "Asunto" y "Mensaje".

## Mis Pedidos
### Listar Mis Pedidos
- **Endpoint:** `GET /api/orders` 
- **Objetivo:** Obtener la lista histórica de pedidos del usuario para la tabla principal. 
- **Protección:** **Requiere Token**.

#### Frontend envía
| **Cabecera**      | **Valor**                    |
| ----------------- | ---------------------------- |
| **Authorization** | `Bearer [TOKEN_DEL_USUARIO]` |
#### Backend responde
``` json
[
  {
    "id": 1005,
    "date": "2023-10-27 14:30:00",  // created_at formateado
    "status": "Pedido Recibido",    // El valor del ENUM
    "total_price": 1789.99,
    "summary_name": "Kit Gaming Pro + Combo Airflow" // Un nombre generado por el backend para mostrar en la lista
  },
  {
    "id": 1002,
    "date": "2023-09-15 09:15:00",
    "status": "Completado",
    "total_price": 1200.00,
    "summary_name": "Kit Básico"
  }
]
```

### Ver detalle de un Pedido
- **Endpoint:** `GET /api/orders/{id}` 
- **Objetivo:** Obtener la lista completa de componentes ("Snapshot") de un pedido específico. 
- **Protección:** **Requiere Token**.

**Regla de Seguridad Backend:** El Backend debe verificar que el pedido `{id}` pertenezca al usuario del Token. Si el Usuario A intenta ver el pedido del Usuario B, debe devolver `403 Forbidden`
#### Frontend envía
`GET /api/orders/1005` (Header con Token).

#### Backend responde
``` json
{
  "id": 1005,
  "status": "En Ensamblaje",
  "date": "2023-10-27 14:30:00",
  "total_price": 1789.99,
  "components": [
    {
      "name": "AMD Ryzen 7 7700X",
      "type": "CPU",
      "price_at_purchase": 350.00, // El precio histórico
      "quantity": 1
    },
    {
      "name": "NVIDIA RTX 4070",
      "type": "GPU",
      "price_at_purchase": 600.00,
      "quantity": 1
    },
    {
      "name": "Kingston Fury 32GB DDR5",
      "type": "RAM",
      "price_at_purchase": 120.00,
      "quantity": 1
    },
    {
      "name": "Gabinete Corsair 4000D",
      "type": "Gabinete",
      "price_at_purchase": 90.00,
      "quantity": 1
    }
    // ... resto de componentes
  ]
}
```

### Visualización para el Frontend
1. **La Lista:** Usa el endpoint **2** para llenar una tabla simple.
    - Columnas: `#Pedido`, `Fecha`, `Resumen`, `Total`, `Estado` (con colores según el status).
2. **El Modal:** Cuando hacen clic en una fila, usan el endpoint **3**.    
    - Muestran una lista detallada tipo "Factura" con todos los componentes.
    - No hay botones de acción (cancelar/editar), solo información.

## ADMIN Usuarios
**Middleware de Seguridad (Backend):** Todos estos endpoints deben tener un chequeo previo:
1. ¿El Token es válido?
2. ¿`user_role_id` en el Token (o BD) es igual a "Vendedor"?

### Listar todos los Usuarios
- **Endpoint:** `GET /api/admin/users` 
- **Objetivo:** Poblar la tabla principal de gestión de usuarios. 
- **Respuesta:** Debe incluir tanto clientes activos como inactivos.

#### Backend responde
``` json
[
  {
    "id": 10,
    "name": "Juan",
    "lastname": "Pérez",
    "email": "juan@cliente.com",
    "phone_number": "555-1234",
    "role": "Cliente",        // Traducido de user_role_id
    "status": "Activo",       // 'Activo' o 'Inactivo'
    "register_date": "2023-10-01"
  },
  {
    "id": 11,
    "name": "María",
    "lastname": "Gómez",
    "email": "maria@test.com",
    "phone_number": "555-9876",
    "role": "Cliente",
    "status": "Inactivo",     // Usuario baneado/desactivado
    "register_date": "2023-11-15"
  }
]
```

### Crear Usuario (Manualmente)
- **Endpoint:** `POST /api/admin/users` 
- **Objetivo:** Registrar un cliente manualmente (ej. si el cliente contactó por teléfono). 
- **Nota:** El Backend asigna automáticamente `user_role_id = Cliente`.

#### Frontend envía
``` json
{
  "name": "Carlos",
  "lastname": "Ruiz",
  "email": "carlos@nuevo.com",
  "phone_number": "555-5555",
  "password": "temporal123" // El admin asigna una contraseña inicial
}
```

#### Backend responde
|**Estado HTTP**|**Caso**|**Respuesta**|
|---|---|---|
|**201 Created**|Éxito|`{ "success": true, "message": "Usuario creado correctamente.", "id": 12 }`|
|**409 Conflict**|Email duplicado|`{ "success": false, "message": "Ese correo ya está registrado." }`|

### Editar Usuario (Datos y Contraseña)
- **Endpoint:** `PUT /api/admin/users/{id}` 
- **Objetivo:** Modificar datos personales o **restablecer contraseña**.

#### Frontend envía
``` json
{
  "name": "Juan",
  "lastname": "Pérez",
  "email": "juan.nuevo@cliente.com",
  "phone_number": "555-1234",
  "password": "nueva_password_123" // OPCIONAL. Si viene vacío, NO se cambia.
}
```

#### Backend responde:
``` json
{ "success": true, "message": "Usuario actualizado." }
```
**Nota Lógica Backend:**
- Si el campo `password` tiene texto -> `password_hash()` y actualizar.
- Si el campo `password` es `null` o `""` -> Ignorar y mantener la contraseña vieja

### Cambiar Estado (Activar/Desactivar)
- **Endpoint:** `PATCH /api/admin/users/{id}/status` 
- **Objetivo:** El "Soft Delete". Impedir que un usuario inicie sesión sin borrar sus datos históricos.

#### Frontend envía
``` json
{
  "status": "Inactivo" // O "Activo"
}
```

#### Backend responde
``` json
{ "success": true, "message": "El estado del usuario ha cambiado a Inactivo." }
```

#### Nota para el Frontend (UI)
- **Botón "Editar":** Abre un modal con los datos del usuario pre-cargados. El campo "Contraseña" debe estar vacío (placeholder: "Dejar en blanco para mantener la actual").
- **Botón "Desactivar":** Debe mostrar una alerta de confirmación ("¿Estás seguro de que quieres bloquear el acceso a este usuario?").
- **Sin botón "Borrar":** Recordar al equipo que **no hay botón de eliminar** (DELETE) para proteger el historial de pedidos.

## ADMIN Componentes
**Middleware de Seguridad:** Requiere Token válido + Rol "Vendedor".

#### Obtener Tipos de Componentes (Auxiliar)
- **Endpoint:** `GET /api/admin/component-types`
- **Objetivo:** Poblar el selector (dropdown) de "Tipo" en el formulario de creación.

#### Backend responde
``` json
[
  { "id": 1, "name": "CPU" },
  { "id": 2, "name": "GPU" },
  { "id": 3, "name": "RAM" },
  { "id": 4, "name": "Almacenamiento" }
  // ... resto de la tabla component_types
]
```

### Listar Componentes
- **Endpoint:** `GET /api/admin/components`
- **Objetivo:** Mostrar la tabla principal de inventario.
- **Opcional:** Podrían agregar parámetros de filtro en la URL (ej. `?type=1` para ver solo CPUs).

#### Backend Responde
``` json
[
  {
    "id": 101,
    "name": "AMD Ryzen 5 5600X",
    "type_name": "CPU",         // Backend hace el JOIN para traer el nombre, no solo el ID
    "price": 199.99,
    "stock": 15,
    "tier": 2,                  // La "Gama" asignada para DANIEL
    "status": "Activo"
  },
  {
    "id": 205,
    "name": "NVIDIA RTX 3060",
    "type_name": "GPU",
    "price": 350.00,
    "stock": 0,                 // Sin stock (DANIEL lo ignorará)
    "tier": 3,
    "status": "Activo"
  }
]
```

### Crear Componente
- **Endpoint:** `POST /api/admin/components` 
- **Objetivo:** Dar de alta una nueva pieza en el almacén.

#### Frontend envía
``` json
{
  "name": "Kingston Fury 16GB",
  "component_type_id": 3,      // ID obtenido del endpoint 1 (RAM)
  "price": 45.00,
  "stock": 50,
  "tier": 2                    // Gama asignada manualmente por el Vendedor
}
```

#### Backend responde
``` json
{ "success": true, "message": "Componente creado.", "id": 301 }
```

### Editar Componente
- **Endpoint:** `PUT /api/admin/components/{id}` 
- **Objetivo:** Actualizar datos. Vital para ajustar precios o corregir el Tier.

#### Frontend envía
``` json
{
  "name": "Kingston Fury 16GB (RGB)", // Cambio de nombre
  "price": 49.99,                     // Cambio de precio
  "stock": 48,                        // Ajuste manual de stock
  "tier": 2                           // Mantiene el tier
}
```

#### Backend responde
``` json
{ "success": true, "message": "Inventario actualizado." }
```

### Cambiar Estado (Activar/Desactivar)
- **Endpoint:** `PATCH /api/admin/components/{id}/status` 
- **Objetivo:** Sacar un producto del catálogo sin borrarlo (Soft Delete).

#### Frontend envía
``` json
{ "status": "Inactivo" }
```

### Backend responde
``` json
{ "success": true, "message": "Componente desactivado." }
```

### Nota Lógica para el Backend (DANIEL)
Es importante recordar al equipo de Backend que **DANIEL (`/api/configurator/search`) debe filtrar automáticamente**:
- `WHERE status = 'Activo'`
- `AND stock > 0`
Si el Vendedor pone `stock: 0` o `status: Inactivo` usando estos endpoints, esa pieza **debe desaparecer inmediatamente** de los resultados del configurador.

## ADMIN Kits
### Listado de Kits
- **Endpoint:** `GET /api/admin/kits` 
- **Objetivo:** Poblar la tabla principal donde el Vendedor ve _todos_ los kits creados. 
- **Nota Backend:** Este endpoint es especial. Debe consultar **dos tablas** (`functional_kits` y `structural_kits`), unificar los resultados en un solo Array y añadir una propiedad `kit_type` para que el Frontend sepa distinguirlos.

#### Backend responde
``` json
[
  {
    "id": 5,
    "kit_type": "functional", // "Etiqueta" añadida por el backend
    "name": "Kit Gaming Pro",
    "base_price": 1200.00,
    "status": "Activo",
    "tiers_summary": "CPU: 4 | GPU: 4 | RAM: 3" // String formateado para vista rápida
  },
  {
    "id": 12,
    "kit_type": "structural",
    "name": "Combo Airflow RGB",
    "base_price": 150.00, // Mapeado desde structural_price
    "status": "Activo",
    "tiers_summary": "Case: 3"
  }
]
```

#### Preparación del Constructor (Modal/Popup)
Cuando el Vendedor hace clic en "Crear Kit" y elige el tipo (RF_APC002), el Frontend necesita saber qué piezas mostrarle para "arrastrar y soltar" o "agregar".

- **Endpoint:** `GET /api/admin/components/pool` 
- **Query Param:** `?type=functional` O `?type=structural` 
- **Objetivo:** Devolver **solo** los componentes relevantes para ese tipo de kit (filtrados por el Backend según `RF_APC005`).

#### Frontend pide
``` js
GET /api/admin/components/pool?type=functional
```

#### Backend responde
``` json
{
  "cpu": [
    { "id": 101, "name": "Ryzen 5 5600", "price": 150.00 },
    { "id": 102, "name": "Core i5 12400", "price": 160.00 }
  ],
  "gpu": [
    { "id": 201, "name": "RTX 3060", "price": 300.00 }
  ],
  "ram": [ ... ],
  "motherboard": [ ... ],
  "psu": [ ... ]
  // Nota: NO devuelve Gabinetes ni Ventiladores aquí
}
```
*(Si el Frontend pidiera `?type=structural`, el Backend solo devolvería los grupos `case` y `fan`).*

### Guardar Kit
Debido a que las tablas y los atributos de Tiers son diferentes, es más limpio tener **dos endpoints de guardado distintos**.

***CASO A : Kit Funcional***
- **Endpoint:** `POST /api/admin/functional-kits`
***CASO B : Kit Estructural***
- **Endpoint:** `POST /api/admin/structural-kits`

#### Frontend envía

##### Caso A : Kit Funcional
``` json
{
  "name": "Kit Gaming Pro",
  "base_price": 1250.00,      // Precio calculado (Suma + Servicio)
  "cpu_tier": 4,              // Asignado manualmente por Vendedor
  "gpu_tier": 4,
  "ram_tier": 3,
  "components": [             // La lista de materiales para la tabla Pivote
    { "id": 101, "quantity": 1 }, // 1x Ryzen 5
    { "id": 201, "quantity": 1 }, // 1x RTX 3060
    { "id": 305, "quantity": 2 }  // 2x RAM Sticks
  ]
}
```
##### Caso B : Kit Estructural
``` json
{
  "name": "Combo Airflow RGB",
  "structural_price": 180.00,
  "case_tier": 3,             // Solo lleva Tier de Gabinete
  "components": [
    { "id": 801, "quantity": 1 }, // 1x Gabinete
    { "id": 905, "quantity": 3 }  // 3x Ventiladores
  ]
}
```
#### Backend Recibe (Ambos casos)
``` json
{ "success": true, "message": "Kit creado exitosamente.", "id": 15 }
```
#### Nota para el Equipo de Frontend (Validación UI)
Recordad aplicar las reglas de validación (`RF_APC010`) en el navegador antes de enviar el JSON:
- Si es **Funcional**: Debe tener al menos 1 CPU, 1 GPU, 1 RAM, 1 Mobo, 1 PSU.
- Si es **Estructural**: Debe tener 1 Gabinete.

### Editar Kit
El Frontend debe enviar **todos** los datos del kit nuevamente (incluyendo la lista de componentes), ya que la lógica estándar del Backend será: _Actualizar datos base -> Borrar componentes viejos de la pivote -> Insertar componentes nuevos_.

#### Caso A: Editar Kit Funcional
**Endpoint:** `PUT /api/admin/functional-kits/{id}`
##### Frontend envía
``` json
{
  "name": "Kit Gaming Pro v2",    // Nombre actualizado
  "base_price": 1300.00,          // Precio actualizado
  "cpu_tier": 5,                  // Tier actualizado (ej. subió de gama)
  "gpu_tier": 4,
  "ram_tier": 4,
  "components": [                 // La NUEVA lista de componentes
    { "id": 105, "quantity": 1 }, // Cambiamos el Ryzen 5 por un Ryzen 7
    { "id": 201, "quantity": 1 },
    { "id": 305, "quantity": 2 }
  ]
}
```

#### Caso B: Editar Kit Estructural
**Endpoint:** `PUT /api/admin/structural-kits/{id}`
##### Frontend envía
``` json
{
  "name": "Combo Airflow RGB (Edición Blanca)",
  "structural_price": 190.00,
  "case_tier": 4,
  "components": [
    { "id": 802, "quantity": 1 }, // Cambio de gabinete
    { "id": 905, "quantity": 4 }  // Ahora trae 4 ventiladores
  ]
}
```

#### Backend responde (Ambos casos)
``` json
{ "success": true, "message": "Kit actualizado correctamente." }
```

### Cambiar Estado (Activar/Desactivar)
#### Caso A: Estado Kit Funcional
**Endpoint:** `PATCH /api/admin/functional-kits/{id}/status`
#### Caso B: Estado Kit Estructural
**Endpoint:** `PATCH /api/admin/structural-kits/{id}/status`

#### Frontend Envía
``` json
{
  "status": "Inactivo" // O "Activo"
}
```

#### Backend responde
``` json
{ "success": true, "message": "Estado del kit actualizado." }
```

## ADMIN Pedidos
**Middleware de Seguridad:** Requiere Token válido + Rol "Vendedor".

### Listar Pedidos
- **Endpoint:** `GET /api/admin/orders` 
- **Objetivo:** Mostrar la tabla de todos los pedidos para que el administrador tenga una visión global. 
- **Filtros (Query Params):** El Frontend puede enviar `?status=Esperando Pago` para filtrar la tabla (`RF_APD004`).

#### Backend responde
El Backend debe hacer un `JOIN` con la tabla `users` para devolver el nombre del cliente directamente.
``` json
[
  {
    "id": 1001,
    "client_name": "Juan Pérez",  // Traído de la tabla users
    "date": "2023-10-27 14:30",   // created_at formateado
    "total_price": 1789.99,
    "status": "Pedido Recibido"   // El estado actual
  },
  {
    "id": 1002,
    "client_name": "Maria Gonzalez",
    "date": "2023-10-26 09:15",
    "total_price": 1200.50,
    "status": "En Ensamblaje"     // Diferente estado
  }
]
```

### Ver detalle del pedido
- **Endpoint:** `GET /api/admin/orders/{id}` 
- **Objetivo:** Esta es la vista más importante. Es la "Hoja de Ensamblaje". El Vendedor la abre para ver qué piezas sacar del inventario y a quién llamar.

#### Backend responde
Debe devolver **todo**: datos del cliente (para contactarlo) y la lista de componentes ("Snapshot").
``` json
{
  "id": 1001,
  "status": "Pedido Recibido",
  "created_at": "2023-10-27 14:30:00",
  "client_info": {
    "name": "Juan",
    "lastname": "Pérez",
    "email": "juan.perez@gmail.com",
    "phone": "555-123456"       // Vital para coordinar pago/entrega
  },
  "financials": {
    "total_price": 1789.99
  },
  "components": [               // La lista de materiales (BOM)
    {
      "type": "CPU",
      "name": "AMD Ryzen 7 7700X",
      "quantity": 1
    },
    {
      "type": "GPU",
      "name": "NVIDIA RTX 4070",
      "quantity": 1
    },
    {
      "type": "RAM",
      "name": "Kingston Fury 32GB",
      "quantity": 2             // Ojo a la cantidad
    }
    // ... resto de piezas
  ]
}
```

#### Actualizar el Estado del Pedido (Etapas)
- **Endpoint:** `PATCH /api/admin/orders/{id}/status` 
- **Objetivo:** Mover el pedido a la siguiente etapa (ej. de "Pedido Recibido" a "Esperando Pago"). 
- **Lógica Backend:** Validar que el string enviado sea uno de los valores permitidos en el `ENUM`.

#### Frontend envía
``` json
{
  "status": "Esperando Pago"
}
```

#### Backend responde 
``` json
{ "success": true, "message": "El pedido #1001 ahora está: Esperando Pago" }
```

#### Lista de estados Permitidos
Para que el equipo de Frontend pueble su selector (dropdown) correctamente:
1. `Pedido Recibido`
2. `Esperando Pago`
3. `Preparando Componentes`
4. `En Ensamblaje`
5. `Configuración y Pruebas`
6. `Listo para Entrega`
7. `Completado`
8. `Cancelado` (ID 0)