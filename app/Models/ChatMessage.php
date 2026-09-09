<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatMessage extends Model
{
    protected $fillable = [
        'user_id',
        'campus',
        'body',
    ];

    /**
     * Emoji students can react with. Edit here to change the reaction palette.
     */
    public const EMOJIS = ['👍', '❤️', '😂', '🔥', '🎉'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(ChatReaction::class);
    }
}
