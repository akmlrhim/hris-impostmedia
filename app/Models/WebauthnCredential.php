<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebauthnCredential extends Model
{
    protected $fillable = ['user_id', 'credential_id', 'device_name'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
