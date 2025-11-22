<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    protected $table = 'components';

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock_quantity',
        'component_type_id',
        'tier'
    ];

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_x_components')
            ->withPivot('quantity', 'price_at_purchase');
    }
}
