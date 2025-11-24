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
};
