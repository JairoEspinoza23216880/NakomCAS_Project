# Documentación de composer.json

Este archivo describe los campos y dependencias del archivo `composer.json` ubicado en `backend/`.

## Campos principales

- **require**: Lista de dependencias necesarias para el funcionamiento del proyecto en producción.
  - `slim/slim`: Framework ligero para aplicaciones web en PHP.
  - `slim/psr7`: Implementación PSR-7 para manejo de peticiones y respuestas HTTP.
  - `illuminate/database`: ORM Eloquent de Laravel para el manejo de base de datos.
  - `vlucas/phpdotenv`: Manejo de variables de entorno en PHP.

- **autoload**: Configuración para el autoload de clases PHP.
  - `psr-4`: Estándar para autoloading de clases. El namespace `App\` apunta al directorio `app/`.

## Ejemplo de estructura

```json
{
    "require": {
        "slim/slim": "^4.14",
        "slim/psr7": "^1.8",
        "illuminate/database": "^12.38",
        "vlucas/phpdotenv": "^5.6"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    }
}
```

## Notas
- No se pueden agregar comentarios dentro de `composer.json`.
- Para agregar documentación, utiliza este archivo README.
- Si agregas nuevas dependencias, documenta aquí su propósito.
