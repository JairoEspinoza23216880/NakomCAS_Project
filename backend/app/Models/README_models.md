# Modelos de Eloquent

Para el equipo: Esta guía explica qué son los Modelos que usaremos en backend/app/Models/ y cómo configurarlos.

## ¿Qué es un Modelo?

Un Modelo es una clase de PHP que actúa como el "intermediario" entre nuestro código y una tabla de la base de datos.

Sin Eloquent: Tendrías que escribir SQL: SELECT * FROM users WHERE id = 1.

Con Eloquent: Usas la clase: User::find(1).

Cada tabla de nuestra base de datos tendrá su propio archivo de Modelo (ej. users -> User.php).

## Anatomía de un Modelo

Este es el esqueleto básico que tendrán casi todos nuestros archivos en app/Models/.

```
<?php

namespace App\Models; // 1. Ubicación del archivo

use Illuminate\Database\Eloquent\Model; // 2. Importamos la clase base

class User extends Model // 3. Nombre de la Clase (PascalCase)
{
    // 4. Configuración de la Tabla
    protected $table = 'users';

    // 5. Campos Editables (Seguridad)
    protected $fillable = [
        'name',
        'email',
        'password'
    ];

    // 6. Tiempos (Timestamps)
    public $timestamps = true; 

    // 7. Relaciones
    public function orders() {
        return $this->hasMany(Order::class);
    }
}
```

### Explicación de las Partes

#### 1. protected $table

Le dice a Eloquent qué tabla de MySQL corresponde a este archivo.

Si no lo pones, Eloquent intentará adivinarlo (ej. User busca users).

Recomendación: Siempre ponerlo para evitar errores.

#### 2. protected $fillable (¡Muy Importante!)

Es una medida de seguridad. Lista los nombres de las columnas que permites que se llenen masivamente usando User::create($datos).

Si intentas guardar un campo que no está aquí (ej. is_admin), Eloquent lo ignorará silenciosamente.

Tip: Incluye aquí todo lo que venga de un formulario.

#### 3. public $timestamps

true (Por defecto): Eloquent intentará llenar automáticamente las columnas created_at y updated_at cada vez que guardes.

false: Úsalo para tablas que NO tienen estas columnas (como nuestros catálogos: component_types, daniel_map_needs).

## Las Relaciones (El Superpoder)

Aquí es donde conectamos las tablas. En lugar de hacer JOIN manuales en SQL, definimos funciones.

### A. Uno a Muchos (hasMany / belongsTo)

Ejemplo: Un Usuario tiene muchos Pedidos.

En User.php:
```
public function orders() {
    return $this->hasMany(Order::class, 'user_id');
}
// Uso: $user->orders; (Te da un array de pedidos)
```

En Order.php:
```
public function user() {
    return $this->belongsTo(User::class, 'user_id');
}
// Uso: $order->user->name; (Te da el nombre del dueño del pedido)
```

### B. Muchos a Muchos (belongsToMany)

Ejemplo: Un Kit Funcional tiene muchos Componentes (y viceversa).
Esto usa una tabla pivote intermedia.

En FunctionalKit.php:
```
public function components() {
    return $this->belongsToMany(
        Component::class,                // Modelo destino
        'functional_kit_components',     // Nombre de la tabla pivote en MySQL
        'functional_kit_id',             // Llave foránea de ESTE modelo en la pivote
        'component_id'                   // Llave foránea del OTRO modelo en la pivote
    )->withPivot('quantity');            // ¡Importante! Para leer la cantidad
}
```

## Hoja de Trucos (Cheat Sheet)

|**Acción**|**Código Eloquent**|**SQL Equivalente (Aprox)**|
|---|---|---|
|**Buscar todos**|`$users = User::all();`|`SELECT * FROM users`|
|**Buscar por ID**|`$user = User::find(1);`|`SELECT * FROM users WHERE id = 1`|
|**Filtrar**|`$cpu = Component::where('type_id', 1)->get();`|`SELECT * FROM components WHERE type_id = 1`|
|**Crear nuevo**|`User::create(['name' => 'Juan', ...]);`|`INSERT INTO users (name...) VALUES ('Juan'...)`|
|**Actualizar**|`$u = User::find(1); $u->name = 'Pepe'; $u->save();`|`UPDATE users SET name='Pepe' WHERE id=1`|
|**Relación**|`$kit->components`|`SELECT * FROM components INNER JOIN ...`|