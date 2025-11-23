<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StructuralKit extends Model
{
    // TABLA
    protected $table = 'structural_kits';


    // ATRIBUTOS
    // Asignables
    protected $fillable = [
        'name',
        'structural_price',
        'case_tier',
        'status'
    ];

    // Desactivar timestamps
    public $timestamps = false;


    // RELACIONES
    // Relación: Muchos a Muchos con Componentes
    public function components()
    {
        return $this->belongsToMany(
            Component::class,
            'structural_kits_x_components',
            'structural_kit_id', // Esta clave
            'component_id'
        )->withPivot('quantity');
    }
}
