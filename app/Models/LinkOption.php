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

    /**
     * Get the link that owns the option.
     */
    public function link()
    {
        return $this->belongsTo(Link::class);
    }
}
