<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Models\FunctionalKit;
use App\Models\StructuralKit;
use App\Models\DanielMapPersonalization;
use App\Models\DanielMapNeed;
use App\Models\DanielMapBooster;
use App\Models\Order;
use App\Models\OrderXComponent;
use App\Models\Component;
use App\Middleware\JwtMiddleware;
use Illuminate\Database\Capsule\Manager as DB;

return function (App $app) {
    $app->post('/api/orders', function (Request $request, Response $response) {

        // 1. Obtener datos del cuerpo y usuario autenticado
        $body = $request->getParsedBody();
        $user = $request->getAttribute('user');

        // 2. Validar datos requeridos
        $required = ['functional_kit_id', 'structural_kit_id', 'personalization_ids', 'total_price'];
        foreach ($required as $field) {
            if (!isset($body[$field])) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => "Falta el campo '$field'."
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }

        // 3. Obtener y validar kits
        $functionalKit = FunctionalKit::find($body['functional_kit_id']);
        $structuralKit = StructuralKit::find($body['structural_kit_id']);
        if (!$functionalKit || !$structuralKit) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Kit funcional o estructural no encontrado.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // 4. Construir lista de componentes (kits y personalización)
        $components = collect();
        // 4.1 Componentes funcionales
        foreach ($functionalKit->components as $comp) {
            $components->push([
                'component' => $comp,
                'quantity' => $comp->pivot->quantity
            ]);
        }
        // 4.2 Componentes estructurales
        foreach ($structuralKit->components as $comp) {
            $components->push([
                'component' => $comp,
                'quantity' => $comp->pivot->quantity
            ]);
        }
        // 4.3 Componentes de personalización (el id recibido es el id del componente)
        $personalizationIds = $body['personalization_ids'];
        if (!is_array($personalizationIds)) $personalizationIds = [];
        $invalidPersonalizationIds = [];
        foreach ($personalizationIds as $compId) {
            $comp = Component::find($compId);
            if ($comp) {
                $components->push([
                    'component' => $comp,
                    'quantity' => 1
                ]);
            } else {
                $invalidPersonalizationIds[] = $compId;
            }
        }
        // 5. Validar existencia de componentes de personalización
        if (count($invalidPersonalizationIds) > 0) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Los siguientes IDs de personalización no existen: ' . implode(', ', $invalidPersonalizationIds)
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // 6. Validar stock de todos los componentes
        foreach ($components as $item) {
            $comp = $item['component'];
            $qty = $item['quantity'];
            if ($comp->stock < $qty) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => "Lo sentimos, el componente '{$comp->name}' se agotó mientras confirmabas."
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }

        // 7. Validar precio
        $totalPrice = $body['total_price'];
        if (!is_numeric($totalPrice) || $totalPrice <= 0) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'El precio debe ser un número positivo.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // 8. Crear pedido y descontar stock en transacción
        try {
            DB::beginTransaction();

            // 8.1 Crear pedido
            $order = Order::create([
                'user_id' => $user->sub ?? $user->id,
                'total_price' => $totalPrice,
                'status' => 'pendiente'
            ]);

            // 8.2 Guardar componentes y descontar stock
            foreach ($components as $item) {
                $comp = $item['component'];
                $qty = $item['quantity'];
                $order->components()->attach($comp->id, [
                    'quantity' => $qty,
                    'price_at_purchase' => $comp->price
                ]);
                $comp->stock -= $qty;
                $comp->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Error al crear el pedido: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        // 9. Responder éxito
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => "Pedido #{$order->id} creado.",
            'order_id' => $order->id
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    })->add(new JwtMiddleware());
};
