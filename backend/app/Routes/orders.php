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

return function (App $app) {
    $app->post('/api/orders', function (Request $request, Response $response) {

        // 1. Obtener datos del cuerpo y usuario autenticado
        $body = $request->getParsedBody();
        $user = $request->getAttribute('user');

        // Validar datos requeridos
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

        // Obtener componentes de los kits
        $functionalKit = FunctionalKit::find($body['functional_kit_id']);
        $structuralKit = StructuralKit::find($body['structural_kit_id']);
        if (!$functionalKit || !$structuralKit) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Kit funcional o estructural no encontrado.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $components = collect();

        // Componentes funcionales
        foreach ($functionalKit->components as $comp) {
            $components->push([
                'component' => $comp,
                'quantity' => $comp->pivot->quantity
            ]);
        }

        // Componentes estructurales
        foreach ($structuralKit->components as $comp) {
            $components->push([
                'component' => $comp,
                'quantity' => $comp->pivot->quantity
            ]);
        }

        // Componentes de personalización
        $personalizationIds = $body['personalization_ids'];
        if (!is_array($personalizationIds)) $personalizationIds = [];
        foreach ($personalizationIds as $pid) {
            $pers = DanielMapPersonalization::find($pid);
            if ($pers) {
                // Asumimos que la personalización es un componente extra
                $comp = Component::find($pid);
                if ($comp) {
                    $components->push([
                        'component' => $comp,
                        'quantity' => 1
                    ]);
                }
            }
        }

        // Validar stock de todos los componentes
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

        // Crear el pedido
        $order = Order::create([
            'user_id' => $user->sub ?? $user->id ?? null,
            'total_price' => $body['total_price'],
            'status' => 'pendiente'
        ]);

        // Guardar componentes en la tabla pivote
        foreach ($components as $item) {
            $comp = $item['component'];
            $qty = $item['quantity'];
            // Guardar snapshot del precio actual
            $order->components()->attach($comp->id, [
                'quantity' => $qty,
                'price_at_purchase' => $comp->price
            ]);
            // Descontar stock
            $comp->stock -= $qty;
            $comp->save();
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => "Pedido #{$order->id} creado.",
            'order_id' => $order->id
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    })->add(new JwtMiddleware());
};
