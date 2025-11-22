# Guía Rápida para Backend (Slim + Eloquent)


## Dónde escribir código:

Si vas a hacer una Tabla nueva -> Crea un modelo en app/Models/.
Si vas a hacer un Endpoint -> Edita el archivo correspondiente en app/Routes/.


## Cómo consultar la BD (Eloquent):

- $todos = User::all();
- $uno = User::find(1);
- $filtro = FunctionalKit::where('cpu_tier', '>=', 4)->get();

$nuevo = new Order(); $nuevo->total = 100; $nuevo->save();


## Cómo probar:

Corre el servidor: php -S localhost:8000 -t public
Usa Postman o el navegador en http://localhost:8000/api/...