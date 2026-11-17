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

    /**
     * Get the board that owns the card.
     */
    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    /**
     * Get the links for the card.
     */
    public function links()
    {
        return $this->hasMany(Link::class);
    }

    /**
     * Get the user who last updated the card.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
