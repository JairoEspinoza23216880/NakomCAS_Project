<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    // TABLA
    protected $table = 'user_roles';


    // ATRIBUTOS
    // Asignables
    protected $fillable = ['name'];

    // Desactivar timestamps
    public $timestamps = false;


    // RELACIONES
    // Un Rol tiene muchos Usuarios
    public function users()
    {
        return $this->hasMany(User::class, 'user_role_id');
    }
}
