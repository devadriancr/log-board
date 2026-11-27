<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Concerns\LogsChanges;
use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Stevebauman\Purify\Facades\Purify;

class Card extends Model
{
    use LogsChanges, Translatable;

    /** Sección de la bitácora y campos cuyo cambio se registra. */
    protected string $logSection = 'cards';

    protected array $logFields = [
        'board_id',
        'title',
        'title_en',
        'title_ja',
        'icon',
        'type',
        'content',
        'content_en',
        'content_ja',
        'slide_seconds',
        'color',
        'grid_w',
        'grid_h',
        'is_active',
    ];

    protected function activityLabel(): string
    {
        return $this->title;
    }

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
        'teal' => 'Turquesa',
        'cyan' => 'Cian',
        'indigo' => 'Índigo',
        'pink' => 'Rosa',
        'orange' => 'Naranja',
        'lime' => 'Lima',
        'sky' => 'Celeste',
        'emerald' => 'Esmeralda',
        'yellow' => 'Dorado',
        'violet' => 'Violeta',
        'fuchsia' => 'Fucsia',
        'rose' => 'Rosado',
        'brown' => 'Café',
        'black' => 'Negro',
    ];

    /**
     * The names of the palette colours in the language of the visitor: key => name.
     *
     * @return array<string, string>
     */
    public static function colors(): array
    {
        return collect(self::COLORS)->map(fn ($name, $key) => __("cards.colors.$key"))->all();
    }

    protected $fillable = [
        'board_id',
        'title',
        'title_en',
        'title_ja',
        'icon',
        'type',
        'content',
        'content_en',
        'content_ja',
        'slide_seconds',
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
            'slide_seconds' => 'integer',
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
                $card->title = Str::limit(trim(html_entity_decode(strip_tags($card->content[0] ?? ''))), 60) ?: __('cards.types.text');
            }
        });
    }

    /**
     * A text card holds a list of HTML slides (the notices of its carousel), in Spanish; content_en and content_ja
     * hold the same list translated. Each one is cleaned every time it is stored, so it is safe to print as it is;
     * blank ones are dropped.
     *
     * @return Attribute<array<int, string>, array<int, string>|string|null>
     */
    protected function content(): Attribute
    {
        return $this->slides();
    }

    protected function contentEn(): Attribute
    {
        return $this->slides();
    }

    protected function contentJa(): Attribute
    {
        return $this->slides();
    }

    private function slides(): Attribute
    {
        return Attribute::make(
            // shortcut: a card saved before slides existed holds one plain HTML text, shown as a single slide.
            get: fn (?string $json) => is_array($slides = json_decode((string) $json, true))
                ? $slides
                : (filled($json) ? [$json] : []),
            set: function ($slides) {
                $slides = collect((array) $slides)
                    ->reject(fn ($html) => self::isBlankSlide($html))
                    ->map(fn ($html) => Purify::config('card')->clean($html))
                    ->values();

                return $slides->isEmpty() ? null : $slides->toJson(JSON_UNESCAPED_UNICODE);
            },
        );
    }

    /**
     * The slides in the language of the visitor, or the Spanish ones when that translation has none.
     *
     * @return array<int, string>
     */
    public function localizedSlides(): array
    {
        $locale = app()->getLocale();

        return $locale !== 'es' && filled($this->getAttribute("content_$locale")) ? $this->getAttribute("content_$locale") : $this->content;
    }

    /**
     * A slide with no text, image or line is blank.
     */
    public static function isBlankSlide(?string $html): bool
    {
        return trim(strip_tags((string) $html, '<img><hr>')) === '' && ! str_contains((string) $html, 'data-icon');
    }

    /**
     * Fills the icon markers of a slide (inline <span data-icon="star"></span> and callouts) with the SVG of the icon.
     * Unknown names are left empty, so only icons of the system are ever printed.
     */
    public static function withIcons(string $html): string
    {
        $svg = fn (string $name) => in_array($name, self::icons(), true)
            ? Blade::render('<flux:icon :name="$name" />', ['name' => $name])
            : null;

        // Inline icons: <span data-icon="star"></span>
        $html = preg_replace_callback(
            '/<span data-icon="([\w-]+)"><\/span>/',
            fn (array $m) => ($icon = $svg($m[1])) ? '<span data-icon="'.$m[1].'">'.$icon.'</span>' : $m[0],
            $html,
        );

        // Callouts: the icon goes first inside the quote, in its own colour of the palette when it has one.
        return preg_replace_callback('/<blockquote\b[^>]*\bdata-callout-icon="([\w-]+)"[^>]*>/', function (array $m) use ($svg) {
            if (! ($icon = $svg($m[1]))) {
                return $m[0];
            }

            $color = preg_match('/data-callout-icon-color="(\w+)"/', $m[0], $c) && isset(self::COLORS[$c[1]]) ? ' data-callout-color="'.$c[1].'"' : '';

            return $m[0].'<span class="callout-icon"'.$color.'>'.$icon.'</span>';
        }, $html);
    }

    public function isText(): bool
    {
        return $this->type === self::TYPE_TEXT;
    }

    /**
     * The key of the palette colour of the card (its CSS lives in app.css), or null when it has none.
     */
    public function colorName(): ?string
    {
        return isset(self::COLORS[$this->color]) ? $this->color : null;
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
     * Rows of the link grid when a preview fits the card ("1fr" takes the free height), both as placed in the
     * designer and stacked on narrow cards. Null when no preview needs it.
     */
    public function previewRows(): ?string
    {
        $fills = fn (Link $link) => $link->show_preview && $link->preview_size === 'fill';

        if (! $this->links->contains($fills)) {
            return null;
        }

        $placed = collect(range(0, $this->links->max('grid_y')))
            ->map(fn (int $y) => $this->links->where('grid_y', $y)->contains($fills));
        $rows = fn ($filled) => $filled->map(fn (bool $fill) => $fill ? '1fr' : 'auto')->implode(' ');

        return "--rows-placed: {$rows($placed)}; --rows-stacked: {$rows($this->links->toBase()->map($fills))}";
    }

    /**
     * Get the user who last updated the card.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
