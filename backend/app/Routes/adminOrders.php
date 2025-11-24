<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Models\Order;
use App\Models\User;
use App\Middleware\JwtMiddleware;
use Illuminate\Database\Capsule\Manager as DB;

return function (App $app) {
    // Middleware: Solo usuarios con rol Vendedor
    $app->get('/api/admin/orders', function (Request $request, Response $response) {
        try {

            // 1. Obtener usuario autenticado desde el token (JWT decodificado)
            $jwtUser = $request->getAttribute('user');
            if (!$jwtUser || !isset($jwtUser->sub)) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'No se pudo identificar al usuario.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }

            // 2. Buscar el usuario real en la base de datos para obtener el rol
            $user = User::find($jwtUser->sub);
            if (!$user || $user->user_role_id != 1) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Acceso denegado: solo administradores pueden ver los pedidos.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
            }

            // 3. Consultar todos los pedidos con datos del cliente
            $orders = Order::with(['user'])
                ->orderBy('id', 'desc')
                ->get();

            // 4. Formatear la respuesta para cada pedido
            $result = $orders->map(function ($order) {
                // 4.1 Generar summary_name (ejemplo: "Kit Gaming Pro + Combo Airflow")
                $summary_name = '';
                // Si hay relación con kits, aquí se puede mejorar para mostrar el nombre del kit
                // Por ahora, solo ejemplo con nombre del cliente
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

            // 5. Devolver respuesta exitosa
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {
            // 6. Manejar excepciones y devolver mensaje de error
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Error al obtener los pedidos: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    })->add(new JwtMiddleware());


    $app->get('/api/admin/orders/{id}', function (Request $request, Response $response, $args) {
        try {
            // 1. Autenticación y verificación de rol
            $jwtUser = $request->getAttribute('user');
            if (!$jwtUser || !isset($jwtUser->sub)) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'No se pudo identificar al usuario.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }
            $user = User::find($jwtUser->sub);
            if (!$user || $user->user_role_id != 1) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Acceso denegado: solo administradores pueden ver los pedidos.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
            }

            // 2. Validar que el ID sea numérico
            $orderId = $args['id'];
            if (!is_numeric($orderId)) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'El ID del pedido debe ser numérico.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            // 3. Obtener el pedido por ID
            $order = Order::with(['user', 'components.componentType'])->find($orderId);
            if (!$order) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Pedido no encontrado.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }

            // 4. Formatear la respuesta con datos del cliente y componentes
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

            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Error al obtener el pedido: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    })->add(new JwtMiddleware());

    $app->patch('/api/admin/orders/{id}/status', function (Request $request, Response $response, $args) {
        try {
            // 1. Autenticación y verificación de rol
            $jwtUser = $request->getAttribute('user');
            if (!$jwtUser || !isset($jwtUser->sub)) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'No se pudo identificar al usuario.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }
            $user = User::find($jwtUser->sub);
            if (!$user || $user->user_role_id != 1) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Acceso denegado: solo administradores pueden modificar pedidos.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
            }

            // 2. Validar que el ID sea numérico
            $orderId = $args['id'];
            if (!is_numeric($orderId)) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'El ID del pedido debe ser numérico.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            // 3. Obtener el pedido
            $order = Order::find($orderId);
            if (!$order) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Pedido no encontrado.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }

            // 4. Validar el estado recibido
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

            // 5. Actualizar el estado y guardar
            $order->status = $newStatus;
            $order->save();

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => "El pedido #{$order->id} ahora está: {$newStatus}"
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Error al actualizar el estado: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    })->add(new JwtMiddleware());
};
