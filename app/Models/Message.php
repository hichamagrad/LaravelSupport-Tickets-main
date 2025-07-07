<?php

namespace App\Models;

use Coderflex\LaravelTicket\Models\Message as BaseMessage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Message extends BaseMessage
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
