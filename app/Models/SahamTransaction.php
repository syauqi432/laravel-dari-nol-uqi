<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SahamTransaction extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'harga' => 'decimal:2',
            'laba_terealisasi' => 'decimal:2',
        ];
    }

    public function saham(): BelongsTo
    {
        return $this->belongsTo(Saham::class);
    }
}
