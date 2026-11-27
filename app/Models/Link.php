<?php

namespace App\Models;

use App\Models\Concerns\LogsChanges;
use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    use LogsChanges, Translatable;

    /** Height of the preview: "fill" takes what is left in the card, the others are fixed. */
    public const PREVIEW_SIZES = ['fill', 'sm', 'md', 'lg'];

    /** Sección de la bitácora y campos cuyo cambio se registra. */
    protected string $logSection = 'links';

    protected array $logFields = [
        'card_id',
        'application_id',
        'label',
        'label_en',
        'label_ja',
        'path',
        'description',
        'description_en',
        'description_ja',
        'show_description',
        'open_in_new_tab',
        'show_preview',
        'preview_size',
        'is_active',
    ];

    protected function activityLabel(): string
    {
        return $this->label;
    }

    protected $fillable = [
        'card_id',
        'application_id',
        'label',
        'label_en',
        'label_ja',
        'path',
        'description',
        'description_en',
        'description_ja',
        'show_description',
        'open_in_new_tab',
        'show_preview',
        'preview_size',
        'sort_order',
        'grid_x',
        'grid_y',
        'grid_w',
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
            'show_description' => 'boolean',
            'open_in_new_tab' => 'boolean',
            'show_preview' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the full URL of the link. The {param} placeholder is kept when the link has options.
     */
    public function urlTemplate(?string $base = null): string
    {
        return rtrim($base ?: $this->application->base_url, '/').'/'.ltrim($this->path, '/');
    }

    /**
     * The address the thumbnail loads: through the preview address of the application when it has one.
     */
    public function previewUrlTemplate(): string
    {
        return $this->urlTemplate($this->application->preview_url);
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
