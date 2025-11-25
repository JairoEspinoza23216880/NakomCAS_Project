<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    // TABLA
    protected $table = 'components';


    // ATRIBUTOS
    // Asignables
    protected $fillable = [
        'name',
        'price',
        'stock',
        'component_type_id',
        'tier',
        'status'
    ];

    // Desactivar timestamps
    public $timestamps = false;


    // RELACIONES
    // Un Componente pertenece a un Tipo
    public function componentType()
    {
        return $this->belongsTo(ComponentType::class, 'component_type_id');
    }
    
    // Varios Componentes pertenecen a varias Órdenes
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_x_components')
            ->withPivot('quantity', 'price_at_purchase');
    }
}
