<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\CameraExpluitationInfo;

class Camera extends Model
{
    protected $fillable = [
        'real_camera_id',
        'name',
        'adress',
        'latitude',
        'longitude',
        'status'
    ];

    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }

    public function exploitationInfo(): HasOne{
        return $this->hasOne(CameraExpluitationInfo::class);
    }
}
