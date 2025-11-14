<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    protected $fillable = [
        'content',
        'id_product',
        'id_user',
        'approve',
        'parent_id',
        'rating',
    ];

    protected $casts = [
        'approve' => 'boolean',
        'rating' => 'integer',
        'parent_id' => 'integer',
    ];

    /**
     * Get the product that owns the comment.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'id_product');
    }

    /**
     * Get the user that authored the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /**
     * Get the parent comment.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get the replies for the comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
