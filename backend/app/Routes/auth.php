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
        //$expirationTime = $issuedAt + 3600 * 8; // El token expira en 8 horas
        $expirationTime = $issuedAt + 60 * 10; // Tiempo de Expiración para Pruebas

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

        // 1. Obtener los datos del JSON enviado por el Frontend
        $data = $request->getParsedBody();
        $name = $data['name'] ?? null;
        $lastname = $data['lastname'] ?? null;
        $email = $data['email'] ?? null;
        $phoneNumber = $data['phone_number'] ?? null;
        $password = $data['password'] ?? null;

        // 2. Validación de campos obligatorios
        if (!$name || !$lastname || !$email || !$phoneNumber || !$password) {
            $payload = json_encode([
                'success' => false,
                'message' => 'Todos los campos son obligatorios.'
            ]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // 3. Verificar si el email ya existe
        if (User::where('email', $email)->exists()) {
            $payload = json_encode([
                'success' => false,
                'message' => 'El correo electrónico ya está registrado.'
            ]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
        }

        // 4. Crear usuario (rol cliente por defecto: 2)
        $user = new User([
            'name' => $name,
            'lastname' => $lastname,
            'email' => $email,
            'phone_number' => $phoneNumber,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'user_role_id' => 2
        ]);
        $user->save();

        // 5. El proceso fue exitoso, devuelve respuesta
        $payload = json_encode([
            'success' => true,
            'message' => 'Registro exitoso. Procede a iniciar sesión.'
        ]);

        // 6. Enviar respuesta
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    });



    /**
     * VERIFICAR SESIÓN
     * Endpoint: GET /api/me
     * (Espacio reservado para PJDV)
     */
    $app->get('/api/me', function (Request $request, Response $response) {
        // 1. Obtener datos del usuario autenticado desde el atributo 'user' inyectado por JwtMiddleware
        $userData = $request->getAttribute('user');

        if (!$userData) {
            $payload = json_encode([
                'success' => false,
                'message' => 'No se pudo validar la sesión. Token inválido o no proporcionado.'
            ]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        // 2. Buscar el usuario en la base de datos por ID
        $user = \App\Models\User::find($userData->sub);
        if (!$user) {
            $payload = json_encode([
                'success' => false,
                'message' => 'Usuario no encontrado.'
            ]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // Preparar respuesta con datos del usuario
        $responseData = [
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => ($user->user_role_id == 1) ? 'admin' : 'client',
                'status' => $user->status
            ]
        ];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    })->add(new \App\Middleware\JwtMiddleware());
};
