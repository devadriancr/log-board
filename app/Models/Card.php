<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'board_id',
        'title',
        'grid_x',
        'grid_y',
        'grid_w',
        'grid_h',
        'link_columns',
        'is_active',
        'updated_by',
    ];
}
