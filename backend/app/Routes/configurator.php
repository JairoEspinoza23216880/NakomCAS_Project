<?php
// ------------------------------------------------------------ //
// --------------- DANIEL EL GRANDE Y PODEROSO ---------------- //
// --- Digital Advisor for Need-based IT Equipment Location --- //
// -------------- Motor de configuración de PCs --------------- //
// ------------------------------------------------------------ //

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Models\FunctionalKit;
use App\Models\StructuralKit;
use App\Models\DanielMapPersonalization;
use App\Models\DanielMapNeed;
use App\Models\DanielMapBooster;


return function (App $app) {

    $app->post('/api/configurator/search', function (Request $request, Response $response) {

        // 1. Obtener datos del body
        $body = $request->getParsedBody();
        $needs_boosters = $body['selected_needs_n_boosters'] ?? [];
        $personalization_ids = $body['selected_personalization_ids'] ?? [];

        // Arrays para tiers
        $cpu_tiers = [];
        $gpu_tiers = [];
        $ram_tiers = [];

        // 2. Separar needs y boosters en Arrays
        foreach ($needs_boosters as $pair) {
            if (!is_array($pair) || count($pair) != 2) {
                continue; // Saltar si el formato no es correcto
            }
            $need_id = $pair[0];
            $booster_id = $pair[1];

            $need = DanielMapNeed::find($need_id);
            $booster = $booster_id ? DanielMapBooster::find($booster_id) : null;

            // 3. Sumar boosters a cada need
            $cpu = $need ? $need->cpu_tier : 0;
            $gpu = $need ? $need->gpu_tier : 0;
            $ram = $need ? $need->ram_tier : 0;

            if ($booster) {
                $cpu += $booster->cpu_tier_plus;
                $gpu += $booster->gpu_tier_plus;
                $ram += $booster->ram_tier_plus;
            }

            $cpu_tiers[] = $cpu;
            $gpu_tiers[] = $gpu;
            $ram_tiers[] = $ram;
        }


        // 4. Calcular máximos tiers
        $max_cpu_tier = !empty($cpu_tiers) ? max($cpu_tiers) : 0;
        $max_gpu_tier = !empty($gpu_tiers) ? max($gpu_tiers) : 0;
        $max_ram_tier = !empty($ram_tiers) ? max($ram_tiers) : 0;


        // 5. Buscar kit funcional óptimo (menor precio que cumpla los tiers)
        $kit = FunctionalKit::where('cpu_tier', '>=', $max_cpu_tier)
            ->where('gpu_tier', '>=', $max_gpu_tier)
            ->where('ram_tier', '>=', $max_ram_tier)
            ->where('status', 'Activo')
            ->orderBy('base_price', 'asc')
            ->first();


        // 6. Buscar kit estructural óptimo (menor precio, status Activo, y tier de gabinete si aplica)
        $cabinet_tier = null;
        foreach ($personalization_ids as $pid) {
            $p = DanielMapPersonalization::find($pid);
            if ($p && $p->super_category_id == 3) {
                $cabinet_tier = $p->tier;
                break;
            }
        }
        $structural_query = StructuralKit::where('status', 'Activo');
        if ($cabinet_tier !== null) {
            $structural_query = $structural_query->where('case_tier', $cabinet_tier);
        }
        $structural_kit = $structural_query->orderBy('structural_price', 'asc')->first();


        // 7. Personalizaciones (con metadatos)
        $personalizations = [];
        foreach ($personalization_ids as $pid) {
            $p = DanielMapPersonalization::find($pid);
            if ($p) {
                $personalizations[] = [
                    'id' => $p->id,
                    'name' => $p->name,
                    'super_category_id' => $p->super_category_id,
                    'tier' => $p->tier
                ];
            }
        }


        // 8. Si no hay kit funcional, devolver error
        if (!$kit) {
            $data = [
                'success' => false,
                'message' => 'No pudimos encontrar una combinación viable dentro del presupuesto y requisitos.'
            ];
            $response = $response->withStatus(404);
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json');
        }


        // 9. Calcular precio de los kits por suma de componentes
        $functional_kit_price = 0;
        $functional_components = $kit ? $kit->components : collect();
        foreach ($functional_components as $component) {
            $functional_kit_price += ($component->price ?? 0) * ($component->pivot->quantity ?? 1);
        }

        $structural_kit_price = 0;
        $structural_components = $structural_kit ? $structural_kit->components : collect();
        foreach ($structural_components as $component) {
            $structural_kit_price += ($component->price ?? 0) * ($component->pivot->quantity ?? 1);
        }

        // Sumar personalizaciones
        $total_price = $functional_kit_price + $structural_kit_price;
        foreach ($personalizations as $p) {
            $total_price += isset($p['price']) ? $p['price'] : 0;
        }


        // 10. Formatear respuesta
        $data = [
            'success' => true,
            'total_price' => round($total_price, 2),
            'build' => [
                'functional_kit' => [
                    'id' => $kit ? $kit->id : null,
                    'name' => $kit ? $kit->name : null,
                    'calculated_price' => round($functional_kit_price, 2),
                    'components_list' => $functional_components->map(function ($c) {
                        return [
                            'name' => $c->name,
                            'price' => $c->price,
                            'quantity' => $c->pivot->quantity ?? 1
                        ];
                    })->toArray()
                ],
                'structural_kit' => $structural_kit ? [
                    'id' => $structural_kit->id,
                    'name' => $structural_kit->name,
                    'calculated_price' => round($structural_kit_price, 2),
                    'components_list' => $structural_components->map(function ($c) {
                        return [
                            'name' => $c->name,
                            'price' => $c->price,
                            'quantity' => $c->pivot->quantity ?? 1
                        ];
                    })->toArray()
                ] : null,
                'personalization_components' => $personalizations
            ]
        ];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
