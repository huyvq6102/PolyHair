<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barber extends Model
{
    protected $fillable = [
        'name',
        'account',
        'phone',
        'email',
        'password',
        'address',
        'images',
        'code',
        'time_code',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'time_code' => 'integer',
        'password' => 'hashed',
    ];

    /**
     * Get all appointments for the barber.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'id_barber');
    }
}
