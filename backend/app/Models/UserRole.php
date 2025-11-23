<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $table = 'user_roles';
    protected $fillable = ['name'];
    public $timestamps = false; // No tiene columnas created_at/updated_at

    // Relación: Un Rol tiene muchos Usuarios (hasMany)
    public function users()
    {
        // En User.php, la clave foránea es 'user_role_id'
        return $this->hasMany(User::class, 'user_role_id');
    }
}
