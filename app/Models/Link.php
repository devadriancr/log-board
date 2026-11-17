<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $fillable = [
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'show_description' => 'boolean',
            'open_in_new_tab' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the card that owns the link.
     */
    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    /**
     * Get the application that owns the link.
     */
    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Get the options for the link.
     */
    public function options()
    {
        return $this->hasMany(LinkOption::class);
    }

    /**
     * Get the user who last updated the link.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
