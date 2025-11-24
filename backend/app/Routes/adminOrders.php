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

// Rutas para administración de pedidos
return function (App $app) {
    // Middleware: Solo usuarios con rol Vendedor
    $app->get('/api/admin/orders', function (Request $request, Response $response) {
        try {
            $auth = authenticateAdmin($request);
            if (!$auth['success']) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => $auth['message']
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus($auth['code']);
            }
            $user = $auth['user'];

            $orders = Order::with(['user'])
                ->orderBy('id', 'desc')
                ->get();

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

            $response->getBody()->write(json_encode([
                'success' => true,
                'orders' => $result
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Error al obtener los pedidos: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    })->add(new JwtMiddleware());


    $app->get('/api/admin/orders/{id}', function (Request $request, Response $response, $args) {
        try {
            $auth = authenticateAdmin($request);
            if (!$auth['success']) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => $auth['message']
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus($auth['code']);
            }

            $orderId = $args['id'];
            if (!is_numeric($orderId)) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'El ID del pedido debe ser numérico.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            $order = Order::with(['user', 'components.componentType'])->find($orderId);
            if (!$order) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Pedido no encontrado.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }

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

            $response->getBody()->write(json_encode([
                'success' => true,
                'order' => $result
            ]));
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
            $auth = authenticateAdmin($request);
            if (!$auth['success']) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => $auth['message']
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus($auth['code']);
            }

            $orderId = $args['id'];
            if (!is_numeric($orderId)) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'El ID del pedido debe ser numérico.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            $order = Order::find($orderId);
            if (!$order) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Pedido no encontrado.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }

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

            $order->status = $newStatus;
            $order->save();

            $response->getBody()->write(json_encode([
                'success' => true,
                'order_id' => $order->id,
                'new_status' => $newStatus,
                'message' => "El pedido ha sido actualizado correctamente."
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
