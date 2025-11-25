<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Middleware\JwtMiddleware;
use App\Middleware\AdminMiddleware;


return function (App $app) {

    // Función privada para validar tipos y valores de los datos del componente
    function validateComponentData($data)
    {
        $numericFields = [
            'component_type_id' => ['positive' => true, 'label' => 'tipo de componente'],
            'price' => ['positive' => false, 'label' => 'precio'],
            'stock' => ['positive' => false, 'label' => 'stock'],
            'tier' => ['tier' => true, 'label' => 'gama']
        ];
        foreach ($numericFields as $field => $opts) {
            if (!is_numeric($data[$field])) {
                return [
                    'success' => false,
                    'message' => "El campo '{$field}' ({$opts['label']}) debe ser numérico."
                ];
            }
            if (isset($opts['positive']) && $opts['positive'] && intval($data[$field]) <= 0) {
                return [
                    'success' => false,
                    'message' => "El campo '{$field}' ({$opts['label']}) debe ser un número positivo."
                ];
            }
            // tier puede ser 0 o mayor
            if (isset($opts['tier']) && $opts['tier'] && intval($data[$field]) < 0) {
                return [
                    'success' => false,
                    'message' => "El campo '{$field}' ({$opts['label']}) debe ser 0 (Gama de Entrada) o mayor."
                ];
            }
            if (isset($opts['positive']) && !$opts['positive'] && floatval($data[$field]) < 0) {
                return [
                    'success' => false,
                    'message' => "El campo '{$field}' ({$opts['label']}) debe ser mayor o igual a cero."
                ];
            }
        }
        return null;
    }


    /**
     * OBTENER TIPOS DE COMPONENTES
     * Endpoint: GET /api/admin/component-types
     * Objetivo: Obtener todos los tipos de componentes disponibles
     */
    $app->get('/api/admin/component-types', function (Request $request, Response $response) {
        try {

            // 1. Consulta de tipos de componentes en la base de datos
            $types = \App\Models\ComponentType::all();


            // 2. Formateo de la respuesta según el contrato del API
            $result = $types->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->type_name
                ];
            })->toArray();


            // 3. Envío de respuesta JSON (array, aunque esté vacío)
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {

            // 4. Manejo de errores internos
            $response->getBody()->write(json_encode([
                'error' => 'Error interno del servidor',
                'details' => $e->getMessage()
            ]));
            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    })->add(new AdminMiddleware())->add(new JwtMiddleware());



    /**
     * OBTENER COMPONENTES
     * Endpoint: GET /api/admin/components
     * Objetivo: Obtener todos los componentes registrados
     */
    $app->get('/api/admin/components', function (Request $request, Response $response) {
        try {

            // 1. Obtener parámetros de filtro (opcional) y validar 'type'
            $params = $request->getQueryParams();
            $query = \App\Models\Component::with('componentType');
            if (isset($params['type'])) {
                if (!is_numeric($params['type'])) {
                    $response->getBody()->write(json_encode([
                        'success' => false,
                        'message' => 'El parámetro "type" debe ser numérico.'
                    ]));
                    return $response->withStatus(400)
                        ->withHeader('Content-Type', 'application/json');
                }
                $query->where('component_type_id', $params['type']);
            }


            // 2. Consulta de componentes con JOIN al tipo
            $components = $query->get();


            // 3. Formateo de la respuesta según el contrato del API
            $result = $components->map(function ($component) {
                return [
                    'id' => $component->id,
                    'name' => $component->name,
                    'type_name' => $component->componentType ? $component->componentType->type_name : null,
                    'price' => $component->price,
                    'stock' => $component->stock,
                    'tier' => $component->tier,
                    'status' => $component->status
                ];
            })->toArray();


            // 4. Envío de respuesta JSON (array, aunque esté vacío)
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {

            // 5. Manejo de errores internos con mensaje personalizado
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'No se pudo obtener el inventario. Intenta nuevamente o contacta al administrador.'
            ]));
            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    })->add(new AdminMiddleware())->add(new JwtMiddleware());



    /**
     * CREAR COMPONENTE
     * Endpoint: POST /api/admin/components
     * Objetivo: Registrar un nuevo componente en el sistema
     */
    $app->post('/api/admin/components', function (Request $request, Response $response) {
        try {

            // 1. Obtener y validar datos del componente
            $data = $request->getParsedBody();
            $required = ['name', 'component_type_id', 'price', 'stock', 'tier'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    $response->getBody()->write(json_encode([
                        'success' => false,
                        'message' => "El campo '$field' es obligatorio."
                    ]));
                    return $response->withStatus(400)
                        ->withHeader('Content-Type', 'application/json');
                }
            }


            // 2. Validar tipos y valores con función privada
            if ($error = validateComponentData($data)) {
                $response->getBody()->write(json_encode($error));
                return $response->withStatus(400)
                    ->withHeader('Content-Type', 'application/json');
            }


            // 3. Verificar existencia de tipo de componente
            $typeExists = \App\Models\ComponentType::find($data['component_type_id']);
            if (!$typeExists) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => "El tipo de componente especificado no existe."
                ]));
                return $response->withStatus(400)
                    ->withHeader('Content-Type', 'application/json');
            }


            // 4. Crear el componente
            $component = new \App\Models\Component([
                'name' => $data['name'],
                'component_type_id' => $data['component_type_id'],
                'price' => $data['price'],
                'stock' => $data['stock'],
                'tier' => $data['tier'],
                'status' => 'Activo'
            ]);
            $component->save();


            // 5. Respuesta exitosa
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Componente creado.',
                'id' => $component->id
            ]));
            return $response->withStatus(201)
                ->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {

            // 6. Manejo de errores internos con mensaje específico
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Ocurrió un error inesperado al crear el componente. Intenta nuevamente o contacta al administrador.'
            ]));
            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    })->add(new AdminMiddleware())->add(new JwtMiddleware());



    /**
     * EDITAR COMPONENTE
     * Endpoint: PUT /api/admin/components/{id}
     * Objetivo: Editar los datos de un componente existente
     */
    $app->put('/api/admin/components/{id}', function (Request $request, Response $response, $args) {
        try {

            // 1. Validar el parámetro ID
            $componentId = $args['id'];
            if (!is_numeric($componentId)) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'El ID del componente debe ser numérico.'
                ]));
                return $response->withStatus(400)
                    ->withHeader('Content-Type', 'application/json');
            }


            // 2. Buscar el componente
            $component = \App\Models\Component::find($componentId);
            if (!$component) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Componente no encontrado.'
                ]));
                return $response->withStatus(404)
                    ->withHeader('Content-Type', 'application/json');
            }


            // 3. Obtener y validar datos
            $data = $request->getParsedBody();
            $required = ['name', 'price', 'stock', 'tier'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    $response->getBody()->write(json_encode([
                        'success' => false,
                        'message' => "El campo '$field' es obligatorio."
                    ]));
                    return $response->withStatus(400)
                        ->withHeader('Content-Type', 'application/json');
                }
            }


            // 4. Validar tipos y valores
            // Usamos la función privada, pero solo para los campos editables
            $validateData = [
                'component_type_id' => $component->component_type_id, // No editable aquí
                'price' => $data['price'],
                'stock' => $data['stock'],
                'tier' => $data['tier']
            ];
            if ($error = validateComponentData($validateData)) {
                $response->getBody()->write(json_encode($error));
                return $response->withStatus(400)
                    ->withHeader('Content-Type', 'application/json');
            }


            // 5. Actualizar datos
            $component->name = $data['name'];
            $component->price = $data['price'];
            $component->stock = $data['stock'];
            $component->tier = $data['tier'];
            $component->save();


            // 6. Respuesta exitosa
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Inventario actualizado.'
            ]));
            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {

            // 7. Manejo de errores internos con mensaje específico
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Ocurrió un error inesperado al editar el componente.'
            ]));
            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    })->add(new AdminMiddleware())->add(new JwtMiddleware());



    /**
     * CAMBIAR ESTADO DE COMPONENTE
     * Endpoint: PATCH /api/admin/components/{id}/status
     * Objetivo: Activar o desactivar un componente
     */
    $app->patch('/api/admin/components/{id}/status', function (Request $request, Response $response, $args) {
        try {
            // 1. Validar el parámetro ID
            $componentId = $args['id'];
            if (!is_numeric($componentId)) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'El ID del componente debe ser numérico.'
                ]));
                return $response->withStatus(400)
                    ->withHeader('Content-Type', 'application/json');
            }

            // 2. Buscar el componente
            $component = \App\Models\Component::find($componentId);
            if (!$component) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Componente no encontrado.'
                ]));
                return $response->withStatus(404)
                    ->withHeader('Content-Type', 'application/json');
            }

            // 3. Obtener y validar el nuevo estado
            $data = $request->getParsedBody();
            $newStatus = $data['status'] ?? null;
            $allowedStatuses = ['Activo', 'Inactivo'];
            if (!$newStatus || !in_array($newStatus, $allowedStatuses)) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => "El estado debe ser 'Activo' o 'Inactivo'."
                ]));
                return $response->withStatus(400)
                    ->withHeader('Content-Type', 'application/json');
            }

            // 4. Actualizar estado y guardar
            $component->status = $newStatus;
            $component->save();

            // 5. Respuesta exitosa
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => "Componente " . ($newStatus === 'Activo' ? 'activado' : 'desactivado') . "."
            ]));
            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Ocurrió un error inesperado al cambiar el estado del componente.'
            ]));
            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    })->add(new AdminMiddleware())->add(new JwtMiddleware());
};
