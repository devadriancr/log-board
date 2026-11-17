<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinkOption extends Model
{
    protected $fillable = [
        'link_id',
        'value',
        'label',
        'sort_order',
    ];
}
