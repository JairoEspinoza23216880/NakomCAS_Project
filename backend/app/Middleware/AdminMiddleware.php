<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

use App\Models\User;

class AdminMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        // Obtener datos del usuario autenticado desde el atributo 'user' (inyectado por JwtMiddleware)
        $jwtUser = $request->getAttribute('user');

        // Adaptar acceso para objeto o array
        $userId = null;
        if (is_object($jwtUser) && isset($jwtUser->sub)) {
            $userId = $jwtUser->sub;
        } elseif (is_array($jwtUser) && isset($jwtUser['sub'])) {
            $userId = $jwtUser['sub'];
        }

        if (!$userId) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'No se pudo identificar al usuario.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        // Buscar el usuario en la base de datos y verificar su rol
        $user = User::with('userRole')->find($userId);
        if (!$user || !$user->userRole || stripos(strtolower($user->userRole->name), 'admin') === false) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Acceso denegado: solo administradores pueden realizar esta acción.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        // Si es admin, continuar con el siguiente middleware/handler
        return $handler->handle($request);
    }
}
