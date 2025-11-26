<?php
// backend/app/Routes/admin.php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Component;
use App\Models\ComponentType;

return function ($app) {
    
    // ============================================
    // ADMIN - Tipos de Componentes
    // ============================================
    
    /**
     * GET /api/admin/component-types
     * Obtener todos los tipos de componentes
     */
    $app->get('/api/admin/component-types', function (Request $request, Response $response) {
        try {
            $types = ComponentType::all()->map(function($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->type_name
                ];
            });
            
            $response->getBody()->write(json_encode($types));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $error = ['success' => false, 'message' => 'Error al obtener tipos: ' . $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });
    
    // ============================================
    // ADMIN - Componentes
    // ============================================
    
    /**
     * GET /api/admin/components
     * Listar todos los componentes
     */
    $app->get('/api/admin/components', function (Request $request, Response $response) {
        try {
            $components = Component::with('componentType')
                ->get()
                ->map(function ($component) {
                    return [
                        'id' => $component->id,
                        'name' => $component->name,
                        'type_name' => $component->componentType ? $component->componentType->name : 'Desconocido',
                        'component_type_id' => $component->component_type_id,
                        'price' => (float) $component->price,
                        'stock' => (int) $component->stock,
                        'tier' => (int) $component->tier,
                        'status' => $component->status
                    ];
                });
            
            $response->getBody()->write(json_encode($components));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $error = ['success' => false, 'message' => 'Error al obtener componentes: ' . $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });
    
    /**
     * POST /api/admin/components
     * Crear un nuevo componente
     */
    $app->post('/api/admin/components', function (Request $request, Response $response) {
        try {
            $data = $request->getParsedBody();
            
            // Validar datos requeridos
            if (empty($data['name']) || empty($data['component_type_id'])) {
                $error = ['success' => false, 'message' => 'El nombre y tipo son requeridos'];
                $response->getBody()->write(json_encode($error));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
            
            // Crear componente
            $component = Component::create([
                'name' => $data['name'],
                'component_type_id' => $data['component_type_id'],
                'price' => $data['price'] ?? 0,
                'stock' => $data['stock'] ?? 0,
                'tier' => $data['tier'] ?? 1,
                'status' => 'Activo'
            ]);
            
            $result = [
                'success' => true,
                'message' => 'Componente creado correctamente',
                'id' => $component->id
            ];
            
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (\Exception $e) {
            $error = ['success' => false, 'message' => 'Error al crear componente: ' . $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });
    
    /**
     * PUT /api/admin/components/{id}
     * Actualizar un componente existente
     */
    $app->put('/api/admin/components/{id}', function (Request $request, Response $response, array $args) {
        try {
            $id = $args['id'];
            $data = $request->getParsedBody();
            
            $component = Component::find($id);
            
            if (!$component) {
                $error = ['success' => false, 'message' => 'Componente no encontrado'];
                $response->getBody()->write(json_encode($error));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
            
            // Actualizar campos
            if (isset($data['name'])) $component->name = $data['name'];
            if (isset($data['component_type_id'])) $component->component_type_id = $data['component_type_id'];
            if (isset($data['price'])) $component->price = $data['price'];
            if (isset($data['stock'])) $component->stock = $data['stock'];
            if (isset($data['tier'])) $component->tier = $data['tier'];
            
            $component->save();
            
            $result = ['success' => true, 'message' => 'Componente actualizado correctamente'];
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $error = ['success' => false, 'message' => 'Error al actualizar componente: ' . $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });
    
    /**
     * PATCH /api/admin/components/{id}/status
     * Cambiar el estado de un componente (Activar/Desactivar)
     */
    $app->patch('/api/admin/components/{id}/status', function (Request $request, Response $response, array $args) {
        try {
            $id = $args['id'];
            $data = $request->getParsedBody();
            
            $component = Component::find($id);
            
            if (!$component) {
                $error = ['success' => false, 'message' => 'Componente no encontrado'];
                $response->getBody()->write(json_encode($error));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
            
            if (isset($data['status'])) {
                $component->status = $data['status'];
                $component->save();
            }
            
            $result = ['success' => true, 'message' => 'Estado del componente actualizado'];
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $error = ['success' => false, 'message' => 'Error al cambiar estado: ' . $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });
};
