<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'link_style',
        'sort_order',
        'is_home',
        'is_active',
        'updated_by',
    ];
}
