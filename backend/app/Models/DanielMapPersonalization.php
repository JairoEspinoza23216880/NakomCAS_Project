<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DanielMapPersonalization extends Model
{
    // TABLA
    protected $table = 'daniel_map_personalization';


    // ATRIBUTOS
    // Asignables
    protected $fillable = [
        'name',
        'super_category_id',
        'tier',
        'description'
    ];

    // Desactivar timestamps
    public $timestamps = false;


    // RELACIONES
    // Una Personalización pertenece a una Super Categoría
    public function superCategory()
    {
        return $this->belongsTo(DanielMapSuperCategory::class, 'super_category_id');
    }
}
