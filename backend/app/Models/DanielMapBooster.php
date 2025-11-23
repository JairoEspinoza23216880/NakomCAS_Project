<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DanielMapBooster extends Model
{
    protected $table = 'daniel_map_boosters';
    protected $fillable = ['name', 'cpu_tier_plus', 'gpu_tier_plus', 'ram_tier_plus', 'description'];
    public $timestamps = false;
}
