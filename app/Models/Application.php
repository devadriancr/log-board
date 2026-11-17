<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'name',
        'base_url',
        'requires_auth',
        'notes',
        'is_active',
        'updated_by',
    ];

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
