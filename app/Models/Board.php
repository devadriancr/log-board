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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_home' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the cards for the board.
     */
    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    /**
     * Get the user who last updated the board.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
