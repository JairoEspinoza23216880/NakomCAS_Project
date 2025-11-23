<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ComponentType extends Model
{
    protected $table = 'component_types';
    protected $fillable = ['type_name'];
    public $timestamps = false; 

    // Relación: Un Tipo tiene muchos Componentes
    public function components()
    {
        return $this->hasMany(Component::class, 'component_type_id');
    }
}
