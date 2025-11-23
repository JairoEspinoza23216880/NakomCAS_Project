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
        'tier'
    ];

    // Desactivar timestamps
    public $timestamps = false;


    // RELACIONES
    // Varios Componentes pertenecen varias Órdenes
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_x_components')
            ->withPivot('quantity', 'price_at_purchase');
    }
}
