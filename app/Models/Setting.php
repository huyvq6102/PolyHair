<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'logo',
        'file_ico',
        'title',
        'introduce',
        'slogan',
    ];
}
