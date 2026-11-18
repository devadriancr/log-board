<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'board_id',
        'title',
        'icon',
        'grid_x',
        'grid_y',
        'grid_w',
        'grid_h',
        'link_columns',
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
            'link_columns' => 'integer',
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
