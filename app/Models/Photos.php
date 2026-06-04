<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Photos extends Model
{
    protected $fillable = [
        'path',
        'created_at'
    ];

    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }
}
