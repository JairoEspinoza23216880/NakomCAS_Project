<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DanielMapBooster extends Model
{
    // TABLA
    protected $table = 'daniel_map_boosters';


    // ATRIBUTOS
    // Asignables
    protected $fillable = [
        'name',
        'cpu_tier_plus',
        'gpu_tier_plus',
        'ram_tier_plus',
        'description'
    ];

    // Desactivar timestamps
    public $timestamps = false;
}
