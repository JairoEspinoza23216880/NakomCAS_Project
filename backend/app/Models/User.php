<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';

    protected $fillable = [
        'name',
        'lastname',
        'email',
        'password',
        'user_role_id'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
