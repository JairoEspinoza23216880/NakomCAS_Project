<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Models\Order;
use App\Models\User;
use App\Middleware\JwtMiddleware;

// Función para autenticar usuario y obtener el rol
if (!function_exists('authenticateAdmin')) {
    function authenticateAdmin($request)
    {
        $jwtUser = $request->getAttribute('user');
        if (!$jwtUser || !isset($jwtUser->sub)) {
            return [
                'success' => false,
                'code' => 401,
                'message' => 'No se pudo identificar al usuario.'
            ];
        }
        $user = User::with('userRole')->find($jwtUser->sub);
        if (!$user || !$user->userRole || stripos(strtolower($user->userRole->name), 'admin') === false) {
            return [
                'success' => false,
                'code' => 403,
                'message' => 'Acceso denegado: solo administradores pueden realizar esta acción.'
            ];
        }
        return [
            'success' => true,
            'user' => $user
        ];
    }
}

// Rutas para administración de componentes
return function (App $app) {

    /**
     * OBTENER TIPOS DE COMPONENTES
     * Endpoint: GET /api/admin/component-types
     * Objetivo: Obtener todos los tipos de componentes disponibles
     */
    $app->get('/api/admin/component-types', function (Request $request, Response $response) {
        try {
            // 1. Autenticación y validación de rol administrador
            $auth = authenticateAdmin($request);
            if (!$auth['success']) {
                $response->getBody()->write(json_encode([
                    'error' => $auth['message']
                ]));
                return $response->withStatus($auth['code'])
                    ->withHeader('Content-Type', 'application/json');
            }

            // 2. Consulta de tipos de componentes en la base de datos
            $types = \App\Models\ComponentType::all();

            // 3. Formateo de la respuesta según el contrato del API
            $result = $types->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->type_name
                ];
            })->toArray();

            // 4. Envío de respuesta JSON (array, aunque esté vacío)
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            // 5. Manejo de errores internos
            $response->getBody()->write(json_encode([
                'error' => 'Error interno del servidor',
                'details' => $e->getMessage()
            ]));
            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    })->add(new JwtMiddleware());



    /**
     * OBTENER COMPONENTES
     * Endpoint: GET /api/admin/components
     * Objetivo: Obtener todos los componentes registrados
     */
    $app->get('/api/admin/components', function (Request $request, Response $response) {
        // Obtener todos los componentes
    })->add(new JwtMiddleware());



    /**
     * CREAR COMPONENTE
     * Endpoint: POST /api/admin/components
     * Objetivo: Registrar un nuevo componente en el sistema
     */
    $app->post('/api/admin/components', function (Request $request, Response $response) {
        // Crear un nuevo componente
    })->add(new JwtMiddleware());



    /**
     * EDITAR COMPONENTE
     * Endpoint: PUT /api/admin/components/{id}
     * Objetivo: Editar los datos de un componente existente
     */
    $app->put('/api/admin/components/{id}', function (Request $request, Response $response, $args) {
        // Editar un componente existente
    })->add(new JwtMiddleware());



    /**
     * CAMBIAR ESTADO DE COMPONENTE
     * Endpoint: PATCH /api/admin/components/{id}/status
     * Objetivo: Activar o desactivar un componente
     */
    $app->patch('/api/admin/components/{id}/status', function (Request $request, Response $response, $args) {
        // Desactivar o activar un componente
    })->add(new JwtMiddleware());
};
