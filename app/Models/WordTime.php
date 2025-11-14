<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WordTime extends Model
{
    protected $table = 'word_time';

    protected $fillable = [
        'time',
    ];

    /**
     * Get all appointments for the time slot.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'id_time');
    }
}
