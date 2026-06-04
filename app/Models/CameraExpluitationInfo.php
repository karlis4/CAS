<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CameraExpluitationInfo extends Model
{
    protected $table = "cameras_exploitation_info";
    public $timestamps = false;

    protected $fillable = [
        'currentCorp',
        'currentPerson',
        'dateExpluatation',
        'dateGuarantee',
        'inventNumber'
    ];

    public function camera(): BelongsTo{
        return $this->belongsTo(Camera::class);
    }
}
