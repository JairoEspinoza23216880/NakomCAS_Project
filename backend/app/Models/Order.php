<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $fillable = ['user_id', 'total_price', 'status'];
    
    // Relación 1: Un Pedido pertenece a un Usuario (belongsTo)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    // Relación 2: Muchos a Muchos con Componentes (a través de order_x_kits)
    public function components()
    {
        return $this->belongsToMany(
            Component::class, 
            'order_x_kits',
            'order_id', 
            'component_id'
        )->withPivot('quantity', 'price_at_purchase'); // Capturar la cantidad y el precio del momento
    }
}
