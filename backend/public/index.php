<?php
// backend/public/index.php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Slim\Factory\AppFactory;

// Carga todas las dependencias de Composer
require __DIR__ . '/../vendor/autoload.php';

// Carga la configuración de la Base de Datos (Eloquent)
// Esto conecta Eloquent ANTES de que Slim arranque.
require __DIR__ . '/../config/database.php';

// Crea la aplicación Slim
$app = AppFactory::create();

// --- RUTAS DE TU API ---

/**
 * Ruta de Prueba: GET /api/hello
 * (Cuando configures tu servidor, esta será tu-proyecto.com/api/hello)
 */
$app->get('/hello', function (Request $request, Response $response, $args) {
    $data = ['message' => '¡Hola Mundo! El API de Slim está funcionando.'];
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

/**
 * Ruta de Prueba de BD: GET /api/test-db
 * (Esta ruta intentará usar Eloquent para probar la conexión)
 */
$app->get('/test-db', function (Request $request, Response $response, $args) {
    try {
        // Intenta hacer una consulta simple usando Eloquent
        // (Asume que tienes una tabla 'users')
        $userCount = User::count(); 
        $data = ['message' => '¡Conexión a la BD exitosa!', 'user_count' => $userCount];

    } catch (\Exception $e) {
        // Si falla (ej. tabla no existe, credenciales mal)
        $data = ['error' => 'Error conectando a la BD', 'message' => $e->getMessage()];
    }

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

// Carga tus rutas de API modulares (¡Buena práctica!)
// (Aún no lo hemos creado, pero aquí iría)
// require __DIR__ . '/../app/Routes/api.php';


// ¡Corre la aplicación!
$app->run();