<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SahamPost extends Model
{
    protected $guarded = ['id'];

    public function saham(): BelongsTo
    {
        return $this->belongsTo(Saham::class);
    }
}
