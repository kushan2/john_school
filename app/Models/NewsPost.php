<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsPost extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'category',
        'campus',
    ];

    public const CATEGORIES = [
        'announcement' => 'Announcement',
        'sports'       => 'Sports',
        'academic'     => 'Academic',
        'campus_life'  => 'Campus Life',
        'other'        => 'Other',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? 'Other';
    }

    public function excerpt(int $words = 32): string
    {
        return \Illuminate\Support\Str::words(strip_tags($this->body), $words, '…');
    }
}
