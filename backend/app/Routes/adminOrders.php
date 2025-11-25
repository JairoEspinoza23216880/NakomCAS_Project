<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Models\Order;
use App\Middleware\JwtMiddleware;
use App\Middleware\AdminMiddleware;


return function (App $app) {

    /**
     * OBTENER TODOS LOS PEDIDOS (ADMIN)
     * Endpoint: GET /api/admin/orders
     * Objetivo: Retornar la lista de todos los pedidos con información resumida
     */
    $app->get('/api/admin/orders', function (Request $request, Response $response) {
        try {
            // 1. Obtener todos los pedidos con información resumida
            $orders = Order::with(['user'])
                ->orderBy('id', 'desc')
                ->get();


            // 2. Formatear resultado
            $result = $orders->map(function ($order) {
                $summary_name = '';
                if ($order->user) {
                    $summary_name = $order->user->name . ' ' . $order->user->lastname;
                }
                return [
                    'id' => $order->id,
                    'date' => $order->created_at,
                    'client_name' => $order->user ? $order->user->name : null,
                    'client_lastname' => $order->user ? $order->user->lastname : null,
                    'client_email' => $order->user ? $order->user->email : null,
                    'status' => $order->status,
                    'total_price' => $order->total_price,
                    'summary_name' => $summary_name
                ];
            });


            // 3. Enviar respuesta
            $response->getBody()->write(json_encode([
                'success' => true,
                'orders' => $result
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {

            // 4. Manejar errores inesperados
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Error al obtener los pedidos: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    })->add(new AdminMiddleware())->add(new JwtMiddleware());



    /**
     * OBTENER PEDIDO POR ID (ADMIN)
     * Endpoint: GET /api/admin/orders/{id}
     * Objetivo: Retornar los detalles de un pedido específico
     */
    $app->get('/api/admin/orders/{id}', function (Request $request, Response $response, $args) {
        try {

            // 1. Validar el parámetro ID
            $orderId = $args['id'];
            if (!is_numeric($orderId)) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'El ID del pedido debe ser numérico.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }


            // 2. Buscar el pedido y sus relaciones
            $order = Order::with(['user', 'components.componentType'])->find($orderId);
            if (!$order) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Pedido no encontrado.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }


            // 3. Formatear resultado
            $result = [
                'id' => $order->id,
                'status' => $order->status,
                'date' => $order->created_at,
                'client_info' => [
                    'name' => $order->user ? $order->user->name : null,
                    'lastname' => $order->user ? $order->user->lastname : null,
                    'email' => $order->user ? $order->user->email : null,
                    'phone' => $order->user ? $order->user->phone_number : null
                ],
                'financials' => [
                    'total_price' => $order->total_price
                ],
                'components' => $order->components->map(function ($comp) {
                    return [
                        'name' => $comp->name,
                        'type_name' => $comp->componentType ? $comp->componentType->type_name : null,
                        'quantity' => $comp->pivot->quantity,
                        'price_at_purchase' => $comp->pivot->price_at_purchase
                    ];
                })
            ];


            // 4. Enviar respuesta
            $response->getBody()->write(json_encode([
                'success' => true,
                'order' => $result
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {

            // 5. Manejar errores inesperados
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Error al obtener el pedido: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    })->add(new AdminMiddleware())->add(new JwtMiddleware());



    /**
     * ACTUALIZAR ESTADO DE PEDIDO (ADMIN)
     * Endpoint: PATCH /api/admin/orders/{id}/status
     * Objetivo: Cambiar el estado de un pedido específico
     */
    $app->patch('/api/admin/orders/{id}/status', function (Request $request, Response $response, $args) {
        try {

            // 1. Validar el parámetro ID
            $orderId = $args['id'];
            if (!is_numeric($orderId)) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'El ID del pedido debe ser numérico.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }


            // 2. Buscar el pedido
            $order = Order::find($orderId);
            if (!$order) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Pedido no encontrado.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }


            // 3. Validar el nuevo estado
            $body = $request->getParsedBody();
            $newStatus = $body['status'] ?? null;
            $allowedStatuses = [
                'Pedido Recibido',
                'Esperando Pago',
                'Preparando Componentes',
                'En Ensamblaje',
                'Configuración y Pruebas',
                'Listo para Entrega',
                'Completado',
                'Cancelado'
            ];
            if (!$newStatus || !in_array($newStatus, $allowedStatuses)) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Estado inválido. Debe ser uno de: ' . implode(', ', $allowedStatuses)
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }


            // 4. Actualizar estado y guardar
            $order->status = $newStatus;
            $order->save();


            // 5. Enviar respuesta
            $response->getBody()->write(json_encode([
                'success' => true,
                'order_id' => $order->id,
                'new_status' => $newStatus,
                'message' => "El pedido ha sido actualizado correctamente."
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {

            // 6. Manejar errores inesperados
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Error al actualizar el estado: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    })->add(new AdminMiddleware())->add(new JwtMiddleware());
};
