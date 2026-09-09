<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassifiedReply extends Model
{
    protected $fillable = [
        'classified_id',
        'user_id',
        'body',
    ];

    public function classified(): BelongsTo
    {
        return $this->belongsTo(Classified::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
