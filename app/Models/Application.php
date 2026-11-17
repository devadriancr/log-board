<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'name',
        'base_url',
        'requires_auth',
        'notes',
        'is_active',
        'updated_by',
    ];
}
