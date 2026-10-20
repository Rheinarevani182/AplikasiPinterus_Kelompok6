<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pin extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'description', 'category', 'image_url', 'image_path', 'link_url'];

    protected $hidden = ['image_path'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function savedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'is_saved' => 'boolean',
        ];
    }
}

