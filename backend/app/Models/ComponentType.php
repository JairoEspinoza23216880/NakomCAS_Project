<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComponentType extends Model
{
    // TABLA
    protected $table = 'component_types';


    // ATRIBUTOS
    // Asignables
    protected $fillable = ['type_name'];

    // Desactivar timestamps si no se usan
    public $timestamps = false;


    // RELACIONES
    // Un Tipo tiene muchos Componentes
    public function components()
    {
        return $this->hasMany(Component::class, 'component_type_id');
    }
}
