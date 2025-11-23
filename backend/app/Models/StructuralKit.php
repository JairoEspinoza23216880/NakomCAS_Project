<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StructuralKit extends Model
{
    protected $table = 'structural_kits';
    protected $fillable = ['name', 'structural_price', 'case_tier', 'status'];
    
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
