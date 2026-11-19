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
     * Inline style for the header when the card has a colour: the background and a text colour that stays readable on it.
     */
    public function headerStyle(): ?string
    {
        if (! $this->color || $this->isText()) {
            return null;
        }

        [$r, $g, $b] = sscanf($this->color, '#%02x%02x%02x');
        $dark = (0.299 * $r + 0.587 * $g + 0.114 * $b) < 150;

        return 'background-color: '.$this->color.'; color: '.($dark ? '#ffffff' : '#18181b').';';
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
