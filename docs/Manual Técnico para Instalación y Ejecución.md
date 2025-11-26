
## **1. Requisitos Previos**

Antes de comenzar, asegúrate de tener instaladas las siguientes herramientas. Sin esto, el proyecto no podrá arrancar.

### Herramientas necesarias:

- **XAMPP / MySQL**  
    Para ejecutar el servidor de base de datos.
- **Node.js + NPM**  
    Necesarios para correr el frontend e instalar dependencias.
- **Composer** (Versión recomendada: `v2.9.2`)  
    Se usa para instalar las dependencias del backend en PHP.

Cuando todo esto esté instalado, puedes continuar.

---
## **2. Instalación del Backend (PHP / Slim / MySQL)**

El backend es la parte del sistema encargada de procesar solicitudes, conectarse a la base de datos y devolver respuestas al frontend. Para instalarlo correctamente sigue estos pasos:

---
### **2.1 Clonar el repositorio**

Desde tu terminal, escribe:

`git clone https://github.com/proyecto`

Esto descargará el código del proyecto.

---
### **2.2 Entrar en la carpeta del backend**

`cd backend`

---
### **2.3 Instalar dependencias con Composer**

`composer install`

Este comando descargará todas las librerías necesarias (Slim, JWT, Dotenv, etc.).

---
### **2.4 Importar la base de datos**

Dentro de phpMyAdmin o tu herramienta de preferencia:

1. Crear una base de datos nueva  
    Nombre: `nakomcas`
    
2. Importar `schema.sql`  
    Este archivo contiene las tablas del sistema.
    
3. Importar `seeds.sql`  
    Inserta los datos iniciales necesarios.
    
---
### **2.5 Crear y configurar el archivo `.nav`**

En la carpeta del backend crea un archivo llamado:

`.nav`

Y agrega lo siguiente:

```
DB_HOST = 127.0.0.1 
DB_PORT = 3306 
DB_DATABASE = nakomcas 
DB_USERNAME = root 
DB_PASSWORD = 
JWT_SECRET = calvesecreta123
```
#### Significado de cada variable:

- **DB_HOST** → Dirección del servidor MySQL (localhost).
- **DB_PORT** → Puerto donde corre MySQL (3306 por defecto).
- **DB_DATABASE** → Nombre de la BD creada.
- **DB_USERNAME** → Usuario de MySQL (normalmente `root`).
- **DB_PASSWORD** → Contraseña del usuario (vacía si usas XAMPP).
- **JWT_SECRET** → Clave privada usada para generar tokens JWT.
---
### **2.6 Levantar el servidor PHP**

Desde la carpeta `backend`, ejecuta:

`php -S localhost:8000 -t public`

Tu backend estará disponible en:

 **[http://localhost:8000](http://localhost:8000)**

![[Pasted image 20251126012541.png]]

---
## **3. Instalación del Frontend (Astro / Tailwind / JS)**

El frontend es la parte visual del proyecto, hecha con Astro y TailwindCSS.

---
### **3.1 Entrar a la carpeta del frontend**

`cd frontend`

---
### **3.2 Instalar dependencias**

Asegúrate de tener Node.js instalado previamente.  
Luego ejecuta:

`npm install`

---
### **3.3 Levantar el proyecto**

Ejecuta:

`npm run dev`

El servidor de desarrollo se abrirá en:

👉 **[http://localhost:4321](http://localhost:4321)**

![[Pasted image 20251126012713.png]]



