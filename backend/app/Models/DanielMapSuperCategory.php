<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DanielMapSuperCategory extends Model
{
    // TABLA
    protected $table = 'daniel_map_super_categories';


    // ATRIBUTOS
    // Asignables
    protected $fillable = ['name'];

    // Desactivar timestamps
    public $timestamps = false;


    // RELACIONES
    // Una Super Categoría tiene muchas Necesidades
    public function needs()
    {
        return $this->hasMany(DanielMapNeed::class, 'super_category_id');
    }

    // Una Super Categoría tiene muchas Personalizaciones
    public function personalizations()
    {
        return $this->hasMany(DanielMapPersonalization::class, 'super_category_id');
    }
}
