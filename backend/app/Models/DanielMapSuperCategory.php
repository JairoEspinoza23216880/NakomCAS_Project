<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DanielMapSuperCategory extends Model
{
    protected $table = 'daniel_map_super_categories';
    protected $fillable = ['name'];
    public $timestamps = false;

    
    public function needs() {
        return $this->hasMany(DanielMapNeed::class, 'super_category_id');
    }
    public function personalizations() {
        return $this->hasMany(DanielMapPersonalization::class, 'super_category_id');
    }
}


va el código del modelo DanielMapSuperCategory

