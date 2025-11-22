# 📘 Estándares de Código Backend (PHP - PSR-12)

> **IMPORTANTE:** El cumplimiento de estos estándares representa el **40% de la calificación** en la asignatura "Construcción de Software". Todo código subido al repositorio debe seguir estas reglas.

El proyecto sigue el estándar industrial **PSR-12**.

---

## 1. Convenciones de Nombres (Naming)

| Elemento | Estilo | Ejemplo Correcto | Ejemplo Incorrecto |
| :--- | :--- | :--- | :--- |
| **Clases / Modelos** | `PascalCase` | `class FunctionalKit` | `class functional_kit` |
| **Métodos** | `camelCase` | `public function getPrice()` | `public function get_price()` |
| **Variables** | `camelCase` | `$userId`, `$kitsList` | `$user_id`, `$kits_list` |
| **Constantes** | `UPPER_CASE` | `const MAX_ITEMS = 5;` | `const max_items = 5;` |

---

## 2. Reglas de Sintaxis (PSR-12)

### Llaves (Braces) `{}`
Esta es la regla más estricta y donde más fallamos.
* **Clases y Métodos:** La llave de apertura va en la **SIGUIENTE** línea.
* **Control (if, for, while):** La llave de apertura va en la **MISMA** línea.

```php
<?php

namespace App\Models;

class User extends Model
{ // <--- CLASE: Abajo
    
    public function orders()
    { // <--- MÉTODO: Abajo
        // ...
    }

    public function checkStatus()
    {
        if ($this->status === 'Active') { // <--- IF: Arriba (con espacio antes)
            return true;
        } else {
            return false;
        }
    }
}
```

### Visibilidad
Es obligatorio declarar la visibilidad (public, private, protected) en todas las propiedades y métodos.

- CORRECTO: `public function index()`
- INCORRECTO: `function index()`

### Etiquetas PHP
Usar siempre <?php. Nunca usar <?.

NUNCA cerrar la etiqueta ?> al final de archivos que solo contienen código PHP.

---
## 3. Documentación (PHPDoc)
Requisito obligatorio para funciones principales (especialmente las usadas para cálculo de complejidad ciclomática). No usar comentarios de línea // para documentar funciones.

Formato requerido:

```
/**
 * Busca kits funcionales que cumplan con los tiers mínimos.
 * Descripción breve de qué hace la función.
 *
 * @param int $cpuTier El nivel mínimo de CPU requerido.
 * @param int $gpuTier El nivel mínimo de GPU requerido.
 * @return array Lista de objetos FunctionalKit encontrados.
 */
public function searchKits(int $cpuTier, int $gpuTier): array
{
    // lógica...
}
```

---
## 4. Automatización (Configuración VS Code)
Para no perder tiempo arreglando espacios manualmente, configura tu editor para que lo haga solo.

- Instala la extensión "PHP Intelephense" (Autor: Intelephense).
- Ve a Configuración (Ctrl + ,).
- Busca y activa "Format On Save".
- Busca "Default Formatter" y selecciona "PHP Intelephense".

Resultado: Cada vez que guardes (Ctrl + S), VS Code corregirá las llaves y la indentación automáticamente según PSR-12.