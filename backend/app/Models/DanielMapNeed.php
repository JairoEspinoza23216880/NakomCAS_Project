<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DanielMapNeed extends Model
{
    // TABLA
    protected $table = 'daniel_map_needs';


    // ATRIBUTOS
    // Asignables
    protected $fillable = [
        'name',
        'super_category_id',
        'cpu_tier',
        'gpu_tier',
        'ram_tier',
        'description'
    ];

    // Deshabilitar timestamps
    public $timestamps = false;


    //RELACIONES
    // Una Necesidad pertenece a una Super Categoría
    public function superCategory()
    {
        return $this->belongsTo(DanielMapSuperCategory::class, 'super_category_id');
    }
}
