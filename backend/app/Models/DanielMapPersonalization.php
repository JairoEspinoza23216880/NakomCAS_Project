<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DanielMapPersonalization extends Model
{
    protected $table = 'daniel_map_personalization';
    protected $fillable = ['name', 'super_category_id', 'tier', 'description'];
    public $timestamps = false;
    
    // Relación: Una Personalización pertenece a una Super Categoría
    public function superCategory()
    {
        return $this->belongsTo(DanielMapSuperCategory::class, 'super_category_id');
    }
}
