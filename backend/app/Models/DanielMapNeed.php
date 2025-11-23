<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DanielMapNeed extends Model
{
    protected $table = 'daniel_map_needs';
    protected $fillable = ['name', 'super_category_id', 'cpu_tier', 'gpu_tier', 'ram_tier', 'description'];
    public $timestamps = false;
    
    // Relación: Una Necesidad pertenece a una Super Categoría
    public function superCategory()
    {
        return $this->belongsTo(DanielMapSuperCategory::class, 'super_category_id');
    }
}
