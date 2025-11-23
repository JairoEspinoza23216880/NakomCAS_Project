<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    // TABLA
    protected $table = 'orders';


    // ATRIBUTOS
    // Asignables
    protected $fillable = [
        'user_id',
        'total_price',
        'status'
    ];

    // Activar timestamps
    public $timestamps = true;


    // RELACIONES
    // Un Pedido pertenece a un Usuario (belongsTo)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Muchos a Muchos con Componentes (a través de order_x_components)
    public function components()
    {
        return $this->belongsToMany(
            Component::class,
            'order_x_components',
            'order_id',
            'component_id'
        )->withPivot('quantity', 'price_at_purchase'); // Capturar la cantidad y el precio del momento
    }
}
