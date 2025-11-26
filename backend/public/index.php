<?php
// backend/public/index.php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Slim\Factory\AppFactory;
use App\Models\User;

// --------------------------------------------
// --- CARGA DEPENDENCIAS Y CONFIGURACIONES ---
// --------------------------------------------

// Carga todas las dependencias de Composer
require __DIR__ . '/../vendor/autoload.php';

// Carga la configuración de la Base de Datos (Eloquent)
// Esto conecta Eloquent ANTES de que Slim arranque.
require __DIR__ . '/../config/database.php';

// Crea la aplicación Slim
$app = AppFactory::create();
$app->addBodyParsingMiddleware(); // Middleware para parsear JSON automáticamente

// ---------------------
// --- CONFIGURAR CORS ---
// ---------------------
// IMPORTANTE: CORS debe configurarse ANTES de las rutas

// Configurar CORS middleware
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    
    $response = $response
        ->withHeader('Access-Control-Allow-Origin', 'http://localhost:4321')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
        ->withHeader('Access-Control-Allow-Credentials', 'true');
    
    return $response;
});

// Manejar preflight OPTIONS
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

// ---------------------
// --- RUTAS DEL API ---
// ---------------------


// Carga rutas desde archivos separados para mejor organización
$authRoutes = require __DIR__ . '/../app/Routes/auth.php';
$authRoutes($app);

$configRoutes = require __DIR__ . '/../app/Routes/configurator.php';
$configRoutes($app);

// Rutas de administración
$adminRoutes = require __DIR__ . '/../app/Routes/admin.php';
$adminRoutes($app);

$adminOrdersRoutes = require __DIR__ . '/../app/Routes/adminOrders.php';
$adminOrdersRoutes($app);

// adminComponents.php está comentado porque admin.php ya maneja las rutas de componentes
// $adminComponentsRoutes = require __DIR__ . '/../app/Routes/adminComponents.php';
// $adminComponentsRoutes($app);

$adminUsersRoutes = require __DIR__ . '/../app/Routes/adminUsers.php';
$adminUsersRoutes($app);

// Rutas de pedidos
$ordersRoutes = require __DIR__ . '/../app/Routes/orders.php';
$ordersRoutes($app);


// -----------------------
// --- RUTAS DE PRUEBA ---
// -----------------------

/**
 * Ruta de Prueba: GET /hello
 * (Cuando configures tu servidor, esta será tu-proyecto.com/hello)
 */
$app->get('/hello', function (Request $request, Response $response, $args) {
    $data = ['message' => '¡Hola Mundo! El API de Slim está funcionando.'];
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/', function (Request $request, Response $response, $args) {
    $data = ['message' => 'Bienvenido al API'];
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

/**
 * Ruta de Prueba de BD: GET /test-db
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
