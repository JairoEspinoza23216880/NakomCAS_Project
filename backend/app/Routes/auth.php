<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT; // Librería para generar el token

use App\Models\User;

return function (App $app) {

    /**
     * LOGIN DE USUARIO
     * Endpoint: POST /api/login
     * Objetivo: Validar credenciales y entregar Token JWT
     */
    $app->post('/api/login', function (Request $request, Response $response) {

        // 1. Obtener los datos del JSON enviado por el Frontend
        $data = $request->getParsedBody();
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;


        // 2. Validación básica de campos vacíos
        if (!$email || !$password) {
            $payload = json_encode([
                'success' => false,
                'message' => 'El correo y la contraseña son obligatorios.'
            ]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }


        // 3. Buscar al usuario en la Base de Datos usando Eloquent
        // Buscamos por email y verificamos que esté Activo
        $user = User::where('email', $email)->first();


        // 4. Verificar credenciales
        // A) Si el usuario no existe
        // B) Si la contraseña no coincide con el hash (password_verify)
        if (!$user || !password_verify($password, $user->password)) {
            $payload = json_encode([
                'success' => false,
                'message' => 'Credenciales inválidas.'
            ]);
            $response->getBody()->write($payload);
            // Retornamos 401 Unauthorized para que el Frontend sepa que falló
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        // C) Si el usuario está Inactivo (Baneado/Desactivado)
        if ($user->status !== 'Activo') {
            $payload = json_encode([
                'success' => false,
                'message' => 'Tu cuenta ha sido desactivada. Contacta al soporte.'
            ]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }


        // 5. La comprobación ha sido exitosa, Generar el Token JWT (La "Pulsera VIP")
        // Cargar la clave secreta del .env (o usar una por defecto si falla, aunque no debería)
        $secretKey = $_ENV['JWT_SECRET'] ?? 'clave_secreta_por_defecto_insegura';
        $issuedAt = time();
        $expirationTime = $issuedAt + 3600 * 8; // El token expira en 8 horas

        $tokenPayload = [
            'iat' => $issuedAt,          // Cuándo se creó
            'exp' => $expirationTime,    // Cuándo expira
            'sub' => $user->id,          // Subject (ID del Usuario)
            'role_id' => $user->user_role_id, // Rol (1=Admin, 2=Cliente) para el Middleware
            'email' => $user->email      // Dato extra útil
        ];

        // Codificar el token
        $jwt = JWT::encode($tokenPayload, $secretKey, 'HS256');


        // 6. Preparar respuesta JSON para el Frontend
        $responseData = [
            'success' => true,
            'message' => 'Login exitoso',
            'token' => $jwt, // El Frontend guardará esto en localStorage
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                // Convertimos el ID de rol a texto para facilitar al Frontend
                'role' => ($user->user_role_id == 1) ? 'admin' : 'client'
            ]
        ];


        // 7. Enviar respuesta
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    });

    /**
     * REGISTRO DE USUARIO
     * Endpoint: POST /api/register
     * (Espacio reservado para PJDV)
     */
    $app->post('/api/register', function (Request $request, Response $response) {
        // Espacio reservado para PJDV
    });

    /**
     * VERIFICAR SESIÓN
     * Endpoint: GET /api/me
     * (Espacio reservado para PJDV)
     */
    $app->get('/api/me', function (Request $request, Response $response) {
        // Espacio reservado para PJDV
    });
};
