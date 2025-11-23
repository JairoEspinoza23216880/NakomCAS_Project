<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Middleware para verificar Tokens JWT en las cabeceras de autorización.
 */
class JwtMiddleware
{
    /**
     * Ejecuta el middleware.
     *
     * @param Request $request La petición entrante.
     * @param RequestHandler $handler El siguiente manejador en la cadena.
     * @return Response La respuesta generada.
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        // 1. Obtener la cabecera Authorization
        $authHeader = $request->getHeaderLine('Authorization');


        // 2. Validar que tenga el formato "Bearer <token>"
        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $this->unauthorizedResponse('Token no proporcionado o formato inválido.');
        }

        // Verificar que si se haya capturado bien el token, por si las dudas
        if (!isset($matches[1])) {
            return $this->unauthorizedResponse('Token no proporcionado o formato inválido.');
        }

        // Extraer el token
        $token = $matches[1];


        try {
            // 3. Decodificar el token
            // Usamos la clave secreta del .env (La que nadie podrá adivinar muajaja)
            $secretKey = $_ENV['JWT_SECRET'] ?? 'default_secret_key_DO_NOT_USE';
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));


            // 4. ¡Éxito! Inyectar los datos del usuario en la petición
            // Esto permite acceder a $request->getAttribute('user') en los controladores
            // Muy útil para obtener el ID del usuario, roles, etc.
            $request = $request->withAttribute('user', $decoded);
        } catch (\Exception $e) {
            // 5. Si falla (expirado, firma falsa, etc.)
            return $this->unauthorizedResponse('Token inválido o expirado: ' . $e->getMessage());
        }

        // 6. Pasar la petición al siguiente eslabón (el controlador)
        return $handler->handle($request);
    }

    /**
     * Genera una respuesta JSON estándar de error 401.
     *
     * @param string $message Mensaje de error.
     * @return Response
     */
    private function unauthorizedResponse(string $message): Response
    {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => $message
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401); // 401 Unauthorized
    }
}
