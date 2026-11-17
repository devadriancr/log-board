<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $fillable = [
        'url',
        'card_id',
        'application_id',
        'label',
        'path',
        'description',
        'show_description',
        'open_in_new_tab',
        'sort_order',
        'is_active',
        'updated_by',
    ];
}
