<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\Pivot;

// Usamos Pivot en lugar de Model, ya que esta tabla solo enlaza otros
class OrderXComponent extends Pivot
{
    protected $table = 'order_x_kits';
    protected $fillable = ['order_id', 'component_id', 'quantity', 'price_at_purchase'];
    public $timestamps = false;
    
}
