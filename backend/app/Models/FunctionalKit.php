<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FunctionalKit extends Model
{
    // TABLA
    protected $table = 'functional_kits';


    // ATRIBUTOS
    // Asignables
    protected $fillable = [
        'name',
        'base_price',
        'cpu_tier',
        'gpu_tier',
        'ram_tier',
        'status',
    ];

    // Desactivar timestamps
    public $timestamps = false;


    // RELACIONES
    // Relación Muchos a Muchos con Componentes (Tabla Pivote)
    public function components()
    {
        return $this->belongsToMany(
            Component::class,
            'functional_kits_x_components', // Nombre tabla pivote
            'functional_kit_id',         // Llave foránea de este modelo
            'component_id'               // Llave foránea del otro modelo
        )->withPivot('quantity');        // Traer la cantidad
    }
}
