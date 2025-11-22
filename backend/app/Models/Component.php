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
        'stock_quantity'
    ];

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_components')
            ->withPivot('quantity', 'price_at_order');
    }
}
