<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FunctionalKit extends Model {
    protected $table = 'functional_kits';
    protected $fillable = ['name', 'base_price', 'cpu_tier', 'gpu_tier', 'ram_tier', 'status'];
    public $timestamps = false; // Si la tabla no tiene created_at/updated_at

    // Relación Muchos a Muchos con Componentes (Tabla Pivote)
    public function components()
    {
        return $this->belongsToMany(
            Component::class, 
            'functional_kit_components', // Nombre tabla pivote
            'functional_kit_id',         // Llave foránea de este modelo
            'component_id'               // Llave foránea del otro modelo
        )->withPivot('quantity');        // Traer la cantidad
    }
}