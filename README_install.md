# Instalación del Proyecto NakomCAS

## Requisitos Previos
- PHP >= 8.0
- Composer
- Node.js >= 18.x
- npm o yarn

---

## Backend (PHP + Composer)

### 1. Instalar Composer
Si no tienes Composer instalado:
- Windows: https://getcomposer.org/Composer-Setup.exe
- Mac/Linux: Ejecuta en terminal:
  ```sh
  curl -sS https://getcomposer.org/installer | php
  sudo mv composer.phar /usr/local/bin/composer
  ```

### 2. Instalar dependencias PHP
Desde la carpeta `backend/` ejecuta:
```sh
cd backend
composer install
```
Esto instalará todas las dependencias definidas en `composer.json`.

### 3. Configurar archivo `.env`
Copia el archivo de ejemplo si existe:
```sh
cp .env.example .env
```
Edita el archivo `.env` y configura:
(Pídele a Jairo el .env)
```
DB_HOST=localhost
DB_DATABASE=nakomcas_db
DB_USERNAME=root
DB_PASSWORD=tu_contraseña
JWT_SECRET=tu_clave_secreta
```
Asegúrate de que los datos coincidan con tu entorno local.

Para correr el Backend, dentro de la carpeta usando la terminal crea un server php simple
```
php -S localhost:8000 -t public
```

---

## Frontend (Astro)

### 1. Instalar dependencias de Astro
Desde la carpeta `frontend/` ejecuta:
```sh
cd frontend
npm install
```
Esto instalará Astro y todas las dependencias del frontend.

### 2. Ejecutar Astro en modo desarrollo
```sh
npm run dev
```
El proyecto estará disponible en `http://localhost:4321` (por defecto).

---

## Notas Adicionales
- Si usas Docker, puedes crear tus propios archivos `Dockerfile` y `docker-compose.yml`.
- Para migrar la base de datos, revisa los archivos en `backend/sqlfiles/`.
- Si tienes problemas con permisos, ejecuta los comandos como administrador o con `sudo`.

---

¡Listo! Tu entorno NakomCAS estará preparado para desarrollo local.
