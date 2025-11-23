<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

// Usamos Pivot en lugar de Model, ya que esta tabla solo enlaza otros
class OrderXComponent extends Pivot
{
    // TABLA
    protected $table = 'order_x_component';

    // ATRIBUTOS
    // Asignables
    protected $fillable = [
        'order_id',
        'component_id',
        'quantity',
        'price_at_purchase'
    ];

    // Desactivamos timestamps
    public $timestamps = false;
}
