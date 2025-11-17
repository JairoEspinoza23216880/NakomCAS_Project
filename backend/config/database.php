<?php
// backend/config/database.php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Cargar las variables de entorno (.env) desde la raíz del backend
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Crear el gestor de Eloquent
$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_ENV['DB_HOST'],     // ej. 127.0.0.1
    'database'  => $_ENV['DB_DATABASE'], // ej. configurador_pc
    'username'  => $_ENV['DB_USERNAME'], // ej. root
    'password'  => $_ENV['DB_PASSWORD'], // tu contraseña
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

// Hacer que Eloquent esté disponible globalmente
$capsule->setAsGlobal();

// Arrancar Eloquent
$capsule->bootEloquent();