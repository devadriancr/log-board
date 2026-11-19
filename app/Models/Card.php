<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Stevebauman\Purify\Facades\Purify;

class Card extends Model
{
    public const TYPE_LINKS = 'links';

    public const TYPE_TEXT = 'text';

    /** Colours a card can take: key => name. Header, border and text colours of each one are defined in app.css. */
    public const COLORS = [
        'blue' => 'Azul',
        'green' => 'Verde',
        'red' => 'Rojo',
        'amber' => 'Amarillo',
        'purple' => 'Morado',
        'slate' => 'Gris',
    ];

    protected $fillable = [
        'board_id',
        'title',
        'icon',
        'type',
        'content',
        'color',
        'grid_x',
        'grid_y',
        'grid_w',
        'grid_h',
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
            'grid_x' => 'integer',
            'grid_y' => 'integer',
            'grid_w' => 'integer',
            'grid_h' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Names of the icons that <flux:icon> can render (Flux's bundled set plus the ones published in the project).
     *
     * @return array<int, string>
     */
    public static function icons(): array
    {
        return collect([
            base_path('vendor/livewire/flux/stubs/resources/views/flux/icon'),
            resource_path('views/flux/icon'),
        ])
            ->flatMap(fn (string $dir) => glob("$dir/*.blade.php"))
            ->map(fn (string $file) => basename($file, '.blade.php'))
            ->reject('index') // index.blade.php is the <flux:icon> component itself, rendering it loops forever
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * A text card has no title or header: its name (for lists) is taken from the first words of its content.
     */
    protected static function booted(): void
    {
        static::saving(function (Card $card) {
            if ($card->isText()) {
                $card->title = Str::limit(trim(html_entity_decode(strip_tags((string) $card->content))), 60) ?: __('cards.types.text');
            }
        });
    }

    /**
     * The HTML of a text card is cleaned every time it is stored, so it is safe to print as it is.
     */
    protected function content(): Attribute
    {
        return Attribute::set(fn (?string $html) => $html === null || trim(strip_tags($html, '<img><hr>')) === ''
            ? null
            : Purify::config('card')->clean($html));
    }

    public function isText(): bool
    {
        return $this->type === self::TYPE_TEXT;
    }

    /**
     * The key of the palette colour of the card (its CSS lives in app.css), or null when it has none.
     * Text cards have no header, so they never use one.
     */
    public function colorName(): ?string
    {
        return ! $this->isText() && isset(self::COLORS[$this->color]) ? $this->color : null;
    }

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
