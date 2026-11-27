<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;

class LinkOption extends Model
{
    use Translatable;

    protected $fillable = [
        'link_id',
        'value',
        'label',
        'label_en',
        'label_ja',
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
