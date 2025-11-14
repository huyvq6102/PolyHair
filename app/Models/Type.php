<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Type extends Model
{
    protected $fillable = [
        'name',
        'images',
    ];

    /**
     * Get all services for the type.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'id_type');
    }
}
