<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // TABLA
    protected $table = 'users';


    // ATRIBUTOS
    // Asignables
    protected $fillable = [
        'name',
        'lastname',
        'email',
        'password',
        'user_role_id',
        'phone_number',
        'status'
    ];

    // Timestamps
    const CREATED_AT = 'register_date';
    const UPDATED_AT = null;
    public $timestamps = true;


    // RELACIONES
    // Un usuario puede tener muchos pedidos
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
