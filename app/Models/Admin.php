<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admin extends Authenticatable
{
    use SoftDeletes;
    protected $fillable = [
        'full_name',
        'username',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
    ];
}
