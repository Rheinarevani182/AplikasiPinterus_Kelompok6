<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class PinReport extends Model
{
    protected $fillable = ['user_id', 'pin_id', 'reason'];

    public function pin(): BelongsTo
    {
        return $this->belongsTo(Pin::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
