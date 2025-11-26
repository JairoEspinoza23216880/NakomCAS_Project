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

/**
 * Motor de Búsqueda de Configuración Óptima de PC
 * POST /api/configurator/search
 * 
 * Encuentra la configuración óptima de un kit de PC basado en
 * las necesidades del usuario, boosters y personalizaciones.
 * 
 * Request body:
 * - selected_needs_n_boosters: array de parejas [need_id, booster_id]
 * - selected_personalization_ids: array de IDs de personalización
 * 
 * Returns: Optimal functional kit, structural kit, and total price
 */
return function (App $app) {

    $app->post('/api/configurator/search', function (Request $request, Response $response) {

        // 0. Definir constante para super_category_id de gabinete
        if (!defined('SUPER_CATEGORY_GABINET')) {
            define('SUPER_CATEGORY_GABINET', 3);
        }


        // 1. Obtener datos del body
        $body = $request->getParsedBody();
        $needs_boosters = $body['selected_needs_n_boosters'] ?? [];
        $personalization_ids = $body['selected_personalization_ids'] ?? [];

        $cpu_tiers = [];
        $gpu_tiers = [];
        $ram_tiers = [];
        $ram_tiers = [];

        // Validar y filtrar IDs
        $valid_need_ids = array_filter(array_map(function ($pair) {
            return isset($pair[0]) && is_numeric($pair[0]) ? (int)$pair[0] : null;
        }, $needs_boosters));
        $valid_booster_ids = array_filter(array_map(function ($pair) {
            return isset($pair[1]) && is_numeric($pair[1]) && $pair[1] > 0 ? (int)$pair[1] : null;
        }, $needs_boosters));
        $valid_personalization_ids = array_filter($personalization_ids, 'is_numeric');


        // 2. Inicializar arrays para tiers
        $cpu_tiers = [];
        $gpu_tiers = [];
        $ram_tiers = [];
        // Obtener needs y boosters en lote
        $needs = DanielMapNeed::whereIn('id', $valid_need_ids)->get()->keyBy('id');
        $boosters = DanielMapBooster::whereIn('id', $valid_booster_ids)->get()->keyBy('id');

        // Arrays para tiers
        $cpu_tiers = [];
        $gpu_tiers = [];
        $ram_tiers = [];


        // 3. Calcular tiers máximos considerando boosters
        foreach ($needs_boosters as $pair) {
            if (!is_array($pair) || count($pair) != 2) continue;
            $need_id = $pair[0];
            $booster_id = $pair[1];
            if (!is_numeric($need_id) || !isset($needs[$need_id])) continue;
            $need = $needs[$need_id];
            $booster = (is_numeric($booster_id) && isset($boosters[$booster_id])) ? $boosters[$booster_id] : null;

            $cpu = $need->cpu_tier;
            $gpu = $need->gpu_tier;
            $ram = $need->ram_tier;

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
        $personalizations = [];
        $cabinet_tier = null;
        $personalization_objs = DanielMapPersonalization::whereIn('id', $valid_personalization_ids)->get();
        foreach ($personalization_objs as $p) {
            $personalizations[] = [
                'id' => $p->id,
                'name' => $p->name,
                'price' => isset($p->price) ? $p->price : 0,
                'super_category_id' => $p->super_category_id,
                'tier' => $p->tier
            ];
            if ($p->super_category_id == SUPER_CATEGORY_GABINET) {
                $cabinet_tier = $p->tier;
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
                'message' => 'No pudimos encontrar una combinación viable dentro de los requisitos.'
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


        // 11. Enviar respuesta
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
