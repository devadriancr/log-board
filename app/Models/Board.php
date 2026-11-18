<?php

namespace App\Models;

use App\Enums\LinkStyle;
use Illuminate\Database\Eloquent\Collection;
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
            'link_style' => LinkStyle::class,
            'is_home' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Order the boards as the tabs are shown: the home board first, then by position.
     */
    public function scopeOrdered($query)
    {
        return $query->orderByDesc('is_home')->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get the cards for the board.
     */
    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    /**
     * Get the cards the public sees: active cards with at least one active link
     * whose application is also active.
     */
    public function visibleCards(): Collection
    {
        return $this->cards()
            ->where('is_active', true)
            ->with(['links' => fn ($query) => $query
                ->where('is_active', true)
                ->whereHas('application', fn ($application) => $application->where('is_active', true))
                ->with(['application', 'options' => fn ($options) => $options->orderBy('sort_order')])
                ->orderBy('sort_order')])
            ->orderBy('grid_y')
            ->orderBy('grid_x')
            ->get()
            ->filter(fn ($card) => $card->links->isNotEmpty())
            ->values();
    }

    /**
     * Get the address where the public sees the board.
     */
    public function publicUrl(): string
    {
        return $this->is_home ? route('home') : route('boards.show', ['board' => $this->slug]);
    }

    /**
     * Get the user who last updated the board.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
