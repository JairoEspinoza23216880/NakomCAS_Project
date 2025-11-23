<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'users';

    // Campos asignables masivamente
    protected $fillable = [
        'name',
        'lastname',
        'email',
        'password',
        'user_role_id',
        'phone_number',
        'status'
    ];

    // Desactivar timestamps si no se usan
    public $timestamps = false;

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
