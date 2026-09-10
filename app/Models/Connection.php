<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Connection extends Model
{
    protected $fillable = [
        'user_id',
        'friend_id',
        'status',
    ];

    /** The user who initiated the request / block. */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** The other party (recipient / blocked user). */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'friend_id');
    }

    /** The single row (if any) describing the relationship between two users. */
    public static function between(int $a, int $b): ?self
    {
        return static::where(fn ($q) => $q->where('user_id', $a)->where('friend_id', $b))
            ->orWhere(fn ($q) => $q->where('user_id', $b)->where('friend_id', $a))
            ->first();
    }
}
