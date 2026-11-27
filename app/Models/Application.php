<?php

namespace App\Models;

use App\Models\Concerns\LogsChanges;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use LogsChanges;

    /** Sección de la bitácora y campos cuyo cambio se registra. */
    protected string $logSection = 'applications';

    protected array $logFields = [
        'name',
        'base_url',
        'requires_auth',
        'notes',
        'is_active',
    ];

    protected function activityLabel(): string
    {
        return $this->name;
    }

    protected $fillable = [
        'name',
        'base_url',
        'requires_auth',
        'notes',
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
            'requires_auth' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the links that belong to the application.
     */
    public function links()
    {
        return $this->hasMany(Link::class);
    }

    /**
     * Get the user who last updated the application.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
