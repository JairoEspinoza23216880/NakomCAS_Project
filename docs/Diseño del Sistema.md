# Diseño del Sistema

El diseño de la plataforma NakomCAS se fundamenta en principios de ingeniería de software modernos, priorizando la escalabilidad, el desacoplamiento de componentes y la integridad de los datos.
A continuación, se detallan los aspectos arquitectónicos y lógicos que sustentan la solución.
## 1. Arquitectura del Software

Para garantizar la independencia entre la interfaz de usuario y la lógica de negocio, se optó por una arquitectura *Cliente-Servidor desacoplada* basada en el estilo arquitectónico *REST (Representational State Transfer)*.
- **Capa de Presentación (Frontend):**
  Desarrollada usando Astro usando su arquitectura propia de Islas, para tener secciones de la página de forma estática, potenciando la velocidad de carga, pero manteniendo zonas dinámicas potenciadas por JavaScript, para poder responder a la información que recibe del Backend.
- **Capa de Lógica y Servicios:**
  Implementada con PHP 8 bajo el micro-framework Slim. Funciona como una API RESTful que expone recursos a través de endpoints HTTP, procesando las solicitudes en formato JSON. Se utiliza el patrón MVC (Modelo-Vista-Controlador) para organizar el código, separando las rutas (Controladores) de la lógica de acceso a datos (Modelos); se comunica con la capa de persistencia mediante el ORM (Object Relation Mapping) Eloquent.
- **Capa de Persistencia (Datos):**
  Se gestiona con el gestor de bases de datos relaciones MySQL, encargado de almacenar la información de los usuarios, catálogo de componentes, reglas del algoritmo y órdenes de compra.

| ![[Diagrama de Componentes.png]]                                                                                 |
| ---------------------------------------------------------------------------------------------------------------- |
| _Figura 1: Diagrama de componentes ilustrando la separación física y lógica entre el cliente Astro y la API PHP_ |

## 2. Diseño de Datos
El esquema de base de datos ha sido normalizado para eliminar redundancias y asegurar la integridad de las referencias El modelo se centra en la flexibilidad del algoritmo de recomendación (DANIEL).

**Elementos Clave del Diseño**
1) **Abstracción de Requerimientos:**
   NakomCAS traduce necesidades abstractas del usuario (ej. "Gaming AAA 4K") a requerimientos técnicos concretos en forma de valores de gama para componentes, para eso hace uso de tablas como `daniel_map_needs` y `daniel_map_boosters` que justamente mapean las necesidades de forma simple y extensible.
2) **Gestión de Inventario:**
   La tabla `components` centraliza el hardware, diferenciado por tipos y atributos de rendimiento, permitiendo que el algoritmo realice consultas agnósticas a la marca.
3) **Historial Transaccional:**
   La tabla `orders` almacena el "snapshot" de la configuración generada, vinculando al usuario con el kit específico seleccionado en el momento de la compra.

| ![[Diagrama Entidad Relacion.png]]                                                                                  |
| ------------------------------------------------------------------------------------------------------------------- |
| _Figura 2: Esquema relacional de la base de datos `nakomcas`, destacando las relaciones cardinales entre entidades_ |

## 3. Lógica de Negocio y Flujo de Usuario

La lógica central del sistema reside en el **Algoritmo DANIEL** (Asistente Dinámico basado en Necesidades para la Localización de Equipos de TI por sus siglas en Inglés).
Se implementó el patrón de diseño "Lazy Registration" (Registro Diferido) para permitirle al usuario interactuar y experimentar con el configurador (el valor principal del sistema) sin compromisos. La autenticación se posterga hasta el momento estrictamente necesario (la confirmación de la orden), reduciendo la fricción y mejorando la experiencia de usuario (UX).

| ![[Diagrama de Actividad.png]]                                                                                                              |
| ------------------------------------------------------------------------------------------------------------------------------------------- |
| _Figura 3: Diagrama de actividad detallando el flujo de navegación, la toma de decisiones del usuario y los procesos de fondo del sistema._ |
## 4. Interacción y Protocolos (Diseño de API)
La comunicación entre el Frontend y el Backend se realiza mediante peticiones HTTP asíncronas (`fetch`).
El intercambio de datos se estandarizó utilizando JSON (JavaScript Object Notation), asegurando ligereza en la transmisión.

| ![[Diagrama de Secuencia.png]]                                                                                                                                                |
| ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| _Figura 4: Diagrama de secuencia mostrando el ciclo de vida de una petición de recomendación: desde el evento de clic en el cliente hasta la respuesta procesada por la API._ |

## 5. Estándares de Seguridad Implementados
Para cumplir con los requisitos no funcionales de seguridad y protección de datos, se integraron los siguientes mecanismos:

1) **Autenticación Stateless (Sin Estado):**
   Se hace uso de JWT (JSON Web Tokens) para la gestión de sesiones. Esto permite que la API sea escalable y no dependa de sesiones en memoria del servidor.
   El token firma digitalmente la identidad del usuario y se envía en la cabecera `Authorization` de cada petición privada.
2) **Sanitización de Consultas:**
   Se utiliza el ORM **Eloquent**, el cual previene ataques de inyección SQL (SQLi) mediante el uso automático de Sentencias Preparadas (Prepared Statements) y "bindings" de PDO en todas las consultas a la base de datos.
3) **Protección de Credenciales:**
   Las contraseñas de los usuarios nunca se almacenan en texto plano; se utiliza el algoritmo de hash unidireccional `bcrypt` antes de la persistencia en la base de datos.