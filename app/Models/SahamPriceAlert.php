<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SahamPriceAlert extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'harga_target' => 'decimal:2',
            'terpicu_pada' => 'datetime',
        ];
    }

    public function saham(): BelongsTo
    {
        return $this->belongsTo(Saham::class);
    }
}
