<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classified extends Model
{
    protected $fillable = [
        'user_id',
        'category',
        'title',
        'body',
        'price',
        'campus',
    ];

    /**
     * Categories a listing can belong to. Key = stored value, value = label.
     * Edit here to add/rename categories.
     */
    public const CATEGORIES = [
        'roommate'  => 'Roommate',
        'for_sale'  => 'Buy / Sell',
        'books'     => 'Books',
        'furniture' => 'Dorm Furniture',
        'other'     => 'Other',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ClassifiedReply::class)->oldest();
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? 'Other';
    }
}
