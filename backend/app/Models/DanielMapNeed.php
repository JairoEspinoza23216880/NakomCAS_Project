<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DanielMapNeed extends Model
{
    protected $table = 'daniel_map_needs';
    public $timestamps = false;

    // Relación: Una necesidad tiene muchos boosters
    public function boosters()
    {
        // Usamos la tabla pivote nueva que creamos
        return $this->belongsToMany(
            DanielMapBooster::class,
            'needs_x_boosters',
            'need_id',
            'booster_id'
        );
    }
}
