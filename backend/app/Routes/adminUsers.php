<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Middleware\JwtMiddleware;
use App\Middleware\AdminMiddleware;
use App\Models\User;

return function (App $app) {
    // Función privada para validar y actualizar campos editables de usuario
    function validarYActualizarUsuario($user, $data)
    {
        $editable = ['name', 'lastname', 'email', 'phone_number', 'password'];
        $updated = false;
        foreach ($editable as $field) {
            if (isset($data[$field]) && trim($data[$field]) !== '') {
                if ($field === 'name' || $field === 'lastname') {
                    if (!is_string($data[$field])) {
                        return ['error' => 'Datos inválidos.', 'code' => 400];
                    }
                    $user->$field = trim($data[$field]);
                    $updated = true;
                } elseif ($field === 'email') {
                    if ($data['email'] !== $user->email) {
                        if (User::where('email', $data['email'])->where('id', '!=', $user->id)->exists()) {
                            return ['error' => 'Datos inválidos.', 'code' => 409];
                        }
                        $user->email = $data['email'];
                        $updated = true;
                    }
                } elseif ($field === 'phone_number') {
                    if (!is_string($data['phone_number'])) {
                        return ['error' => 'Datos inválidos.', 'code' => 400];
                    }
                    $user->phone_number = trim($data['phone_number']);
                    $updated = true;
                } elseif ($field === 'password') {
                    if (!is_string($data['password'])) {
                        return ['error' => 'Datos inválidos.', 'code' => 400];
                    }
                    $user->password = password_hash($data['password'], PASSWORD_DEFAULT);
                    $updated = true;
                }
            }
        }
        if (!$updated) {
            return ['error' => 'No se proporcionaron datos válidos para actualizar.', 'code' => 400];
        }
        return ['success' => true];
    }


    /**
     * LISTAR TODOS LOS USUARIOS (ADMIN)
     * Endpoint: GET /api/admin/users
     * Objetivo: Poblar la tabla principal de gestión de usuarios (activos e inactivos)
     */
    $app->get('/api/admin/users', function (Request $request, Response $response) {
        try {

            // 1. Obtener todos los usuarios con relación de rol
            $users = User::with('userRole')->orderBy('id', 'desc')->get();


            // 2. Formatear resultado según contrato, excluyendo campos sensibles
            $result = $users->map(function ($user) {
                // Obtener el nombre del rol desde la relación (userRole)
                $role = isset($user->userRole) ? $user->userRole->name : 'Desconocido';

                // Validar tipo de fecha
                $register_date = null;
                if ($user->register_date instanceof \DateTimeInterface) {
                    $register_date = $user->register_date->format('Y-m-d');
                } elseif (is_string($user->register_date)) {
                    $register_date = substr($user->register_date, 0, 10);
                }

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'role' => $role,
                    'status' => $user->status,
                    'register_date' => $register_date
                ];
            });


            // 3. Enviar respuesta
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {

            // 4. Manejar errores inesperados
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Error al obtener los usuarios: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    })->add(new AdminMiddleware())->add(new JwtMiddleware());



    /**
     * CREAR USUARIO MANUALMENTE (ADMIN)
     * Endpoint: POST /api/admin/users
     * Objetivo: Registrar un cliente manualmente (ej. por teléfono)
     */
    $app->post('/api/admin/users', function (Request $request, Response $response) {
        try {

            // 1. Obtener y validar datos
            $data = $request->getParsedBody();
            $required = ['name', 'lastname', 'email', 'phone_number', 'password'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    $response->getBody()->write(json_encode([
                        'success' => false,
                        'message' => "El campo '$field' es obligatorio."
                    ]));
                    return $response->withStatus(400)
                        ->withHeader('Content-Type', 'application/json');
                }
            }


            // 2. Verificar si el email ya existe
            if (User::where('email', $data['email'])->exists()) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Ese correo ya está registrado.'
                ]));
                return $response->withStatus(409)
                    ->withHeader('Content-Type', 'application/json');
            }


            // 3. Crear el usuario (rol cliente por defecto)
            $user = new User([
                'name' => $data['name'],
                'lastname' => $data['lastname'],
                'email' => $data['email'],
                'phone_number' => $data['phone_number'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'user_role_id' => 1, // Cliente
                'status' => 'Activo'
            ]);
            $user->save();


            // 4. Respuesta exitosa
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Usuario creado correctamente.',
                'id' => $user->id
            ]));
            return $response->withStatus(201)
                ->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {

            // 5. Manejar errores inesperados
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Error al crear el usuario: ' . $e->getMessage()
            ]));
            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    })->add(new AdminMiddleware())->add(new JwtMiddleware());



    /**
     * EDITAR USUARIO (ADMIN)
     * Endpoint: PUT /api/admin/users/{id}
     * Objetivo: Modificar datos personales o restablecer contraseña
     */
    $app->put('/api/admin/users/{id}', function (Request $request, Response $response, $args) {
        try {

            // 0. Obtener ID y datos
            $userId = $args['id'];
            $data = $request->getParsedBody();


            // 1. Buscar usuario
            $user = User::find($userId);
            if (!$user) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Usuario no encontrado.'
                ]));
                return $response->withStatus(404)
                    ->withHeader('Content-Type', 'application/json');
            }


            // 2. Validar campos editables
            // Validar y actualizar usuario usando función privada
            $resultado = validarYActualizarUsuario($user, $data);
            if (isset($resultado['error'])) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => $resultado['error']
                ]));
                return $response->withStatus($resultado['code'])
                    ->withHeader('Content-Type', 'application/json');
            }
            $user->save();
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Usuario actualizado correctamente.'
            ]));
            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {

            // 6. Manejar errores inesperados
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Error al editar el usuario: ' . $e->getMessage()
            ]));
            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    })->add(new AdminMiddleware())->add(new JwtMiddleware());



    /**
     * CAMBIAR ESTADO DE USUARIO (ADMIN)
     * Endpoint: PATCH /api/admin/users/{id}/status
     * Objetivo: Activar o desactivar usuario (soft delete)
     */
    $app->patch('/api/admin/users/{id}/status', function (Request $request, Response $response, $args) {
        // Aquí va el código
    })->add(new AdminMiddleware())->add(new JwtMiddleware());
};
